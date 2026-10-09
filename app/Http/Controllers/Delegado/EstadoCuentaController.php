<?php

namespace App\Http\Controllers\Delegado;

use App\Http\Controllers\Concerns\AutorizaEquipoDelegado;
use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use Illuminate\View\View;

class EstadoCuentaController extends Controller
{
    use AutorizaEquipoDelegado;

    public function show(Inscripcion $inscripcion): View
    {
        $this->autorizarInscripcion($inscripcion);

        $inscripcion->load(['equipo', 'categoria.torneo']);

        $cargos = $inscripcion->cargos()
            ->with(['conceptoCobro', 'pagos'])
            ->orderByDesc('id')
            ->get();

        return view('delegado.estado-cuenta.show', [
            'inscripcion' => $inscripcion,
            'cargos' => $cargos,
            'saldoTotal' => $cargos->sum(fn ($cargo) => $cargo->saldoPendiente()),
        ]);
    }
}
