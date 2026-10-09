<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Arbitro;
use App\Models\AsignacionArbitro;
use App\Services\PagoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

class PagoArbitroController extends Controller
{
    public function index(Request $request): View
    {
        $asignaciones = AsignacionArbitro::query()
            ->with(['arbitro', 'partido.fase.categoria', 'partido.local.equipo', 'partido.visita.equipo', 'pagos'])
            ->when($request->filled('arbitro_id'), fn ($query) => $query->where('arbitro_id', $request->integer('arbitro_id')))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pagos-arbitro.index', [
            'asignaciones' => $asignaciones,
            'arbitros' => Arbitro::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request, AsignacionArbitro $asignacion): RedirectResponse
    {
        $datos = $request->validate([
            'monto' => ['required', 'numeric', 'min:0.01'],
            'fecha_pago' => ['nullable', 'date'],
        ]);

        try {
            app(PagoService::class)->registrarPagoArbitro(
                $asignacion,
                (float) $datos['monto'],
                isset($datos['fecha_pago']) ? Carbon::parse($datos['fecha_pago']) : null,
                Auth::user(),
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['monto' => $e->getMessage()]);
        }

        return back()->with('status', 'Pago a árbitro registrado.');
    }
}
