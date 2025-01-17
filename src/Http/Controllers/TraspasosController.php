<?php

namespace Ongoing\Inventarios\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Log;
use Ongoing\Inventarios\Entities\Inventario;
use Ongoing\Inventarios\Repositories\TraspasosProductosRepositoryEloquent;
use Ongoing\Inventarios\Repositories\TraspasosRepositoryEloquent;
use Ongoing\Inventarios\Repositories\ProductosPendientesTraspasoRepositoryEloquent;
use Ongoing\Inventarios\Entities\Productos;
use Ongoing\Sucursales\Entities\Sucursales;
use Illuminate\Support\Facades\DB;

class TraspasosController extends Controller
{
    protected $traspasos;
    protected $traspasosProductos;
    protected $productosPendientes;

    public function __construct(
        TraspasosRepositoryEloquent $traspasos,
        TraspasosProductosRepositoryEloquent $traspasosProductos,
        ProductosPendientesTraspasoRepositoryEloquent $productosPendientes,
    ) {
        $this->traspasos = $traspasos;
        $this->traspasosProductos = $traspasosProductos;
        $this->productosPendientes = $productosPendientes;
    }

    public function getTraspaso($traspaso_id)
    {
        try {

            $traspaso = $this->traspasos->with(['sucursalOrigen', 'sucursalDestino', 'empleado', 'traspasoProductos'])->find($traspaso_id);

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
            $inputTraspasos = [];
            $inputProdTraspasos = [];
            $productosInexistentes = [];
            $productosTraspasados = [];

            $inputTraspasos['sucursal_origen_id'] = $request->sucursal_origen_id;
            $inputTraspasos['sucursal_destino_id'] = $request->sucursal_destino_id;
            $inputTraspasos['tipo'] = $request->tipo;
            $inputTraspasos['empleado_id'] = $request->empleado_id;
            $inputTraspasos['comentarios'] = $request->comentarios;

            foreach ($request->productos as $producto) {
                $productoExistente = Productos::find($producto['producto_id']);

                if (!$productoExistente) {
                    $productosInexistentes[] = $producto['producto_id'];
                } else {
                    $productosTraspasados[] = $producto;
                }
            }

            $traspaso = $this->traspasos->create($inputTraspasos);

            foreach ($productosTraspasados as $producto) {
                $inputProdTraspasos['traspaso_id'] = $traspaso->id;
                $inputProdTraspasos['producto_id'] = $producto['producto_id'];
                $inputProdTraspasos['cantidad'] = $producto['cantidad'];
                $inputProdTraspasos['cantidad_recibida'] = $producto['cantidad_recibida'];

                $this->traspasosProductos->create($inputProdTraspasos);

                // productos_pendientes_traspaso eliminar los registros que coincidan (sucursal_origen, sucurasl_destino, id del producto)
            }

            // Al 

            $traspasoConDetalle = $this->traspasos->with(['sucursalOrigen', 'sucursalDestino', 'empleado', 'traspasoProductos'])->find($traspaso->id);

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
    public function productosPendientesTraspaso($sucursalId)
    {

        try {

            $sucursal = Sucursales::find($sucursalId);

            if (!$sucursal) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sucursal no existe.'
                ], 404);
            }

            $productosPendientes = $this->productosPendientes->with('producto')
                // cambiar sucursla_id por sucurasl origen
                ->where('sucursal_id', $sucursal->id)
                // cambiar count por SUM para cantidad total de producto
                ->select('producto_id', DB::raw('COUNT(*) as cantidad'))
                ->groupBy('producto_id')
                ->get();

                //renombrar sucursa_id por sucursal_origen
                //agregar columna sucursal_destino

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
                    'cantidad' => $productoPendiente->cantidad
                ];
            })->filter();

            return response()->json([
                'status' => true,
                'results' => [
                    'sucursal_id' => $sucursal->id,
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

}
