<?php

use App\Http\Controllers\Delegado\EstadoCuentaController;
use App\Http\Controllers\Delegado\JugadorController;
use App\Http\Controllers\Delegado\RolDeJuegosController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:delegado'])->prefix('delegado')->name('delegado.')->group(function () {
    Route::get('equipos/{inscripcion}/estado-cuenta', [EstadoCuentaController::class, 'show'])
        ->name('inscripciones.estado-cuenta.show');
    Route::get('equipos/{inscripcion}/rol-de-juegos', [RolDeJuegosController::class, 'show'])
        ->name('inscripciones.rol-de-juegos.show');
    Route::get('equipos/{inscripcion}/jugadores', [JugadorController::class, 'index'])
        ->name('inscripciones.jugadores.index');
    Route::post('equipos/{inscripcion}/jugadores', [JugadorController::class, 'store'])
        ->name('inscripciones.jugadores.store');
    Route::post('equipos/{inscripcion}/jugadores/nuevo', [JugadorController::class, 'storeNuevo'])
        ->name('inscripciones.jugadores.store-nuevo');
});
