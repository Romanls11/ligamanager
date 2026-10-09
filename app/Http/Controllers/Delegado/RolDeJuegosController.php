<?php

namespace App\Http\Controllers\Delegado;

use App\Enums\TipoFase;
use App\Http\Controllers\Concerns\AutorizaEquipoDelegado;
use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use Illuminate\View\View;

class RolDeJuegosController extends Controller
{
    use AutorizaEquipoDelegado;

    public function show(Inscripcion $inscripcion): View
    {
        $this->autorizarInscripcion($inscripcion);

        $inscripcion->load(['equipo', 'categoria.torneo']);

        $fase = $inscripcion->categoria->fases()
            ->where('tipo', TipoFase::Jornadas)
            ->with(['partidos' => function ($query) {
                $query->with(['local.equipo', 'visita.equipo'])->orderBy('jornada')->orderBy('id');
            }])
            ->first();

        $partidosPorJornada = $fase ? $fase->partidos->groupBy('jornada') : collect();

        return view('delegado.rol-de-juegos.show', [
            'inscripcion' => $inscripcion,
            'partidosPorJornada' => $partidosPorJornada,
        ]);
    }
}
