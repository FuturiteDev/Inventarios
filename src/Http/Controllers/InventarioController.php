<?php

namespace Ongoing\Inventarios\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Log;

use Ongoing\Inventarios\Repositories\ColeccionesRepositoryEloquent;
use Ongoing\Inventarios\Repositories\InventarioRepositoryEloquent;
use Ongoing\Inventarios\Entities\Inventario; 

use Illuminate\Http\Request;
use Ongoing\Inventarios\Repositories\InventarioRepository;
use Ongoing\Sucursales\Repositories\SucursalesRepositoryEloquent;

class InventarioController extends Controller
{
    protected $inventario;
    protected $sucursales;

    public function __construct(
        InventarioRepositoryEloquent $inventario,
        SucursalesRepositoryEloquent $sucursales
    ) {
        $this->inventario = $inventario;
        $this->sucursales = $sucursales;
    }

    function index() {
        Gate::authorize('access-granted', '/inventarios/inventario');
        return view('inventarios::inventario');
    }


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

}
