<?php

namespace App\Services;

use App\Enums\EstadoFase;
use App\Enums\EstadoPartido;
use App\Enums\TipoFase;
use App\Models\Categoria;
use App\Models\Fase;
use App\Models\Inscripcion;
use App\Models\Partido;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LiguillaService
{
    private const ESTADOS_CONCLUIDOS = [
        EstadoPartido::Jugado->value,
        EstadoPartido::DefaultLocal->value,
        EstadoPartido::DefaultVisita->value,
    ];

    // Nombre de la ronda según cuántos equipos arrancan la liguilla: con 8 equipos,
    // la primera ronda (4 llaves) son "cuartos de final", no "octavos" (eso sería con 16).
    private const TIPO_POR_NUMERO_EQUIPOS = [
        16 => TipoFase::Octavos,
        8 => TipoFase::Cuartos,
        4 => TipoFase::Semifinal,
        2 => TipoFase::Final,
    ];

    private const SIGUIENTE_TIPO = [
        'octavos' => TipoFase::Cuartos,
        'cuartos' => TipoFase::Semifinal,
        'semifinal' => TipoFase::Final,
    ];

    public function __construct(private readonly TablaGeneralService $tablaGeneral) {}

    /**
     * Genera la primera ronda de liguilla a partir de la tabla general, con el
     * sembrado clásico de bracket (1 vs 8, 4 vs 5, 2 vs 7, 3 vs 6, ...) a ida y vuelta.
     */
    public function generar(Categoria $categoria): Fase
    {
        $numEquipos = $categoria->equipos_a_liguilla;
        $tipo = self::TIPO_POR_NUMERO_EQUIPOS[$numEquipos] ?? null;

        if (! $tipo) {
            throw new RuntimeException('El número de equipos a liguilla de esta categoría debe ser 2, 4, 8 o 16.');
        }

        $tiposLiguilla = [
            TipoFase::Octavos->value, TipoFase::Cuartos->value, TipoFase::Semifinal->value, TipoFase::Final->value,
        ];

        if ($categoria->fases()->whereIn('tipo', $tiposLiguilla)->exists()) {
            throw new RuntimeException('Esta categoría ya tiene una liguilla generada.');
        }

        $faseJornadas = $categoria->fases()->where('tipo', TipoFase::Jornadas)->first();

        if (! $faseJornadas || $faseJornadas->partidos()->where('estado', EstadoPartido::Programado)->exists()) {
            throw new RuntimeException('No se puede generar la liguilla: aún hay partidos de temporada regular sin concluir.');
        }

        $tabla = $this->tablaGeneral->calcular($categoria);

        if ($tabla->count() < $numEquipos) {
            throw new RuntimeException("No hay suficientes equipos en la tabla general (se necesitan {$numEquipos}).");
        }

        $clasificados = $tabla->take($numEquipos)->values()->map(fn ($fila) => $fila['inscripcion']);

        return DB::transaction(function () use ($categoria, $tipo, $clasificados, $numEquipos) {
            $fase = Fase::create([
                'categoria_id' => $categoria->id,
                'tipo' => $tipo,
                'orden' => ($categoria->fases()->max('orden') ?? 0) + 1,
                'estado' => EstadoFase::EnCurso,
            ]);

            foreach ($this->ordenSembrado($numEquipos) as $llave => [$semillaA, $semillaB]) {
                $this->crearLlave($fase, $llave + 1, $clasificados[$semillaA - 1], $clasificados[$semillaB - 1]);
            }

            return $fase->fresh('partidos');
        });
    }

    /**
     * Revisa si la ronda a la que pertenece el partido ya se completó por
     * entero y, de ser así, determina ganadores y genera la siguiente ronda
     * (o marca la final como concluida).
     */
    public function avanzarSiRondaCompleta(Partido $partido): void
    {
        $fase = $partido->fase;

        if ($fase->tipo === TipoFase::Jornadas) {
            return;
        }

        $partidos = $fase->partidos()->get();

        if ($partidos->contains(fn ($p) => ! in_array($p->estado->value, self::ESTADOS_CONCLUIDOS, true))) {
            return;
        }

        $fase->update(['estado' => EstadoFase::Finalizada]);

        $siguienteTipo = self::SIGUIENTE_TIPO[$fase->tipo->value] ?? null;

        if (! $siguienteTipo) {
            // Era la final: no hay más rondas que generar.
            return;
        }

        $ganadoresPorLlave = $partidos->groupBy('llave')
            ->sortKeys()
            ->map(fn ($partidosLlave) => $this->ganadorDeLlave($partidosLlave));

        DB::transaction(function () use ($fase, $siguienteTipo, $ganadoresPorLlave) {
            $nuevaFase = Fase::create([
                'categoria_id' => $fase->categoria_id,
                'tipo' => $siguienteTipo,
                'orden' => $fase->orden + 1,
                'estado' => EstadoFase::EnCurso,
            ]);

            $ganadores = $ganadoresPorLlave->values();

            for ($i = 0; $i < $ganadores->count(); $i += 2) {
                $this->crearLlave($nuevaFase, ($i / 2) + 1, $ganadores[$i], $ganadores[$i + 1]);
            }
        });
    }

    /**
     * Crea los dos partidos (ida y vuelta) de una llave. $equipoLocalVuelta juega la
     * vuelta (el partido decisivo) como local: en la primera ronda es el mejor
     * posicionado en la tabla; en rondas posteriores, el del lado del bracket
     * que ocupó esa posición desde el sembrado inicial.
     */
    private function crearLlave(Fase $fase, int $llave, Inscripcion $equipoLocalVuelta, Inscripcion $equipoVisitaVuelta): void
    {
        $fase->partidos()->create([
            'llave' => $llave,
            'local_inscripcion_id' => $equipoVisitaVuelta->id,
            'visita_inscripcion_id' => $equipoLocalVuelta->id,
            'estado' => EstadoPartido::Programado,
            'es_vuelta' => false,
        ]);

        $fase->partidos()->create([
            'llave' => $llave,
            'local_inscripcion_id' => $equipoLocalVuelta->id,
            'visita_inscripcion_id' => $equipoVisitaVuelta->id,
            'estado' => EstadoPartido::Programado,
            'es_vuelta' => true,
        ]);
    }

    private function ganadorDeLlave($partidosLlave): Inscripcion
    {
        $golesPorInscripcion = [];

        foreach ($partidosLlave as $partido) {
            $golesPorInscripcion[$partido->local_inscripcion_id] = ($golesPorInscripcion[$partido->local_inscripcion_id] ?? 0) + ($partido->goles_local ?? 0);
            $golesPorInscripcion[$partido->visita_inscripcion_id] = ($golesPorInscripcion[$partido->visita_inscripcion_id] ?? 0) + ($partido->goles_visita ?? 0);
        }

        $vuelta = $partidosLlave->firstWhere('es_vuelta', true);
        $mejorSeedId = $vuelta->local_inscripcion_id;
        $peorSeedId = $vuelta->visita_inscripcion_id;

        $golesMejorSeed = $golesPorInscripcion[$mejorSeedId] ?? 0;
        $golesPeorSeed = $golesPorInscripcion[$peorSeedId] ?? 0;

        // En caso de empate en el marcador global, avanza el mejor posicionado en la tabla.
        $ganadorId = $golesPeorSeed > $golesMejorSeed ? $peorSeedId : $mejorSeedId;

        return Inscripcion::findOrFail($ganadorId);
    }

    /**
     * Orden de sembrado de bracket clásico: para n equipos devuelve pares de
     * semillas (1-indexadas) tal que los mejores posicionados solo puedan
     * cruzarse hasta la final (1 vs n, 4 vs n-3, 2 vs n-1, 3 vs n-2, ...).
     *
     * @return array<int, array{0: int, 1: int}>
     */
    private function ordenSembrado(int $numEquipos): array
    {
        $orden = [1, 2];

        while (count($orden) < $numEquipos) {
            $total = count($orden) * 2 + 1;
            $orden = collect($orden)->flatMap(fn ($semilla) => [$semilla, $total - $semilla])->all();
        }

        return array_chunk($orden, 2);
    }
}
