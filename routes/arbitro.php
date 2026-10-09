<?php

use App\Http\Controllers\Arbitro\CedulaController;
use App\Http\Controllers\Arbitro\PartidoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:arbitro'])->prefix('arbitro')->name('arbitro.')->group(function () {
    Route::get('partidos', [PartidoController::class, 'index'])->name('partidos.index');
    Route::get('partidos/{partido}/cedula', [CedulaController::class, 'edit'])->name('partidos.cedula.edit');
    Route::post('partidos/{partido}/cedula/eventos', [CedulaController::class, 'store'])
        ->name('partidos.cedula.eventos.store');
    Route::delete('partidos/{partido}/cedula/eventos/{evento}', [CedulaController::class, 'destroyEvento'])
        ->name('partidos.cedula.eventos.destroy');
    Route::post('partidos/{partido}/cedula/finalizar', [CedulaController::class, 'finalizar'])
        ->name('partidos.cedula.finalizar');
    Route::post('partidos/{partido}/cedula/default', [CedulaController::class, 'marcarDefault'])
        ->name('partidos.cedula.default');
});
