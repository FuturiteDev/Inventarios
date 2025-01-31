<?php

namespace Ongoing\Inventarios\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\NotificacionesRepositoryEloquent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Log;
use Ongoing\Inventarios\Entities\Inventario;
use Ongoing\Inventarios\Repositories\TraspasosProductosRepositoryEloquent;
use Ongoing\Inventarios\Repositories\TraspasosRepositoryEloquent;
use Ongoing\Inventarios\Repositories\ProductosPendientesTraspasoRepositoryEloquent;
use App\Repositories\UsuariosautorizadosRepositoryEloquent;

use Ongoing\Inventarios\Entities\Productos;
use Ongoing\Sucursales\Entities\Sucursales;
use App\Repositories\UsuarioRepositoryEloquent;
use Illuminate\Support\Facades\DB;

use stdClass;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;

class TraspasosController extends Controller
{
    protected $usuarios;
    protected $traspasos;
    protected $traspasosProductos;
    protected $productosPendientes;
    protected $notificaciones;
    protected $usuariosAutorizados;

    public function __construct(
        UsuarioRepositoryEloquent $usuarios,
        TraspasosRepositoryEloquent $traspasos,
        TraspasosProductosRepositoryEloquent $traspasosProductos,
        ProductosPendientesTraspasoRepositoryEloquent $productosPendientes,
        NotificacionesRepositoryEloquent $notificaciones,
        UsuariosautorizadosRepositoryEloquent $usuariosAutorizados
    ) {
        $this->usuarios = $usuarios;
        $this->traspasos = $traspasos;
        $this->traspasosProductos = $traspasosProductos;
        $this->productosPendientes = $productosPendientes;
        $this->notificaciones = $notificaciones;
        $this->usuariosAutorizados = $usuariosAutorizados;
    }

