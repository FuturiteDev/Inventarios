<?php

namespace Ongoing\Inventarios\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\NotificacionesRepositoryEloquent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Log;

use Ongoing\Inventarios\Entities\Inventario;
use Ongoing\Inventarios\Repositories\TraspasosProductosRepositoryEloquent;
use Ongoing\Inventarios\Repositories\TraspasosRepositoryEloquent;
use Ongoing\Inventarios\Repositories\ProductosPendientesTraspasoRepositoryEloquent;
use Ongoing\Inventarios\Repositories\InventarioRepositoryEloquent;
use App\Repositories\UsuariosautorizadosRepositoryEloquent;
use App\Repositories\UsuarioRepositoryEloquent;

use Ongoing\Sucursales\Entities\Sucursales;
use Ongoing\Inventarios\Events\TraspasoRecibido;
use Ongoing\Inventarios\Events\TraspasoNuevo;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Ongoing\Empleados\Entities\Empleado;

use function PHPUnit\Framework\isEmpty;

class TraspasosController extends Controller
{
    protected $usuarios;
    protected $traspasos;
    protected $traspasosProductos;
    protected $productosPendientes;
    protected $notificaciones;
    protected $usuariosAutorizados;
    protected $inventario;

    public function __construct(
        UsuarioRepositoryEloquent $usuarios,
        TraspasosRepositoryEloquent $traspasos,
        TraspasosProductosRepositoryEloquent $traspasosProductos,
        ProductosPendientesTraspasoRepositoryEloquent $productosPendientes,
        NotificacionesRepositoryEloquent $notificaciones,
        UsuariosautorizadosRepositoryEloquent $usuariosAutorizados,
        InventarioRepositoryEloquent $inventario,
    ) {
        $this->usuarios = $usuarios;
        $this->traspasos = $traspasos;
        $this->traspasosProductos = $traspasosProductos;
        $this->productosPendientes = $productosPendientes;
        $this->notificaciones = $notificaciones;
        $this->usuariosAutorizados = $usuariosAutorizados;
        $this->inventario = $inventario;
    }

