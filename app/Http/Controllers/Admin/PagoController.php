<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MetodoPago;
use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\Pago;
use App\Services\PagoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use RuntimeException;

class PagoController extends Controller
{
    public function store(Request $request, Cargo $cargo): RedirectResponse
    {
        $datos = $request->validate([
            'monto' => ['required', 'numeric', 'min:0.01'],
            'metodo' => ['required', Rule::enum(MetodoPago::class)],
            'fecha' => ['nullable', 'date'],
        ]);

        try {
            $pago = app(PagoService::class)->registrarPago(
                $cargo,
                (float) $datos['monto'],
                MetodoPago::from($datos['metodo']),
                isset($datos['fecha']) ? Carbon::parse($datos['fecha']) : null,
                Auth::user(),
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['monto' => $e->getMessage()]);
        }

        return back()->with('status', 'Pago registrado. Folio: '.$pago->folio_recibo);
    }

    public function recibo(Pago $pago): Response
    {
        $pago->load(['cargo.inscripcion.equipo', 'cargo.inscripcion.categoria.torneo', 'cargo.conceptoCobro', 'registradoPor']);

        $pdf = Pdf::loadView('pdf.recibo', compact('pago'));

        return $pdf->stream('recibo-'.$pago->folio_recibo.'.pdf');
    }
}
