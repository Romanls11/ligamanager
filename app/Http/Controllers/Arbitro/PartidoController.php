<?php

namespace App\Http\Controllers\Arbitro;

use App\Http\Controllers\Controller;
use App\Models\Partido;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PartidoController extends Controller
{
    public function index(): View
    {
        $arbitro = Auth::user()->arbitro;

        $partidos = Partido::query()
            ->when($arbitro, fn ($query) => $query->whereHas(
                'asignacionesArbitro', fn ($asignacion) => $asignacion->where('arbitro_id', $arbitro->id)
            ), fn ($query) => $query->whereRaw('1 = 0'))
            ->with(['fase.categoria', 'local.equipo', 'visita.equipo'])
            ->orderByDesc('fecha_hora')
            ->paginate(15);

        return view('arbitro.partidos.index', compact('partidos'));
    }
}