    public function index()
    {
        Gate::authorize('access-granted', '/inventarios/traspasos');
        return view('inventarios::traspasos');
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
                    // 'traspasoProductos:id,foto,traspaso_id'
                ])
                ->find($traspaso_id);


            $traspasoProductos = $traspaso->traspasoProductos()->with('producto')->get();

            $detalle = [];
            foreach ($traspasoProductos->groupBy('producto_id') as $gpo) {
                $tmpProd = $gpo->first()->producto->only('id', 'nombre', 'sku');
                $tmpProd['cantidad_total'] = 0;
                $tmpProd['fechas'] = [];

                foreach ($gpo as $row) {
                    $tmpProd['cantidad_total'] += $row->cantidad;
                    $tmpProd['fechas'][] = [
                        'id' => $row->id,
                        'fecha_caducidad' => $row->fecha_caducidad,
                        'cantidad' => $row->cantidad,
                        'cantidad_recibida' => $row->cantidad_recibida,
                        'foto' => $row->foto
                    ];
                }

                $detalle[] = $tmpProd;
            }

            $traspaso->detalle = $detalle;

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

    /**
     * Retorna traspasos pendientes de la sucursal destino
     * @param mixed $sucursal_id
     */
    public function getTraspasoSucursal($sucursal_id)
    {
        try {

            $traspasos = $this->traspasos->with(['sucursalOrigen', 'sucursalDestino'])
                ->where('estatus', 1)
                ->where('sucursal_destino_id', $sucursal_id)
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

            $inputTraspasos['sucursal_origen_id'] = $sucursal_origen_id;
            $inputTraspasos['sucursal_destino_id'] = $sucursal_destino_id;
            $inputTraspasos['tipo'] = $request->tipo;
            $inputTraspasos['empleado_id'] = $request->empleado_id;
            $inputTraspasos['comentarios'] = $request->comentarios;

            if ($request->filled('asignado_a')) {
                $inputTraspasos['asignado_a'] = Empleado::where('no_empleado', $request->asignado_a)->first()?->id;
            }

            if (empty($request->productos)) {
                return response()->json([
                    'status' => false,
                    'message' => "No se han enviado productos para traspasar.",
                ], 200);
            }

            $traspaso = $this->traspasos->create($inputTraspasos);

            foreach ($request->productos as $row) {
                $inputProdTraspasos = [];
                $prodPendiente = $this->productosPendientes->find($row['producto_id']);
                if ($row['cantidad'] > 0) {
                    $inputProdTraspasos['traspaso_id'] = $traspaso->id;
                    $inputProdTraspasos['producto_id'] = $prodPendiente->producto_id;
                    $inputProdTraspasos['fecha_caducidad'] = $prodPendiente->fecha_caducidad;
                    $inputProdTraspasos['cantidad'] = $row['cantidad'];
                    $inputProdTraspasos['cantidad_recibida'] = 0;

                    if (!empty($row['foto'])) {
                        $cadena = "";
                        foreach ($row['foto'] as $byte) {
                            $cadena .= chr($byte);
                        }
                        $fname = md5(uniqid('', true)) . '.jpg';
                        Storage::disk('public')->put("traspasos/fotos/{$fname}", $cadena);
                        $inputProdTraspasos['foto'] = "traspasos/fotos/{$fname}";
                    }

                    $inv = $this->inventario->scopeQuery(function ($query) use ($row) {
                        return $query->limit($row['cantidad']);
                    })->findWhere(['sucursal_id' => $sucursal_origen_id, 'producto_id' => $prodPendiente->producto_id, 'fecha_caducidad' => $prodPendiente->fecha_caducidad, ['cantidad_disponible', '>', 0], 'estatus' => 1]);

                    $inputProdTraspasos['inventario_ids'] = $inv->modelKeys();
                    $this->traspasosProductos->create($inputProdTraspasos);

                    $inv->each(function ($item) {
                        $item->estatus = 2;
                        $item->save();
                    });
                }
                $prodPendiente->delete();
            }

            //evento para procesar el traspaso
            TraspasoNuevo::dispatch($traspaso);

            $usuarioAutorizado = $this->usuariosAutorizados
                ->whereJsonContains('configuracion->sucursales', (int) $sucursal_destino_id)
                ->first();

            $traspaso->load(['sucursalOrigen', 'sucursalDestino', 'empleado', 'traspasoProductos']);

            if ($usuarioAutorizado) {
                $usuario_id = $usuarioAutorizado->user_id;

                $notificacion = [
                    'usuario_id' => $usuario_id ?? null,
                    'traspaso_id' => $traspaso->id ?? null,
                    'titulo' => "Nuevo traspaso desde sucursal " . $traspaso->sucursalOrigen->nombre,
                    'mensaje' => "Se ha creado correctamente el traspaso con el ID: " . $traspaso->id
                ];
                $this->sendNotification($notificacion);
            }


            return response()->json([
                'status' => true,
                'results' => $traspaso,
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
            // $traspaso->empleado_id = $request->empleado_id;
            $traspaso->save();

            $productosRecibidos = [];
            $productosRechazados = [];
            foreach ($request->productos as $productoData) {
                $row = $traspaso->traspasoProductos()->find($productoData['id']);

                $row->cantidad_recibida = $productoData['cantidad_recibida'];

                if (!empty($productoData['foto'])) {
                    $cadena = "";
                    foreach ($productoData['foto'] as $byte) {
                        $cadena .= chr($byte);
                    }
                    $fname = md5(uniqid('', true)) . '.jpg';
                    Storage::disk('public')->put("traspasos/fotos/{$fname}", $cadena);
                    $row->foto = "traspasos/fotos/{$fname}";
                }

                $row->save();
                $inv_ids_rechazados = [];
                foreach ($row->inventario_ids as $i => $invId) {
                    if ($row->cantidad_recibida > $i) {
                        $inv = $this->inventario->find($invId);
                        $inv->estatus = $traspaso->tipo == 3 ? 0 : 1;
                        $inv->sucursal_id = $traspaso->sucursal_destino_id;
                        $inv->save();
                    } else {
                        $inv_ids_rechazados[] = $invId;
                    }
                }

                if ($row->cantidad_recibida < $row->cantidad) {
                    $productosRechazados[] = [
                        'producto_id' => $row->producto_id,
                        'cantidad' => $row->cantidad - $row->cantidad_recibida,
                        'cantidad_recibida' => 0,
                        'foto' => $row->foto,
                        'inventario_ids' => $inv_ids_rechazados,
                        'fecha_caducidad' => $row->fecha_caducidad
                    ];
                }
            }

            if (!empty($productosRechazados)) {
                $newTraspaso = $traspaso->replicate();
                $newTraspaso->tipo = 3;
                $newTraspaso->sucursal_origen_id = $traspaso->sucursal_destino_id;
                $matriz = Sucursales::where('matriz', 1)->where('estatus', 1)->first();
                if (!empty($matriz)) {
                    $newTraspaso->sucursal_destino_id = $matriz->id;
                } else {
                    $newTraspaso->sucursal_destino_id = $traspaso->sucursal_origen_id;
                }

                $newTraspaso->comentarios .= "\n\n Merma generada por productos rechazados en traspaso: {$traspaso->id}.";
                $newTraspaso->save();
                foreach ($productosRechazados as $row) {
                    $newTraspaso->traspasoProductos()->create($row);
                }
            }

            //evento para confirmar que se recibio el traspaso
            TraspasoRecibido::dispatch($traspaso);

            return response()->json([
                'status' => true,
                'results' => [
                    'traspaso' => $traspaso
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
                ->with(['sucursalDestino', 'producto.categoria', 'producto.subcategoria'])
                ->get();

            $grouped = $productosPendientes->groupBy('sucursal_destino');

            $productosxsucursal = [];
            foreach ($grouped as $productosPendienteSuc) {
                $tmpProductosSuc = [
                    'sucursal_destino_id' => $productosPendienteSuc->first()->sucursalDestino->id,
                    'sucursal_destino_nombre' => $productosPendienteSuc->first()->sucursalDestino->nombre,
                    'productos' => []
                ];
                $tmpProductosPend = [];
                foreach ($productosPendienteSuc as $row) {
                    if (empty($tmpProductosPend[$row->producto->id])) {
                        $tmpProductosPend[$row->producto->id] = [
                            'producto_id' => $row->producto->id,
                            'nombre' => $row->producto->nombre,
                            'sku' => $row->producto->sku,
                            'categoria' => $row->producto->categoria->only(['id', 'nombre', 'imagen']),
                            'subcategoria' => $row->producto->subcategoria->only(['id', 'nombre']),
                            'cantidad' => 0,
                            'fechas' => []
                        ];
                    }

                    $tmpProductosPend[$row->producto->id]['cantidad'] += $row->cantidad;

                    $stockProd = $this->inventario->findWhere(['sucursal_id' => $sucursal->id, 'producto_id' => $row->producto->id, ['cantidad_disponible', '>', 0], 'fecha_caducidad' => $row->fecha_caducidad]);

                    $tmpProductosPend[$row->producto->id]['fechas'][] = [
                        'producto_pendiente_id' => $row->id,
                        'fecha_caducidad' => $row->fecha_caducidad,
                        'cantidad' => $row->cantidad,
                        'stock' => $stockProd->sum('cantidad_disponible')
                    ];
                }

                $tmpProductosSuc['productos'] = array_values($tmpProductosPend);

                $productosxsucursal[] = $tmpProductosSuc;
            };

            return response()->json([
                'status' => true,
                'results' => [
                    'sucursal_origen_id' => $sucursal->id,
                    'sucursal_origen_nombre' => $sucursal->nombre,
                    'productos_pendientes' => array_values($productosxsucursal)
                ]
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
            $fecha_caducidad = $request->fecha_caducidad;

            if (!empty($request->id)) {
                $prodPendiente = $this->productosPendientes->find($request->id);
                $prodPendiente->cantidad = $cantidad;
                $prodPendiente->save();
            } else {
                $prodPendiente = $this->productosPendientes->findWhere(['sucursal_origen' => $sucursal_origen, 'sucursal_destino' => $sucursal_destino, 'producto_id' => $producto_id, 'fecha_caducidad' => $fecha_caducidad])->first();
                if (!empty($prodPendiente)) {
                    $prodPendiente->cantidad += $cantidad;
                    $prodPendiente->save();
                } else {
                    $prodPendiente = $this->productosPendientes->create(
                        [
                            'sucursal_origen' => $sucursal_origen,
                            'sucursal_destino' => $sucursal_destino,
                            'producto_id' => $producto_id,
                            'cantidad' => $cantidad,
                            'fecha_caducidad' => $fecha_caducidad
                        ]
                    );
                }
            }

            return response()->json([
                'status' => true,
                'results' => $prodPendiente,
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
     * @return mixed
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

    public function listTraspasos()
    {
        try {

            $traspasos = $this->traspasos->with(['sucursalOrigen', 'sucursalDestino'])
                ->where('estatus', 1)
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


    public function listTraspasosPorChofer(Request $request)
    {
        try {
            $traspasos = $this->traspasos->with(['sucursalOrigen', 'sucursalDestino', 'traspasoProductos'])
                ->where('asignado_a', $request->empleado_id)
                ->where('estatus', 1)
                ->get();

            return response()->json([
                'status' => true,
                'results' => $traspasos,
                'message' => 'Traspasos asignados al chofer obtenidos correctamente.',
            ], 200);
        } catch (\Exception $e) {
            Log::info("TraspasosController->listTraspasosPorChofer() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] TraspasosController->listTraspasosPorChofer() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }


    public function cancelarTraspaso($traspaso_id)
    {
        try {
            $traspaso = $this->traspasos->where('estatus', '!=', 0)->find($traspaso_id);

            if (!$traspaso) {
                return response()->json([
                    'status' => false,
                    'message' => "El traspaso no existe o ya ha sido cancelado.",
                ], 200);
            }

            $traspaso->estatus = 0;
            $traspaso->save();

            return response()->json([
                'status' => true,
                'message' => "Traspaso eliminado correctamente.",
            ], 200);
        } catch (\Exception $e) {
            Log::info("TraspasosController->cancelarTraspaso() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] TraspasosController->cancelarTraspaso() | " . $e->getMessage() . " | " . $e->getLine(),
            ], 500);
        }
    }
}
