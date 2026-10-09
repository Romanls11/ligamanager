<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MetodoPago;
use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EstadoCuentaController extends Controller
{
    public function show(Inscripcion $inscripcion): View
    {
        $inscripcion->load(['equipo', 'categoria.torneo']);

        $cargos = $inscripcion->cargos()
            ->with(['conceptoCobro', 'pagos'])
            ->orderByDesc('id')
            ->get();

        return view('admin.estado-cuenta.show', [
            'inscripcion' => $inscripcion,
            'cargos' => $cargos,
            'saldoTotal' => $cargos->sum(fn ($cargo) => $cargo->saldoPendiente()),
            'metodos' => MetodoPago::cases(),
        ]);
    }

    public function pdf(Inscripcion $inscripcion): Response
    {
        $inscripcion->load(['equipo', 'categoria.torneo']);

        $cargos = $inscripcion->cargos()->with(['conceptoCobro', 'pagos'])->orderByDesc('id')->get();

        $pdf = Pdf::loadView('pdf.estado-cuenta', [
            'inscripcion' => $inscripcion,
            'cargos' => $cargos,
            'saldoTotal' => $cargos->sum(fn ($cargo) => $cargo->saldoPendiente()),
        ]);

        return $pdf->stream('estado-cuenta-'.$inscripcion->equipo->nombre.'.pdf');
    }
}
