<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TipoFase;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Services\RolDeJuegosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class RolDeJuegosController extends Controller
{
    public function __construct(private readonly RolDeJuegosService $service) {}

    public function show(Categoria $categoria): View
    {
        $categoria->load('torneo');

        $fase = $categoria->fases()
            ->where('tipo', TipoFase::Jornadas)
            ->with(['partidos' => function ($query) {
                $query->with(['local.equipo', 'visita.equipo', 'asignacionesArbitro.arbitro'])
                    ->orderBy('jornada')
                    ->orderBy('id');
            }])
            ->first();

        $partidosPorJornada = $fase
            ? $fase->partidos->groupBy('jornada')
            : collect();

        $totalInscripcionesActivas = $categoria->inscripciones()->where('estado', 'activa')->count();

        return view('admin.rol-de-juegos.show', [
            'categoria' => $categoria,
            'fase' => $fase,
            'partidosPorJornada' => $partidosPorJornada,
            'totalInscripcionesActivas' => $totalInscripcionesActivas,
        ]);
    }

    public function generar(Categoria $categoria): RedirectResponse
    {
        try {
            $this->service->generar($categoria);
        } catch (RuntimeException $e) {
            return back()->withErrors(['rol_de_juegos' => $e->getMessage()]);
        }

        return redirect()->route('admin.categorias.rol-de-juegos.show', $categoria)
            ->with('status', 'Rol de juegos generado correctamente.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        $fase = $categoria->fases()->where('tipo', TipoFase::Jornadas)->first();

        if ($fase) {
            $tienePartidosJugados = $fase->partidos()->where('estado', '!=', 'programado')->exists();

            if ($tienePartidosJugados) {
                return back()->withErrors(['rol_de_juegos' => 'No se puede eliminar: ya hay partidos con resultado capturado.']);
            }

            $fase->delete();
        }

        return redirect()->route('admin.categorias.rol-de-juegos.show', $categoria)
            ->with('status', 'Rol de juegos eliminado.');
    }
}
