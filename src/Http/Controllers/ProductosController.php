<?php

namespace Ongoing\Inventarios\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Storage;
use File;
use Log;
use Ongoing\Inventarios\Entities\Inventario;
use Ongoing\Inventarios\Repositories\ProductosMultimediaRepositoryEloquent;
use Ongoing\Inventarios\Repositories\ProductosRepositoryEloquent;
use Ongoing\Inventarios\Repositories\ColeccionesProductosRepositoryEloquent;
use Ongoing\Inventarios\Repositories\ProductosPendientesTraspasoRepositoryEloquent;
use Ongoing\Inventarios\Repositories\InventarioRepositoryEloquent;
use Ongoing\Sucursales\Entities\Sucursales;

use function PHPUnit\Framework\isEmpty;

class ProductosController extends Controller
{
    protected $productos;
    protected $productosMultimedia;
    protected $colecciones_productos;
    protected $productosPendientesTraspaso;


    public function __construct(
        ProductosRepositoryEloquent $productos,
        ProductosMultimediaRepositoryEloquent $productosMultimedia,
        ColeccionesProductosRepositoryEloquent $colecciones_productos,
        ProductosPendientesTraspasoRepositoryEloquent $productosPendientesTraspaso
    ) {
        $this->productos = $productos;
        $this->productosMultimedia = $productosMultimedia;
        $this->colecciones_productos = $colecciones_productos;
        $this->productosPendientesTraspaso = $productosPendientesTraspaso;
    }

    function index()
    {
        Gate::authorize('access-granted', '/inventarios/productos');
        return view('inventarios::productos');
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
                if (!empty($colecciones)) {
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
    public function delete(Request $request)
    {
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
    public function getAllMultimedia($productoId)
    {
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
    public function saveMultimedia(Request $request)
    {
        try {

            $file = $request->file('archivo');
            $fileName = md5(date("H:i:s")) . "_image." . $file->getClientOriginalExtension();
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
            Log::info("ProductosController->saveMultimedia() | " . $e->getMessage() . " | " . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => "[ERROR] ProductosController->saveMultimedia() | " . $e->getMessage() . " | " . $e->getLine(),
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

    /**
     * /api/productos/{productoId}/multimedia/all
     *
     * Lista todas los archivos multimedia activos de un producto
     *
     * @return JSON
     **/
    public function getProductsSubcategory($subcategory_id)
    {
        try {

            $products = $this->productos
                ->where(['estatus' => 1, 'subcategoria_id' => $subcategory_id])
                ->select(['id', 'nombre', 'categoria_id', 'subcategoria_id'])
                ->get();

            return response()->json([
                'status' => true,
                'results' => $products
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

    public function searchProducts(Request $request)
    {
        try {
            if (empty($request->sku) && empty($request->nombre)) {
                $products = $this->productos
                    ->where('estatus', 1)
                    ->select(['id', 'nombre', 'categoria_id', 'subcategoria_id'])
                    ->take(10)
                    ->get();
            } else {
                $products = $this->productos
                    ->where(['estatus' => 1, 'sku' => $request->sku, 'nombre' => $request->nombre])
                    ->select(['id', 'nombre', 'categoria_id', 'subcategoria_id'])
                    ->get();
            }

            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'results' => "Producto no encontrado"
                ], 200);
            }

            return response()->json([
                'status' => true,
                'results' => $products
            ], 200);
        } catch (\Exception $e) {
            Log::info("ProductosController->searchProducts() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] ProductosController->searchProducts() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

    public function registrarProductosTraspaso(Request $request)
    {
        try {

            // Verificar si ya existe un registro con el mismo producto_id y sucursal_id
            $existe = $this->productosPendientesTraspaso->where('producto_id', $request->producto_id)
            ->where('sucursal_id', $request->sucursal_id)
            ->exists();

            if ($existe) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ya existe un registro pendiente para este producto y sucursal.',
                ], 400);
            }

            $input = [
                'producto_id' => $request->producto_id,
                'sucursal_id' => $request->sucursal_id,
                'cantidad' => $request->cantidad,
                'estatus' => 1,
            ];

            $productoPendiente = $this->productosPendientesTraspaso->create($input);

            return response()->json([
                'status' => true,
                'message' => 'Producto registrado en lista de pendientes.',
                'data' => $productoPendiente
            ], 200);

        } catch (\Exception $e) {
            Log::error("ProductosController->registrarProductosTraspaso() | " . $e->getMessage() . " | " . $e->getLine());

            return response()->json([
                'status' => false,
                'message' => "[ERROR] ProductosController->registrarProductosTraspaso() | " . $e->getMessage() . " | " . $e->getLine(),
            ], 500);
        }
    }

    public function detallesProducto(Request $request)
    {
        try {

            $sucursalId = $request->sucursal_id;
            $productoId = $request->producto_id;
    
            $sucursal = Sucursales::find($sucursalId);
            if (!$sucursal) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sucursal no encontrada.'
                ], 404);
            }
    
            $inventarios = Inventario::with('producto')
                ->where('sucursal_id', $sucursalId)
                ->where('producto_id', $productoId)
                ->where('estatus', 1)
                ->get();
    
            if ($inventarios->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No hay existencias para el producto en esta sucursal.'
                ], 404);
            }
    
            $inventarioAgrupado = $inventarios->groupBy('fecha_caducidad')->map(function ($items, $fechaCaducidad) {
                return [
                    'fecha_caducidad' => $fechaCaducidad,
                    'total_existencias' => $items->sum('cantidad_disponible'),
                ];
            })->values();
    
            $producto = $inventarios->first()->producto;
    
            $respuesta = [
                'id' => $producto->id,
                'sku' => $producto->sku,
                'nombre' => $producto->nombre,
                'imagen' => $producto->imagen,
                'descripcion' => $producto->descripcion,
                'total_existencias_sucursal' => $inventarios->sum('cantidad_disponible'),
                'inventario' => $inventarioAgrupado,
            ];
    
            return response()->json([
                'status' => true,
                'results' => $respuesta,
                'message' => 'Inventario del producto en la sucursal obtenido con éxito.'
            ], 200);
        } catch (\Exception $e) {
            Log::info("ProductosController->detallesProducto() | " . $e->getMessage() . " | " . $e->getLine());
    
            return response()->json([
                'status' => false,
                'message' => "[ERROR] ProductosController->detallesProducto() | " . $e->getMessage() . " | " . $e->getLine(),
                'results' => null
            ], 500);
        }
    }

}
