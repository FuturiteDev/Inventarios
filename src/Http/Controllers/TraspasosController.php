<?php

namespace Ongoing\Inventarios\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Log;
use Ongoing\Inventarios\Entities\TraspasosProductos;
use Ongoing\Inventarios\Repositories\TraspasosProductosRepositoryEloquent;
use Ongoing\Inventarios\Repositories\TraspasosRepositoryEloquent;
use Ongoing\Inventarios\Entities\Productos;
class TraspasosController extends Controller
{
    protected $traspasos;
    protected $traspasosProductos;

    public function __construct(
        TraspasosRepositoryEloquent $traspasos,
        TraspasosProductosRepositoryEloquent $traspasosProductos
    ) {
        $this->traspasos = $traspasos;
        $this->traspasosProductos = $traspasosProductos;
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
        
                if (isset($producto['foto']) && $producto['foto']) {
                    $path = $producto['foto']->store('traspasos/fotos', 'public');
                    $inputProdTraspasos['foto'] = $path;
                }
        
                $this->traspasosProductos->create($inputProdTraspasos);
            }
        
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
}