<?php

namespace Ongoing\Inventarios\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Log;
use Illuminate\Support\Facades\Validator;

use Ongoing\Inventarios\Repositories\InventarioRepositoryEloquent;
use Ongoing\Inventarios\Entities\Inventario; 

use Illuminate\Http\Request;
use Ongoing\Sucursales\Repositories\SucursalesRepositoryEloquent;

class InventarioController extends Controller
{
    protected $inventario;
    protected $sucursales;

    /**
     * Summary of __construct
     * @param \Ongoing\Inventarios\Repositories\InventarioRepositoryEloquent $inventario
     * @param \Ongoing\Sucursales\Repositories\SucursalesRepositoryEloquent $sucursales
     */
    public function __construct(
        InventarioRepositoryEloquent $inventario,
        SucursalesRepositoryEloquent $sucursales
    ) {
        $this->inventario = $inventario;
        $this->sucursales = $sucursales;
    }

    /**
     * Summary of index
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    function index() {
        Gate::authorize('access-granted', '/inventarios/inventario');
        return view('inventarios::inventario');
    }


    /**
     * Summary of getProductosConExistencias
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getProductosConExistencias(Request $request)
    {

        try {
            // Obtener productos con existencias
        $productos = Inventario::with(['producto' => function ($query) {
            $query->where('estatus', 1); // Producto activo
        }])
        ->where('sucursal_id', $request->sucursal_id)
        ->where('cantidad_disponible', '>', 0)
        ->where('estatus', '>', 0)
        ->get();

        // Filtrar productos activos
        $productos = $productos->filter(function ($inventario) {
            return $inventario->producto !== null; // Asegurarse de que el producto esté activo
        });

        // Formatear la respuesta
        $productosList = $productos->map(function ($inventario) {
            return [
                'id' => $inventario->id,
                'sucursal_id' => $inventario->sucursal_id,
                'producto_id' => $inventario->producto_id,
                'cantidad_total' => $inventario->cantidad_total,
                'cantidad_existente' => $inventario->cantidad_disponible,
                'fecha_caducidad' => $inventario->fecha_caducidad,
                'estatus' => $inventario->estatus,
                'estatus_desc' => $inventario->estatus === 1 ? 'activo' : 'inactivo',
                'producto' => [
                    'id' => $inventario->producto->id,
                    'nombre' => $inventario->producto->nombre,
                    'sku' => $inventario->producto->sku,
                    'estatus' => $inventario->producto->estatus,
                ],
            ];
        });

        return response()->json([
            'status' => true,
            'results' => $productosList
        ], 200);

        } catch (\Exception $e) {
            Log::info("InventarioController->getProductosConExistencias() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] InventarioController->getProductosConExistencias() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    /**
     * Summary of agregarInventarios
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function agregarInventarios(Request $request)
    {
        try {

        // Validaciones
        $validator = Validator::make($request->all(), [
            'sucursal_id' => 'required|exists:sucursales,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.fecha_caducidad' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => true,
                'message' => "Algun producto no existe en la base de datos",
                "info" => $validator->errors(),
            ], 400);
        }

        $sucursal_id = $request->sucursal_id;

        foreach ($request->productos as $producto) {
            $cantidad = $producto['cantidad'];

            for ($i = 0; $i < $cantidad; $i++) {
                $this->inventario->create([
                    'sucursal_id' => $sucursal_id,
                    'producto_id' => $producto['id'],
                    'cantidad_total' => 1,
                    'cantidad_disponible' => 1,
                    'fecha_caducidad' => $producto['fecha_caducidad'],
                    'estatus' => 1,
                ]);
            }
        }

            return response()->json([
                'status' => true,
                'message' => "Productos ingresados correctamente"
            ], 200);

        } catch (\Exception $e) {
            Log::info("InventarioController->agregarInventarios() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] InventarioController->agregarInventarios() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    /**
     * Summary of eliminarInventarios
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function eliminarInventarios(Request $request)
    {
        try {

            $rowInv = $this->inventario->find($request->inventario_id);

            $rowInv->estatus = 0;
            $rowInv->save();

            return response()->json([
                'status' => true,
                'message' => "Registro eliminado correctamente"
            ], 200);

        } catch (\Exception $e) {
            Log::info("InventarioController->eliminarInventarios() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] InventarioController->eliminarInventarios() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

}