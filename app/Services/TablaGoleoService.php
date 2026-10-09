<?php

namespace App\Services;

use App\Enums\TipoEventoPartido;
use App\Models\Categoria;
use App\Models\EventoPartido;
use Illuminate\Support\Collection;

class TablaGoleoService
{
    /**
     * Tabla de goleo de una categoría (temporada regular + liguilla), ordenada
     * de mayor a menor número de goles. Los autogoles no suman al anotador.
     *
     * @return Collection<int, array{registro: \App\Models\RegistroJugador, goles: int}>
     */
    public function calcular(Categoria $categoria, int $limite = 20): Collection
    {
        return EventoPartido::query()
            ->where('tipo', TipoEventoPartido::Gol)
            ->whereHas('partido.fase', fn ($query) => $query->where('categoria_id', $categoria->id))
            ->with('registroJugador.jugador', 'registroJugador.inscripcion.equipo')
            ->get()
            ->groupBy('registro_jugador_id')
            ->map(fn ($eventos) => [
                'registro' => $eventos->first()->registroJugador,
                'goles' => $eventos->count(),
            ])
            ->sortByDesc('goles')
            ->take($limite)
            ->values();
    }
}
