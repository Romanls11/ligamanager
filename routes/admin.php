<?php

use App\Http\Controllers\Admin\ArbitroController;
use App\Http\Controllers\Admin\CargoController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\CedulaController;
use App\Http\Controllers\Admin\ConceptoCobroController;
use App\Http\Controllers\Admin\EquipoController;
use App\Http\Controllers\Admin\EstadoCuentaController;
use App\Http\Controllers\Admin\InscripcionController;
use App\Http\Controllers\Admin\JugadorController;
use App\Http\Controllers\Admin\LiguillaController;
use App\Http\Controllers\Admin\PagoArbitroController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Admin\PartidoController;
use App\Http\Controllers\Admin\RolDeJuegosController;
use App\Http\Controllers\Admin\SolicitudAltaController;
use App\Http\Controllers\Admin\TorneoController;
use App\Http\Controllers\Admin\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('usuarios', UsuarioController::class)->except(['show']);
    Route::resource('torneos', TorneoController::class)->except(['show']);
    Route::resource('categorias', CategoriaController::class)->except(['show']);
    Route::resource('equipos', EquipoController::class)->except(['show']);
    Route::resource('jugadores', JugadorController::class)->except(['show'])
        ->parameters(['jugadores' => 'jugador']);

    Route::resource('inscripciones', InscripcionController::class)
        ->parameters(['inscripciones' => 'inscripcion']);
    Route::post('inscripciones/{inscripcion}/registros', [InscripcionController::class, 'agregarJugador'])
        ->name('inscripciones.registros.store');
    Route::patch('inscripciones/{inscripcion}/registros/{registro}', [InscripcionController::class, 'actualizarJugador'])
        ->name('inscripciones.registros.update');
    Route::delete('inscripciones/{inscripcion}/registros/{registro}', [InscripcionController::class, 'quitarJugador'])
        ->name('inscripciones.registros.destroy');

    Route::resource('arbitros', ArbitroController::class)->except(['show']);
    Route::resource('conceptos-cobro', ConceptoCobroController::class)->except(['show'])
        ->parameters(['conceptos-cobro' => 'concepto']);

    Route::get('categorias/{categoria}/rol-de-juegos', [RolDeJuegosController::class, 'show'])
        ->name('categorias.rol-de-juegos.show');
    Route::post('categorias/{categoria}/rol-de-juegos', [RolDeJuegosController::class, 'generar'])
        ->name('categorias.rol-de-juegos.generar');
    Route::delete('categorias/{categoria}/rol-de-juegos', [RolDeJuegosController::class, 'destroy'])
        ->name('categorias.rol-de-juegos.destroy');

    Route::get('categorias/{categoria}/liguilla', [LiguillaController::class, 'show'])
        ->name('categorias.liguilla.show');
    Route::post('categorias/{categoria}/liguilla', [LiguillaController::class, 'generar'])
        ->name('categorias.liguilla.generar');

    Route::get('partidos', [PartidoController::class, 'index'])->name('partidos.index');
    Route::get('partidos/{partido}/edit', [PartidoController::class, 'edit'])->name('partidos.edit');
    Route::put('partidos/{partido}', [PartidoController::class, 'update'])->name('partidos.update');
    Route::post('partidos/{partido}/arbitros', [PartidoController::class, 'asignarArbitro'])
        ->name('partidos.arbitros.store');
    Route::delete('partidos/{partido}/arbitros/{asignacion}', [PartidoController::class, 'quitarArbitro'])
        ->name('partidos.arbitros.destroy');

    Route::get('partidos/{partido}/cedula', [CedulaController::class, 'edit'])->name('partidos.cedula.edit');
    Route::post('partidos/{partido}/cedula/eventos', [CedulaController::class, 'store'])
        ->name('partidos.cedula.eventos.store');
    Route::delete('partidos/{partido}/cedula/eventos/{evento}', [CedulaController::class, 'destroyEvento'])
        ->name('partidos.cedula.eventos.destroy');
    Route::post('partidos/{partido}/cedula/finalizar', [CedulaController::class, 'finalizar'])
        ->name('partidos.cedula.finalizar');
    Route::post('partidos/{partido}/cedula/default', [CedulaController::class, 'marcarDefault'])
        ->name('partidos.cedula.default');

    Route::get('cargos', [CargoController::class, 'index'])->name('cargos.index');
    Route::get('cargos/create', [CargoController::class, 'create'])->name('cargos.create');
    Route::post('cargos', [CargoController::class, 'store'])->name('cargos.store');
    Route::post('cargos/{cargo}/condonar', [CargoController::class, 'condonar'])->name('cargos.condonar');
    Route::delete('cargos/{cargo}', [CargoController::class, 'destroy'])->name('cargos.destroy');

    Route::post('cargos/{cargo}/pagos', [PagoController::class, 'store'])->name('cargos.pagos.store');
    Route::get('pagos/{pago}/recibo', [PagoController::class, 'recibo'])->name('pagos.recibo');

    Route::get('inscripciones/{inscripcion}/estado-cuenta', [EstadoCuentaController::class, 'show'])
        ->name('inscripciones.estado-cuenta.show');
    Route::get('inscripciones/{inscripcion}/estado-cuenta/pdf', [EstadoCuentaController::class, 'pdf'])
        ->name('inscripciones.estado-cuenta.pdf');

    Route::get('pagos-arbitro', [PagoArbitroController::class, 'index'])->name('pagos-arbitro.index');
    Route::post('asignaciones/{asignacion}/pagos', [PagoArbitroController::class, 'store'])
        ->name('asignaciones.pagos.store');

    Route::get('solicitudes-alta', [SolicitudAltaController::class, 'index'])->name('solicitudes-alta.index');
    Route::post('solicitudes-alta/{solicitud}/aprobar', [SolicitudAltaController::class, 'aprobar'])
        ->name('solicitudes-alta.aprobar');
    Route::post('solicitudes-alta/{solicitud}/rechazar', [SolicitudAltaController::class, 'rechazar'])
        ->name('solicitudes-alta.rechazar');
});
