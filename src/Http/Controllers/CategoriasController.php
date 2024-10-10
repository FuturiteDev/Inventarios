<?php

namespace Ongoing\Inventarios\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Log;

use Ongoing\Inventarios\Repositories\CategoriasRepositoryEloquent;

class CategoriasController extends Controller
{
    protected $categorias;

    public function __construct(
        CategoriasRepositoryEloquent $categorias
    ) {
        $this->categorias = $categorias;
    }

    function index() {
        Gate::authorize('access-granted', '/inventarios/categorias');
        return view('inventarios::categorias');
    }

    /**
     * /api/categorias/all
     *
     * Lista todas las categorías activas
     *
     * @return JSON
     **/
    public function getCategorias()
    {
        try {
            $categorias = $this->categorias->where(['estatus' => 1])->get();

            return [
                'status' => true,
                'results' => $categorias
            ];
        } catch (\Exception $e) {
            Log::info("CategoriasController->getCategorias() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] CategoriasController->getCategorias() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    /**
     *
     * Agrega o modfica una categoria a la BD
     *
     * @param int    id
     * @param string nombre
     * @param string descripcion
     * @param string estatus
     *
     *
     * @return json object
     *
     */

    public function saveCategorias(Request $request)
    {
        try {
            $info = $request->except(['id']);

            switch ($request->action) {
                case 1:
                    $this->categorias->create($info);

                    $message = 'Categoría creada con éxito.';
                    break;

                case 2:
                    $categoria = $this->categorias->find($request->id);
                    $categoria->fill($info);
                    $categoria->save();

                    $message = 'Categoría actualizada con éxito.';
                    break;

                case 3:
                    $categoria = $this->categorias->find($request->id);
                    $categoria->estatus = 0;
                    $categoria->save();

                    $message = 'Categoría eliminada con éxito.';
                    break;

                default:
                    $message = 'Acción no válida';
                    break;
            }

            return response()->json([
                'status' => true,
                'results' => $this->getCategorias(),
                "message"   => $message,
            ], 200);
        } catch (\Exception $e) {
            Log::info("CategoriasController->saveCategorias() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] CategoriasController->saveCategorias() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }
}
