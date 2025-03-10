<?php

namespace Ongoing\Inventarios\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Log;

use Ongoing\Inventarios\Repositories\CategoriasRepositoryEloquent;
use Ongoing\Inventarios\Repositories\SubCategoriaRepositoryEloquent;

class SubCategoryController extends Controller {
    protected $subCategorias;

    public function __construct(
        SubCategoriaRepositoryEloquent $subCategorias,
        CategoriasRepositoryEloquent $categorias
    ) {
        $this->subCategorias = $subCategorias;
        $this->categorias = $categorias;
    }
    
    function index() {
        Gate::authorize('access-granted', '/inventarios/subcategorias');
        $subCategorias = $this->getAll();
        return view('inventarios::subcategorias', ['subCategorias' => $subCategorias]);
    }

    /**
     * /api/sub-categorias
     *
     * Lista todas las subcategorías
     *
     * @return JSON
     **/
    public function getAll(){
        try {
            $subCategorias = $this->subCategorias->with('categoria')->where(['estatus' => 1])->get();

            return response()->json([
                'status' => true,
                'results' => $subCategorias
            ], 200);
        } catch (\Exception $e) {
            Log::info("SubCategoryController->getAll() | " . $e->getMessage(). " | " . $e->getLine());
            
            return response()->json([
                'status' => false,
                'message' => "[ERROR] SubCategoryController->getAll() | " . $e->getMessage(). " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    /**
     * /api/sub-categorias/one/{id}
     *
     * Lista todas las subcategorías por ID
     *
     * @return JSON
     **/
    public function getOne($id){
        try {
            $subCategoria = $this->subCategorias->where(['id' => $id, 'estatus' => 1])->first();

            return response()->json([
                'status' => true,
                'results' => $subCategoria
            ], 200);
        } catch (\Exception $e) {
            Log::info("SubCategoryController->getOne() | " . $e->getMessage(). " | " . $e->getLine());
            
            return response()->json([
                'status' => false,
                'message' => "[ERROR] SubCategoryController->getOne() | " . $e->getMessage(). " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    /**
     * /api/sub-categorias/categoria/{id}
     *
     * Lista todas las subcategorías por ID de categoría
     *
     * @return JSON
     **/
    public function getByCategory($categoryId){
        try {
            $subCategorias = $this->subCategorias->where(['categoria_id' => $categoryId, 'estatus' => 1])->get();

            return response()->json([
                'status' => true,
                'results' => $subCategorias
            ], 200);
        } catch (\Exception $e) {
            Log::info("SubCategoryController->getByCategory() | " . $e->getMessage(). " | " . $e->getLine());
            
            return response()->json([
                'status' => false,
                'message' => "[ERROR] SubCategoryController->getByCategory() | " . $e->getMessage(). " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    /**
     * /api/sub-categorias/save
     *
     * Guarda una subcategoria
     *
     * @return JSON
     **/
    public function save(Request $request){
        try {

            $values = $request->all();
            if(!empty($values['caracteristicas_json'])){
                foreach($values['caracteristicas_json'] as $k => $item){
                    if(empty($item['slug'])){
                        $item['slug'] = Str::slug($item['etiqueta']);
                    }
                    $values['caracteristicas_json'][$k] = $item;
                }
            }
            
            $this->subCategorias->updateOrCreate(['id' => $request->id], $values);

            return response()->json([
                'status' => true,
                'message' => "Sub-categoría guardada.",
                'results' => $this->subCategorias->with('categoria')->where(['estatus' => 1])->get()
            ], 200);
        } catch (\Exception $e) {
            Log::info("SubCategoryController->save() | " . $e->getMessage(). " | " . $e->getLine());
            
            return response()->json([
                'status' => false,
                'message' => "[ERROR] SubCategoryController->save() | " . $e->getMessage(). " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    
    
}
