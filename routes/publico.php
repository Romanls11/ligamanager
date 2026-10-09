<?php

use App\Http\Controllers\Publico\CategoriaController;
use App\Http\Controllers\Publico\HomeController;
use Illuminate\Support\Facades\Route;

Route::name('publico.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('categorias/{categoria}/tabla', [CategoriaController::class, 'tabla'])->name('categorias.tabla');
    Route::get('categorias/{categoria}/calendario', [CategoriaController::class, 'calendario'])->name('categorias.calendario');
    Route::get('categorias/{categoria}/goleo', [CategoriaController::class, 'goleo'])->name('categorias.goleo');
    Route::get('categorias/{categoria}/liguilla', [CategoriaController::class, 'liguilla'])->name('categorias.liguilla');
});
