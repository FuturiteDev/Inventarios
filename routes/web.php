<?php
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/categorias', [Ongoing\Inventarios\Http\Controllers\CategoriasController::class, 'index'])->name('categorias.list');
    Route::get('/subcategorias', [Ongoing\Inventarios\Http\Controllers\SubCategoryController::class, 'index'])->name('subcategorias.list');
    Route::get('/productos', [Ongoing\Inventarios\Http\Controllers\ProductosController::class, 'index'])->name('productos.list');
    Route::get('/colecciones', [Ongoing\Inventarios\Http\Controllers\ColeccionesController::class, 'index'])->name('colecciones.list');
    Route::view('/producto-terminado', 'inventarios::producto_terminado');
    Route::view('/existencias-sucursal', 'inventarios::existencias');
    Route::view('/traspasos', 'inventarios::traspasos');
});