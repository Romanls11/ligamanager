<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolicitudAlta;
use App\Services\SolicitudAltaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

class SolicitudAltaController extends Controller
{
    public function __construct(private readonly SolicitudAltaService $service) {}

    public function index(): View
    {
        $solicitudes = SolicitudAlta::query()
            ->with(['inscripcion.equipo', 'inscripcion.categoria', 'jugador', 'solicitadoPor'])
            ->orderByRaw("(estado = 'pendiente') desc")
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.solicitudes-alta.index', compact('solicitudes'));
    }

    public function aprobar(SolicitudAlta $solicitud): RedirectResponse
    {
        try {
            $this->service->aprobar($solicitud, Auth::user());
        } catch (RuntimeException $e) {
            return back()->withErrors(['solicitud' => $e->getMessage()]);
        }

        return back()->with('status', 'Solicitud aprobada: jugador agregado a la plantilla.');
    }

    public function rechazar(Request $request, SolicitudAlta $solicitud): RedirectResponse
    {
        $datos = $request->validate([
            'motivo_rechazo' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->service->rechazar($solicitud, Auth::user(), $datos['motivo_rechazo'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withErrors(['solicitud' => $e->getMessage()]);
        }

        return back()->with('status', 'Solicitud rechazada.');
    }
}
