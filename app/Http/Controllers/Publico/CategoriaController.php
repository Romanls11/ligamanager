<?php

namespace App\Http\Controllers\Publico;

use App\Enums\TipoFase;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Services\TablaGeneralService;
use App\Services\TablaGoleoService;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function tabla(Categoria $categoria, TablaGeneralService $service): View
    {
        $categoria->load('torneo');

        return view('publico.categorias.tabla', [
            'categoria' => $categoria,
            'tabla' => $service->calcular($categoria),
        ]);
    }

    public function calendario(Categoria $categoria): View
    {
        $categoria->load('torneo');

        $fase = $categoria->fases()
            ->where('tipo', TipoFase::Jornadas)
            ->with(['partidos' => function ($query) {
                $query->with(['local.equipo', 'visita.equipo'])->orderBy('jornada')->orderBy('id');
            }])
            ->first();

        $partidosPorJornada = $fase ? $fase->partidos->groupBy('jornada') : collect();

        return view('publico.categorias.calendario', compact('categoria', 'partidosPorJornada'));
    }

    public function goleo(Categoria $categoria, TablaGoleoService $service): View
    {
        $categoria->load('torneo');

        return view('publico.categorias.goleo', [
            'categoria' => $categoria,
            'goleadores' => $service->calcular($categoria),
        ]);
    }

    public function liguilla(Categoria $categoria): View
    {
        $categoria->load('torneo');

        $tiposLiguilla = [TipoFase::Octavos, TipoFase::Cuartos, TipoFase::Semifinal, TipoFase::Final];

        $rondas = $categoria->fases()
            ->whereIn('tipo', $tiposLiguilla)
            ->with(['partidos' => function ($query) {
                $query->with(['local.equipo', 'visita.equipo'])->orderBy('llave')->orderBy('es_vuelta');
            }])
            ->orderBy('orden')
            ->get();

        return view('publico.categorias.liguilla', compact('categoria', 'rondas'));
    }
}
