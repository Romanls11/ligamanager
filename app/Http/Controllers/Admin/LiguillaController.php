<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TipoFase;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Services\LiguillaService;
use App\Services\TablaGeneralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class LiguillaController extends Controller
{
    public function __construct(
        private readonly LiguillaService $liguilla,
        private readonly TablaGeneralService $tablaGeneral,
    ) {}

    public function show(Categoria $categoria): View
    {
        $categoria->load('torneo');

        $tabla = $this->tablaGeneral->calcular($categoria);

        $tiposLiguilla = [TipoFase::Octavos, TipoFase::Cuartos, TipoFase::Semifinal, TipoFase::Final];

        $rondas = $categoria->fases()
            ->whereIn('tipo', $tiposLiguilla)
            ->with(['partidos' => function ($query) {
                $query->with(['local.equipo', 'visita.equipo'])->orderBy('llave')->orderBy('es_vuelta');
            }])
            ->orderBy('orden')
            ->get();

        return view('admin.liguilla.show', [
            'categoria' => $categoria,
            'tabla' => $tabla,
            'rondas' => $rondas,
            'clasificados' => $tabla->take($categoria->equipos_a_liguilla)->pluck('inscripcion.id')->all(),
        ]);
    }

    public function generar(Categoria $categoria): RedirectResponse
    {
        try {
            $this->liguilla->generar($categoria);
        } catch (RuntimeException $e) {
            return back()->withErrors(['liguilla' => $e->getMessage()]);
        }

        return redirect()->route('admin.categorias.liguilla.show', $categoria)
            ->with('status', 'Liguilla generada correctamente.');
    }
}
