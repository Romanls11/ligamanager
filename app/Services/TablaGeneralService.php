<?php

namespace App\Services;

use App\Enums\EstadoPartido;
use App\Enums\TipoFase;
use App\Models\Categoria;
use App\Models\Partido;
use Illuminate\Support\Collection;

class TablaGeneralService
{
    private const ESTADOS_CONCLUIDOS = [
        EstadoPartido::Jugado->value,
        EstadoPartido::DefaultLocal->value,
        EstadoPartido::DefaultVisita->value,
    ];

    /**
     * Calcula la tabla general de temporada regular (fase "jornadas") de una categoría,
     * ordenada por puntos, diferencia de goles, goles a favor y, si dos equipos siguen
     * empatados en todo lo anterior, el resultado de su enfrentamiento directo.
     *
     * @return Collection<int, array{inscripcion: \App\Models\Inscripcion, pj: int, pg: int, pe: int, pp: int, gf: int, gc: int, dg: int, pts: int}>
     */
    public function calcular(Categoria $categoria): Collection
    {
        $inscripciones = $categoria->inscripciones()
            ->with('equipo')
            ->where('estado', '!=', 'retirada')
            ->get();

        $tabla = $inscripciones->mapWithKeys(fn ($inscripcion) => [
            $inscripcion->id => [
                'inscripcion' => $inscripcion,
                'pj' => 0, 'pg' => 0, 'pe' => 0, 'pp' => 0,
                'gf' => 0, 'gc' => 0, 'dg' => 0, 'pts' => 0,
            ],
        ]);

        $partidos = Partido::query()
            ->whereHas('fase', fn ($query) => $query->where('categoria_id', $categoria->id)->where('tipo', TipoFase::Jornadas))
            ->whereIn('estado', self::ESTADOS_CONCLUIDOS)
            ->get();

        foreach ($partidos as $partido) {
            $this->acumular($tabla, $partido->local_inscripcion_id, $partido->goles_local, $partido->goles_visita);
            $this->acumular($tabla, $partido->visita_inscripcion_id, $partido->goles_visita, $partido->goles_local);
        }

        return $tabla->values()
            ->sort(fn ($a, $b) => $this->comparar($a, $b, $partidos))
            ->values();
    }

    private function acumular(Collection $tabla, int $inscripcionId, ?int $golesPropios, ?int $golesRival): void
    {
        if (! $tabla->has($inscripcionId) || $golesPropios === null || $golesRival === null) {
            return;
        }

        $fila = $tabla->get($inscripcionId);
        $fila['pj']++;
        $fila['gf'] += $golesPropios;
        $fila['gc'] += $golesRival;
        $fila['dg'] = $fila['gf'] - $fila['gc'];

        if ($golesPropios > $golesRival) {
            $fila['pg']++;
            $fila['pts'] += 3;
        } elseif ($golesPropios === $golesRival) {
            $fila['pe']++;
            $fila['pts'] += 1;
        } else {
            $fila['pp']++;
        }

        $tabla->put($inscripcionId, $fila);
    }

    private function comparar(array $a, array $b, Collection $partidos): int
    {
        if ($a['pts'] !== $b['pts']) {
            return $b['pts'] <=> $a['pts'];
        }

        if ($a['dg'] !== $b['dg']) {
            return $b['dg'] <=> $a['dg'];
        }

        if ($a['gf'] !== $b['gf']) {
            return $b['gf'] <=> $a['gf'];
        }

        $directo = $this->resultadoDirecto($a['inscripcion']->id, $b['inscripcion']->id, $partidos);

        if ($directo !== 0) {
            return $directo;
        }

        return $a['inscripcion']->equipo->nombre <=> $b['inscripcion']->equipo->nombre;
    }

    /**
     * Diferencia de goles del enfrentamiento directo entre dos equipos (ida y vuelta si aplica).
     * Desempate simplificado pensado para grupos de exactamente dos equipos empatados.
     */
    private function resultadoDirecto(int $inscripcionAId, int $inscripcionBId, Collection $partidos): int
    {
        $golesA = 0;
        $golesB = 0;

        foreach ($partidos as $partido) {
            if ($partido->local_inscripcion_id === $inscripcionAId && $partido->visita_inscripcion_id === $inscripcionBId) {
                $golesA += $partido->goles_local ?? 0;
                $golesB += $partido->goles_visita ?? 0;
            } elseif ($partido->local_inscripcion_id === $inscripcionBId && $partido->visita_inscripcion_id === $inscripcionAId) {
                $golesB += $partido->goles_local ?? 0;
                $golesA += $partido->goles_visita ?? 0;
            }
        }

        return $golesB <=> $golesA;
    }
}
