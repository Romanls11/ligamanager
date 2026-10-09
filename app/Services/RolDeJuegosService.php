<?php

namespace App\Services;

use App\Enums\EstadoFase;
use App\Enums\EstadoInscripcion;
use App\Enums\EstadoPartido;
use App\Enums\TipoFase;
use App\Models\Categoria;
use App\Models\Fase;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RolDeJuegosService
{
    /**
     * Genera (o regenera) el rol de juegos de temporada regular para una categoría,
     * usando el método del círculo para el round-robin.
     */
    public function generar(Categoria $categoria): Fase
    {
        $inscripcionIds = $categoria->inscripciones()
            ->where('estado', EstadoInscripcion::Activa)
            ->pluck('id')
            ->all();

        if (count($inscripcionIds) < 2) {
            throw new RuntimeException('La categoría necesita al menos 2 equipos con inscripción activa para generar el rol de juegos.');
        }

        return DB::transaction(function () use ($categoria, $inscripcionIds) {
            $faseExistente = $categoria->fases()->where('tipo', TipoFase::Jornadas)->first();

            if ($faseExistente) {
                $tienePartidosJugados = $faseExistente->partidos()
                    ->where('estado', '!=', EstadoPartido::Programado)
                    ->exists();

                if ($tienePartidosJugados) {
                    throw new RuntimeException('No se puede regenerar el rol de juegos: ya hay partidos con resultado capturado. Elimínalos manualmente antes de regenerar.');
                }

                $faseExistente->delete();
            }

            $fase = Fase::create([
                'categoria_id' => $categoria->id,
                'tipo' => TipoFase::Jornadas,
                'orden' => 1,
                'estado' => EstadoFase::EnCurso,
            ]);

            $jornadasVueltaUno = $this->generarRoundRobin($inscripcionIds);

            $jornada = 1;

            foreach ($jornadasVueltaUno as $partidos) {
                foreach ($partidos as [$local, $visita]) {
                    $fase->partidos()->create([
                        'jornada' => $jornada,
                        'local_inscripcion_id' => $local,
                        'visita_inscripcion_id' => $visita,
                        'estado' => EstadoPartido::Programado,
                        'es_vuelta' => false,
                    ]);
                }
                $jornada++;
            }

            if ((int) $categoria->vueltas === 2) {
                foreach ($jornadasVueltaUno as $partidos) {
                    foreach ($partidos as [$local, $visita]) {
                        $fase->partidos()->create([
                            'jornada' => $jornada,
                            'local_inscripcion_id' => $visita,
                            'visita_inscripcion_id' => $local,
                            'estado' => EstadoPartido::Programado,
                            'es_vuelta' => true,
                        ]);
                    }
                    $jornada++;
                }
            }

            return $fase->fresh('partidos');
        });
    }

    /**
     * Genera el calendario round-robin (método del círculo) para una vuelta.
     * Si el número de equipos es non, se agrega un "descanso" (null) por jornada.
     *
     * @param  array<int, int>  $inscripcionIds
     * @return array<int, array<int, array{0: int, 1: int}>>
     */
    private function generarRoundRobin(array $inscripcionIds): array
    {
        $equipos = $inscripcionIds;

        if (count($equipos) % 2 !== 0) {
            $equipos[] = null; // descanso
        }

        $numEquipos = count($equipos);
        $numJornadas = $numEquipos - 1;
        $mitad = intdiv($numEquipos, 2);

        $jornadas = [];

        for ($j = 0; $j < $numJornadas; $j++) {
            $partidos = [];

            for ($i = 0; $i < $mitad; $i++) {
                $local = $equipos[$i];
                $visita = $equipos[$numEquipos - 1 - $i];

                if ($local !== null && $visita !== null) {
                    // Alterna localía por jornada para repartir partidos en casa/visita.
                    $partidos[] = $j % 2 === 0 ? [$local, $visita] : [$visita, $local];
                }
            }

            $jornadas[] = $partidos;

            // Rotar todos menos el primer equipo (posición 0 fija).
            $ultimo = array_pop($equipos);
            array_splice($equipos, 1, 0, [$ultimo]);
        }

        return $jornadas;
    }
}
