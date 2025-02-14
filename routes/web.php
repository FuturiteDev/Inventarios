<?php
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/categorias', [Ongoing\Inventarios\Http\Controllers\CategoriasController::class, 'index'])->name('categorias.list');
    Route::get('/subcategorias', [Ongoing\Inventarios\Http\Controllers\SubCategoryController::class, 'index'])->name('subcategorias.list');
    Route::get('/productos', [Ongoing\Inventarios\Http\Controllers\ProductosController::class, 'index'])->name('productos.list');
    Route::get('/colecciones', [Ongoing\Inventarios\Http\Controllers\ColeccionesController::class, 'index'])->name('colecciones.list');
    Route::get('/producto-terminado', [Ongoing\Inventarios\Http\Controllers\InventarioController::class, 'index']);
    Route::get('/existencias-sucursal', [Ongoing\Inventarios\Http\Controllers\InventarioController::class, 'existenciasSucursal']);
    Route::get('/traspasos', [Ongoing\Inventarios\Http\Controllers\TraspasosController::class, 'index']);
});