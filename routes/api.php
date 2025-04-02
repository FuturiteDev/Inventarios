<?php

use Illuminate\Support\Facades\Route;
use Ongoing\Inventarios\Http\Controllers\CategoriasController;
use Ongoing\Inventarios\Http\Controllers\SubCategoryController;
use Ongoing\Inventarios\Http\Controllers\ColeccionesController;
use Ongoing\Inventarios\Http\Controllers\ProductosController;
use Ongoing\Inventarios\Http\Controllers\InventarioController;
use Ongoing\Inventarios\Http\Controllers\TraspasosController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix('sub-categorias')->group(function () {
    Route::get('/one/{id}', [SubCategoryController::class, 'getOne'])->name('get_subcategory_by_id');
    Route::get('/categoria/{id}', [SubCategoryController::class, 'getByCategory'])->name('get_subcategories_by_category');
    Route::post('/save', [SubCategoryController::class, 'save'])->name('save_subcategories');
    Route::get('/{subcategory_id}/productos', [ProductosController::class, 'getProductsSubcategory'])->name('get_products_subcategory');
});

Route::prefix('categorias')->group(function () {
    Route::get('/all', [CategoriasController::class, 'getCategorias'])->name('get-categorias-all');
    Route::post('/save', [CategoriasController::class, 'saveCategorias'])->name('save-categorias');
});

Route::prefix('colecciones')->group(function () {
    Route::get('/all', [ColeccionesController::class, 'getAll'])->name('get_all_colections');
    Route::get('/productos/{coleccion_id}', [ColeccionesController::class, 'getProductosColeccion'])->name('get_productos_coleccion');
    Route::post('/save', [ColeccionesController::class, 'saveColecciones'])->name('save-colecciones');
    Route::post('/delete', [ColeccionesController::class, 'delete'])->name('delete_colection');
    Route::get('/publicadas', [ColeccionesController::class, 'getPublicadas'])->name('get_published_colections');
});


Route::prefix('productos')->group(function () {
    Route::get('/all', [ProductosController::class, 'getAll'])->name('get_all_products');
    Route::post('/save', [ProductosController::class, 'save'])->name('save_product');
    Route::post('/delete', [ProductosController::class, 'delete'])->name('delete_product');
    Route::get('/{productoId}/multimedia/all', [ProductosController::class, 'getAllMultimedia'])->name('get_all_product_multimedia');
    Route::post('/multimedia/save', [ProductosController::class, 'saveMultimedia'])->name('save_product_multimedia');
    Route::post('/multimedia/delete', [ProductosController::class, 'deleteMultimedia'])->name('delete_product_multimedia');

    Route::post('/buscar', [ProductosController::class, 'searchProducts'])->name('search_products_sku_name');
    Route::post('/registrar-traspaso', [ProductosController::class, 'registrarProductosTraspaso'])->name('registrar_productos_traspaso');
    Route::post('/detalles', [ProductosController::class, 'detallesProducto'])->name('detalles_de_producto');
    Route::post('/establecer-portada', [ProductosController::class, 'establecerPortada']);
});

Route::prefix('inventarios')->group(function () {
    Route::post('/existencia-sucursal', [InventarioController::class, 'getProductosConExistencias']);
    Route::post('/agregar-inventario', [InventarioController::class, 'agregarInventarios']);
    Route::post('/eliminar-inventario', [InventarioController::class, 'eliminarInventarios']);
    Route::post('/registrar-inventario', [InventarioController::class, 'registrarInventario']);
    Route::get('/existencia-general', [InventarioController::class, 'productosExistenciaGeneral']);
    Route::post('/poca-existencia', [InventarioController::class, 'getProductosConPocaExistencia']);

    Route::get('/revision/sucursal/{sucursal_id}', [InventarioController::class, 'revisionSucursal']);
    Route::post('/revision-guardar', [InventarioController::class, 'inventarioRevisionGuardar']);
    Route::post('/revision-finalizar',  [InventarioController::class, 'inventarioRevisionFinalizar']);
    Route::get('/revisiones',  [InventarioController::class, 'getRevisiones']);
    Route::get('/revisiones-detalles/{revision_id}',  [InventarioController::class, 'getRevisionesDetalles']);

});

Route::prefix('traspasos')->group(function () {
    Route::get('/list', [TraspasosController::class, 'listTraspasos']);
    Route::post('/save', [TraspasosController::class, 'saveTraspaso']);
    Route::get('/get/{traspaso_id}', [TraspasosController::class, 'getTraspaso']);
    Route::post('/recibir', [TraspasosController::class, 'recibirTraspaso']);
    Route::get('/sucursal/{sucursal_id}', [TraspasosController::class, 'getTraspasoSucursal']);
    Route::get('/sucursal/pendientes/{sucursal_id}', [TraspasosController::class, 'productosPendientesTraspaso']);
    Route::post('/pendientes/registrar', [TraspasosController::class, 'registrarPendientesTraspaso']);
    Route::post('/chofer-asignado', [TraspasosController::class, 'listTraspasosPorChofer']);
    Route::post('/cancelar/{traspaso_id}', [TraspasosController::class, 'cancelarTraspaso']);
});