    public function getTraspaso($traspaso_id)
    {
        try {

            $traspaso = $this->traspasos
            ->with([
                'sucursalOrigen',
                'sucursalDestino',
                'empleado:id,nombre,no_empleado',
                'empleadoAsignado:id,nombre,no_empleado',
                'traspasoProductos'
            ])

            ->find($traspaso_id);
            return response()->json([
                'status' => true,
                'results' => $traspaso
            ], 200);
        } catch (\Exception $e) {
            Log::info("TraspasosController->getTraspaso() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] TraspasosController->getTraspaso() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    public function getTraspasoSucursal($sucursal_id)
    {
        try {

            $traspasos = $this->traspasos->with(['sucursalOrigen', 'sucursalDestino'])
                ->where('estatus', 1)
                ->where('sucursal_origen_id', $sucursal_id)
                ->get();

            if ($traspasos->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No se encontraron traspasos.'
                ], 200);
            }

            return response()->json([
                'status' => true,
                'results' => $traspasos,
                'message' => 'Traspasos obtenidos correctamente.',
            ], 200);
        } catch (\Exception $e) {
            Log::info("TraspasosController->getTraspasoSucursal() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] TraspasosController->getTraspasoSucursal() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    public function saveTraspaso(Request $request)
    {
        try {
            $sucursal_origen_id = $request->sucursal_origen_id;
            $sucursal_destino_id = $request->sucursal_destino_id;

            $inputTraspasos = [];
            $inputProdTraspasos = [];
            $productosInexistentes = [];
            $productosTraspasados = [];

            $inputTraspasos['sucursal_origen_id'] = $sucursal_origen_id;
            $inputTraspasos['sucursal_destino_id'] = $sucursal_destino_id;
            $inputTraspasos['tipo'] = $request->tipo;
            $inputTraspasos['empleado_id'] = $request->empleado_id;
            $inputTraspasos['asignado_a'] = $request->asignado_a;
            $inputTraspasos['comentarios'] = $request->comentarios;

            foreach ($request->productos as $producto) {
                $productoExistente = Productos::find($producto['producto_id']);

                if (!$productoExistente) {
                    $productosInexistentes[] = $producto['producto_id'];
                } else {
                    $productosTraspasados[] = $producto;
                    $productosTraspasadosDelete[] = $producto['producto_id'];
                }
            }

            $traspaso = $this->traspasos->create($inputTraspasos);

            foreach ($productosTraspasados as $producto) {
                $inputProdTraspasos['traspaso_id'] = $traspaso->id;
                $inputProdTraspasos['producto_id'] = $producto['producto_id'];
                $inputProdTraspasos['cantidad'] = $producto['cantidad'];
                $inputProdTraspasos['cantidad_recibida'] = $producto['cantidad_recibida'];

                $this->traspasosProductos->create($inputProdTraspasos);
            }

            if (!empty($productosTraspasadosDelete)) {
                $this->productosPendientes
                    ->where('sucursal_origen', $sucursal_origen_id)
                    ->where('sucursal_destino', $sucursal_destino_id)
                    ->whereIn('producto_id', $productosTraspasadosDelete)
                    ->delete();
            } else {
                Log::info('No existen productos existentes en productosPendientes.');
            }

            $usuarioAutorizado = $this->usuariosAutorizados 
            ->whereJsonContains('configuracion->sucursales', (int) $sucursal_destino_id)
            ->first();
            
            if (!$usuarioAutorizado) {
                return response()->json([
                    'status' => false,
                    'message' => "No se encontró un usuario autorizado para la sucursal destino.",
                    'results' => null
                ], 404);
            }

            $usuario_id = $usuarioAutorizado->user_id;

            $traspasoConDetalle = $this->traspasos->with(['sucursalOrigen', 'sucursalDestino', 'empleado', 'traspasoProductos'])->find($traspaso->id);

            $notificacion = [
                'usuario_id' => $usuario_id,
                'traspaso_id' => $traspasoConDetalle->id,
                'titulo' => "Nuevo traspaso desde sucursal " . $traspaso->sucursalOrigen->nombre,
                'mensaje' => "Se ha creado correctamente el traspaso con el ID: " . $traspasoConDetalle->id
            ];
            $this->sendNotification($notificacion);

            return response()->json([
                'status' => true,
                'results' => $traspasoConDetalle,
                'productos_inexistentes_ids' => $productosInexistentes,
                'message' => "Traspaso guardado correctamente",
            ], 200);
        } catch (\Exception $e) {
            Log::info("TraspasosController->saveTraspaso() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] TraspasosController->saveTraspaso() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    public function recibirTraspaso(Request $request)
    {
        try {

            $productosInexistentes = [];

            $traspaso = $this->traspasos->find($request->traspaso_id);

            if ($traspaso->estatus != 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'El traspaso no tiene un estatus valido.'
                ], 200);
            }

            $traspaso->estatus = 2;
            $traspaso->comentarios = $request->comentarios ?? $traspaso->comentarios;
            $traspaso->empleado_id = $request->empleado_id;
            $traspaso->save();

            $productosRecibidos = [];
            foreach ($request->productos as $productoData) {
                $producto = Productos::find($productoData['id']);

                if (!$producto) {
                    $productosInexistentes[] = $productoData['id'];
                    continue;
                }

                $productoTraspaso = $this->traspasosProductos->create([
                    'traspaso_id' => $traspaso->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $productoData['cantidad'],
                    'cantidad_recibida' => $productoData['cantidad_recibida'],
                    'foto' => isset($productoData['foto']) ? $productoData['foto']->store('traspasos/fotos', 'public') : null
                ]);

                $productosRecibidos[] = $productoTraspaso;

                $inventario = Inventario::where('sucursal_id', $traspaso->sucursal_destino_id)
                    ->where('producto_id', $producto->id)
                    ->first();

                if ($inventario) {
                    $inventario->cantidad_disponible += $productoData['cantidad_recibida'];
                    $inventario->cantidad_total += $productoData['cantidad_recibida'];
                    $inventario->save();
                } else {
                    Inventario::create([
                        'sucursal_id' => $traspaso->sucursal_destino_id,
                        'producto_id' => $producto->id,
                        'cantidad_disponible' => $productoData['cantidad_recibida'],
                        'cantidad_total' => $productoData['cantidad_recibida'],
                        'fecha_elaboracion' => $productoData['fecha_elaboracion'],
                        'fecha_caducidad' => $productoData['fecha_caducidad'],
                        'estatus' => 1
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'results' => [
                    'traspaso' => $traspaso,
                    'productos_recibidos' => $productosRecibidos,
                    'productos_inexistentes_ids' => $productosInexistentes

                ],
                'message' => 'Productos recibidos y traspaso actualizado correctamente.'
            ], 200);
        } catch (\Exception $e) {
            Log::info("TraspasosController->recibirTraspaso() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] TraspasosController->recibirTraspaso() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }


    /**
     * Summary of productosPendientesTraspaso
     * @param mixed $sucursalId
     * @return mixed
     */
    public function productosPendientesTraspaso($sucursal_origen)
    {
        try {

            $sucursal = Sucursales::find($sucursal_origen);

            if (!$sucursal) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sucursal no existe.'
                ], 404);
            }

            $productosPendientes = $this->productosPendientes
                ->where('sucursal_origen', $sucursal->id)
                ->select('producto_id', 'sucursal_origen', 'sucursal_destino', DB::raw('SUM(cantidad) as total_cantidad'))
                ->with(['sucursalOrigen', 'sucursalDestino', 'producto.categoria', 'producto.subcategoria'])
                ->groupBy('producto_id', 'sucursal_origen', 'sucursal_destino')
                ->get();

            $productos = $productosPendientes->map(function ($productoPendiente) {
                if (!$productoPendiente->producto) {
                    return null;
                }

                return [
                    'id' => $productoPendiente->producto->id,
                    'nombre' => $productoPendiente->producto->nombre,
                    'sku' => $productoPendiente->producto->sku,
                    'categoria' => $productoPendiente->producto->categoria,
                    'subcategoria' => $productoPendiente->producto->subcategoria,
                    'cantidad' => $productoPendiente->total_cantidad,
                    'sucursal_origen' => [
                        'id' => $productoPendiente->sucursalOrigen->id ?? null,
                        'nombre' => $productoPendiente->sucursalOrigen->nombre ?? null,
                    ],
                    'sucursal_destino' => [
                        'id' => $productoPendiente->sucursalDestino->id ?? null,
                        'nombre' => $productoPendiente->sucursalDestino->nombre ?? null,
                    ],
                ];
            })->filter();

            return response()->json([
                'status' => true,
                'results' => [
                    'sucursal_origen' => $sucursal->id,
                    'nombre' => $sucursal->nombre,
                    'productos' => $productos
                ],
                'message' => 'Productos pendientes.'
            ], 200);
        } catch (\Exception $e) {
            Log::info("TraspasosController->traspasosPendientes() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] TraspasosController->traspasosPendientes() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }


    public function registrarPendientesTraspaso(Request $request)
    {
        try {

            $sucursal_origen = $request->sucursal_origen_id;
            $sucursal_destino = $request->sucursal_destino_id;
            $producto_id = $request->producto_id;
            $cantidad = $request->cantidad;

            $pendienteRegistrado = $this->productosPendientes->updateOrCreate(
                ['id' => $request->id],
                [
                    'sucursal_origen' => $sucursal_origen,
                    'sucursal_destino' => $sucursal_destino,
                    'producto_id' => $producto_id,
                    'cantidad' => $cantidad,
                ]
            );

            return response()->json([
                'status' => true,
                'results' => $pendienteRegistrado,
            ], 200);
        } catch (\Exception $e) {
            Log::info("TraspasosController->saveTraspaso() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] TraspasosController->saveTraspaso() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function sendNotification($request)
    {
        try {

            $this->notificaciones->create([
                'usuario_id' => $request['usuario_id'],
                'trapaso_id' => $request['traspaso_id'],
                'titulo' => $request['titulo'],
                'mensaje' => $request['mensaje'],
                'leido' => $request['leido'] ?? 0
            ]);

            $FcmToken = $this->usuarios->whereNotNull('device_token')->where('id', $request['usuario_id'])->first();

            if ($FcmToken) {
                $firebase = (new Factory)->withServiceAccount(storage_path() . '/app/apetit-60f6a-firebase-adminsdk-fbsvc-cb77effb73.json');

                $messaging = $firebase->createMessaging();
                $message = CloudMessage::withTarget('token', $FcmToken->device_token)
                    ->withNotification(["title" => $request['titulo'], "body" => $request['mensaje']])
                    ->withAndroidConfig(
                        AndroidConfig::fromArray(['notification' => ["title" => $request['titulo'], "body" => $request['mensaje'], 'channel_id' => 'general']])
                            ->withSound('cash.mp3')
                    )
                    ->withApnsConfig(
                        ApnsConfig::new()
                            ->withApsField('alert', ["title" => $request['titulo'], "body" => $request['mensaje']])
                            ->withSound('cash.aiff')
                            ->withBadge(1)
                    );

                $messaging->send($message);
            }

            return ['success' => true];
        } catch (\Throwable $e) {
            Log::info("[TraspasosController-sendNotification] - ERROR " . $e->getMessage());
            report($e);

            return ['success' => false];
        }
    }
}
