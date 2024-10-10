<?php

namespace Ongoing\Inventarios\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Ongoing\Inventarios\Repositories\ColeccionesRepositoryEloquent;
use Ongoing\Inventarios\Repositories\ColeccionesProductosRepositoryEloquent;
use Ongoing\Inventarios\Repositories\ProductosRepositoryEloquent;
use Log;

class ColeccionesController extends Controller
{
    protected $colecciones;
    protected $coleccion_productos;
    protected $productos;

    public function __construct(
        ColeccionesRepositoryEloquent $colecciones,
        ColeccionesProductosRepositoryEloquent $coleccion_productos,
        ProductosRepositoryEloquent $productos
    ) {
        $this->colecciones = $colecciones;
        $this->coleccion_productos = $coleccion_productos;
        $this->productos = $productos;
        $this->middleware('menu.active');
    }

    function index()
    {
        return view('admin.colecciones');
    }

    /**
     * /api/colecciones/all
     *
     * Lista todas las colecciones activas
     *
     * @return JSON
     **/
    public function getAll()
    {
        try {
            $colecciones = $this->colecciones->where('estatus', 1)->get();

            return response()->json([
                'status' => true,
                'results' => $colecciones
            ], 200);
        } catch (\Exception $e) {
            Log::info("ColeccionesController->getAll() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] ColeccionesController->getAll() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    /**
     * /api/colecciones/productos/{coleccion_id}
     *
     * Lista los productos relacionados a una colección
     *
     * @return JSON
     **/
    public function getProductosColeccion($coleccion_id)
    {
        try {
            $coleccion = $this->colecciones->with(['productos' => ['categoria', 'subcategoria']])->find($coleccion_id);

            return response()->json([
                'status' => true,
                'results' => $coleccion->productos
            ], 200);
        } catch (\Exception $e) {
            Log::info("ColeccionesController->getProductosColeccion() | " . $e->getMessage() . " | " . $e->getFile() . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] ColeccionesController->getProductosColeccion() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    /**
     * @api
     *
     * Agregar/Modificar colecciones
     *
     * @param Int $id
     * @param String $nombre
     * @param String $descripcion
     *
     * @return JSON
     **/
    public function saveColecciones(Request $request)
    {
        try {
            $info = $request->except(['id']);
            $colecciones = $this->colecciones->updateOrCreate(["id" => $request->id], $info);

            return response()->json([
                "code" => 200,
                "results" => $colecciones
            ], 200);
        } catch (\Exception $e) {
            Log::info("ColeccionesController->saveColecciones() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] ColeccionesController->saveColecciones() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }


    /**
     * /api/coleccciones/delete
     *
     * Deshabilita una colección
     *
     * @return JSON
     **/
    public function delete(Request $request)
    {
        try {

            $this->colecciones->where('id', $request->coleccion_id)->update(['estatus' => 0]);

            return response()->json([
                'status' => true,
                'message' => "Colección eliminada.",
                'results' => $this->colecciones->where('estatus', 1)->get()
            ], 200);
        } catch (\Exception $e) {
            Log::info("ColeccionesController->delete() | " . $e->getMessage() . " | " . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => "[ERROR] ColeccionesController->delete() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }
}
