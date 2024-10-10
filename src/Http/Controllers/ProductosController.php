<?php

namespace Ongoing\Inventarios\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Storage;
use File;
use Log;

use Ongoing\Inventarios\Repositories\ProductosMultimediaRepositoryEloquent;
use Ongoing\Inventarios\Repositories\ProductosRepositoryEloquent;
use Ongoing\Inventarios\Repositories\ColeccionesProductosRepositoryEloquent;

class ProductosController extends Controller {
    protected $productos;
    protected $productosMultimedia;
    protected $colecciones_productos;


    public function __construct(
        ProductosRepositoryEloquent $productos,
        ProductosMultimediaRepositoryEloquent $productosMultimedia,
        ColeccionesProductosRepositoryEloquent $colecciones_productos
    ) {
        $this->productos = $productos;
        $this->productosMultimedia = $productosMultimedia;
        $this->colecciones_productos = $colecciones_productos;
        $this->middleware('menu.active');
    }

    function index() {
        return view('admin.productos');
    }

    /**
     * /api/productos/all
     *
     * Lista todas productos activos
     *
     * @return JSON
     **/
    public function getAll()
    {
        try {
            $productos = $this->productos
            ->with(["categoria", "subcategoria", "colecciones"])
            ->where(['estatus' => 1])->get();

            return response()->json([
                'status' => true,
                'results' => $productos
            ], 200);
        } catch (\Exception $e) {
            Log::info("ProductosController->getAll() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] ProductosController->getAll() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    /**
     * /api/productos/save
     *
     * Guarda/Actualiza un producto
     *
     * @return JSON
     **/
    public function save(Request $request)
    {
        try {
            $input = $request->except(['colecciones']);
            $sku = $request->sku;
            $productoId = $request->id;
        
            // SKU Validación
            if (!empty($sku)) {

                // Buscar el producto por su SKU
                $productoExistente = $this->productos
                ->where('sku', $sku)
                ->where("estatus", 1)
                ->first();
        
                if ($productoExistente) {
                    // SKU existe y no recibe ID de producto, retornar error
                    if (empty($productoId)) {
                        return response()->json([
                            'status' => false,
                            'message' => "El SKU ya está en uso por otro producto. Proporcione un ID de producto para actualizar.",
                        ], 200);
                    } else {

                        // SKU existe y recibe ID de producto
                        if ($productoExistente->id == $productoId) {

                            // 2.1 - $productoExistente->id == $request->id, entonces modificas el registro
                            $producto = $this->productos->updateOrCreate(['id' => $productoId], $input);
                        } else {

                            // 2.2 - $productoExistente->id != $request->id, entonces retorna error
                            return response()->json([
                                'status' => false,
                                'message' => "El SKU ya está en uso por otro producto con un ID diferente.",
                            ], 200);
                        }
                    }
                } else {

                    // 3 - SKU no existe
                    // Si llega $request->id, actualizas
                    if (!empty($productoId)) {
                        $producto = $this->productos->updateOrCreate(['id' => $productoId], $input);
                    } else {

                        // Si no llega $request->id, insertas
                        $producto = $this->productos->create($input);
                    }
                }

                //Se agregan o eliminan Colecciones
                $this->colecciones_productos->where([
                    'producto_id' => $producto->id,
                ])->delete();
        
                $colecciones = $request->colecciones;
                if(!empty($colecciones)){
                    foreach ($colecciones as $c_id) {
                        $this->colecciones_productos->updateOrCreate([
                            "coleccion_id" => $c_id,
                            "producto_id" => $producto->id,
                        ], [
                            "estatus" => 1
                        ]);
                    }
                }
        
                return response()->json([
                    'status' => true,
                    'message' => "Producto guardado.",
                ], 200);
            } else {

                // SKU está vacío
                return response()->json([
                    'status' => false,
                    'message' => "SKU no puede estar vacío.",
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "[ERROR] ProductosController->save() | " . $e->getMessage() . " | " . $e->getLine(),
            ], 500);
        }
        
    }

    /**
     * /api/productos/delete
     *
     * Deshabilita un producto
     *
     * @return JSON
     **/
    public function delete(Request $request) {
        try {

            $this->productos->where('id', $request->producto_id)->update(['estatus' => 0]);

            return response()->json([
                'status' => true,
                'message' => "Producto eliminado.",
                'results' => $this->productos->where('estatus', 1)->get()
            ], 200);
        } catch (\Exception $e) {
            Log::info("ProductosController->delete() | " . $e->getMessage() . " | " . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => "[ERROR] ProductosController->delete() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    /**
     * /api/productos/{productoId}/multimedia/all
     *
     * Lista todas los archivos multimedia activos de un producto
     *
     * @return JSON
     **/
    public function getAllMultimedia($productoId) {
        try {

            $productosMultimedia = $this->productosMultimedia->where(['estatus' => 1, 'producto_id' => $productoId])->get();

            return response()->json([
                'status' => true,
                'results' => $productosMultimedia
            ], 200);

        } catch (\Exception $e) {
            Log::info("ProductosController->getAllMultimedia() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] ProductosController->getAllMultimedia() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    /**
     * /api/productos/multimedia/save
     *
     * Guarda un archivo vinculado a un producto
     *
     * @return JSON
     **/
    public function saveMultimedia(Request $request){
        try {

            $file = $request->file('archivo');
            $fileName = md5(date("H:i:s"))."_image.".$file->getClientOriginalExtension();
            Storage::disk('local')->putFileAs('public/productos/multimedia', $request->file('archivo'), $fileName);


            $this->productosMultimedia->updateOrCreate(['id' => $request->id], [
                "producto_id" => $request->producto_id,
                "file_name" => $fileName,
                "path" => "/storage/productos/multimedia/" . $fileName,
                "url" => "/storage/productos/multimedia/" . $fileName
            ]);

            return response()->json([
                'status' => true,
                'message' => "Archivo multimedia guardado.",
                'results' => $this->productosMultimedia->where(['estatus' => 1, 'producto_id' => $request->producto_id])->get()
            ], 200);
        } catch (\Exception $e) {
            Log::info("ProductosController->saveMultimedia() | " . $e->getMessage(). " | " . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => "[ERROR] ProductosController->saveMultimedia() | " . $e->getMessage(). " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    /**
     * /api/productos/multimedia/delete
     *
     * Elimina un archivo vinculado a un producto
     *
     * @return JSON
     **/
    public function deleteMultimedia(Request $request)
    {
        try {
            $this->productosMultimedia->update(['estatus' => 0], $request->productos_multimedia_id);

            return response()->json([
                'status' => true,
                'message' => "Archivo multimedia eliminado.",
            ], 200);
        } catch (\Exception $e) {
            Log::info("ProductosController->deleteMultimedia() | " . $e->getMessage() . " | " . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => "[ERROR] ProductosController->deleteMultimedia() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }
}
