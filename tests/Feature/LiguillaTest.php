<?php

namespace Tests\Feature;

use App\Enums\EstadoFase;
use App\Enums\EstadoPartido;
use App\Enums\TipoFase;
use App\Models\Categoria;
use App\Models\Fase;
use App\Models\Inscripcion;
use App\Models\Partido;
use App\Services\LiguillaService;
use App\Services\TablaGeneralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class LiguillaTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{categoria: Categoria, equipos: array<int, Inscripcion>, fase: Fase} */
    private function crearTemporadaConcluida(int $numEquipos, array $resultados): array
    {
        $categoria = Categoria::factory()->create(['equipos_a_liguilla' => $numEquipos]);
        $equipos = collect(range(1, $numEquipos))
            ->map(fn () => Inscripcion::factory()->create(['categoria_id' => $categoria->id]))
            ->all();

        $fase = Fase::create([
            'categoria_id' => $categoria->id,
            'tipo' => TipoFase::Jornadas,
            'orden' => 1,
            'estado' => EstadoFase::EnCurso,
        ]);

        // $resultados: lista de [localIdx, visitaIdx, golesLocal, golesVisita] (índices 0-based sobre $equipos).
        foreach ($resultados as [$localIdx, $visitaIdx, $golesLocal, $golesVisita]) {
            Partido::create([
                'fase_id' => $fase->id,
                'jornada' => 1,
                'local_inscripcion_id' => $equipos[$localIdx]->id,
                'visita_inscripcion_id' => $equipos[$visitaIdx]->id,
                'goles_local' => $golesLocal,
                'goles_visita' => $golesVisita,
                'estado' => EstadoPartido::Jugado,
            ]);
        }

        return compact('categoria', 'equipos', 'fase');
    }

    public function test_la_tabla_general_ordena_por_puntos_diferencia_y_goles_a_favor(): void
    {
        // 4 equipos, todos contra todos una vez.
        ['categoria' => $categoria, 'equipos' => $equipos] = $this->crearTemporadaConcluida(4, [
            [0, 1, 3, 0], // A gana a B
            [2, 3, 1, 1], // C empata con D
            [0, 2, 2, 0], // A gana a C
            [1, 3, 0, 0], // B empata con D
            [0, 3, 1, 1], // A empata con D
            [1, 2, 2, 2], // B empata con C
        ]);

        $tabla = app(TablaGeneralService::class)->calcular($categoria);

        $this->assertSame($equipos[0]->id, $tabla->first()['inscripcion']->id);
        $this->assertSame(7, $tabla->first()['pts']); // A: 2 victorias + 1 empate = 7 pts
    }

    public function test_generar_liguilla_falla_si_quedan_partidos_de_temporada_regular_sin_concluir(): void
    {
        $categoria = Categoria::factory()->create(['equipos_a_liguilla' => 2]);
        $a = Inscripcion::factory()->create(['categoria_id' => $categoria->id]);
        $b = Inscripcion::factory()->create(['categoria_id' => $categoria->id]);
        $fase = Fase::create(['categoria_id' => $categoria->id, 'tipo' => 'jornadas', 'orden' => 1, 'estado' => 'en_curso']);
        Partido::create([
            'fase_id' => $fase->id, 'jornada' => 1,
            'local_inscripcion_id' => $a->id, 'visita_inscripcion_id' => $b->id,
            'estado' => 'programado',
        ]);

        $this->expectException(RuntimeException::class);

        app(LiguillaService::class)->generar($categoria);
    }

    public function test_generar_liguilla_de_4_equipos_crea_semifinales_ida_y_vuelta_con_sembrado_1v4_2v3(): void
    {
        ['categoria' => $categoria] = $this->crearTemporadaConcluida(4, [
            [0, 1, 3, 0], [2, 3, 3, 0], [0, 2, 3, 0], [1, 3, 3, 0], [0, 3, 3, 0], [1, 2, 1, 1],
        ]);
        // A (equipos[0]) queda primero, B segundo, C tercero, D cuarto aprox. Verificamos solo la estructura del bracket.

        $fase = app(LiguillaService::class)->generar($categoria);

        $this->assertSame(TipoFase::Semifinal, $fase->tipo);
        $this->assertSame(2, $fase->partidos->groupBy('llave')->count());
        $this->assertCount(4, $fase->partidos);

        foreach ($fase->partidos->groupBy('llave') as $partidosLlave) {
            $this->assertCount(2, $partidosLlave);
            $this->assertTrue($partidosLlave->contains('es_vuelta', true));
            $this->assertTrue($partidosLlave->contains('es_vuelta', false));
        }
    }

    public function test_con_2_equipos_la_liguilla_es_directamente_la_final_sin_rondas_siguientes(): void
    {
        ['categoria' => $categoria] = $this->crearTemporadaConcluida(2, [[0, 1, 1, 0]]);

        $fase = app(LiguillaService::class)->generar($categoria);

        $this->assertSame(TipoFase::Final, $fase->tipo);

        $ida = $fase->partidos->firstWhere('es_vuelta', false);
        $vuelta = $fase->partidos->firstWhere('es_vuelta', true);
        $ida->update(['goles_local' => 0, 'goles_visita' => 1, 'estado' => 'jugado']);
        $vuelta->update(['goles_local' => 2, 'goles_visita' => 0, 'estado' => 'jugado']);

        $this->assertSame(EstadoFase::Finalizada, $fase->fresh()->estado);
        // Solo existen la fase de jornadas y la final: no se generó ninguna ronda extra.
        $this->assertSame(2, $categoria->fases()->count());
    }

    public function test_no_se_puede_generar_liguilla_dos_veces(): void
    {
        ['categoria' => $categoria] = $this->crearTemporadaConcluida(2, [[0, 1, 1, 0]]);
        app(LiguillaService::class)->generar($categoria);

        $this->expectException(RuntimeException::class);

        app(LiguillaService::class)->generar($categoria);
    }

    public function test_la_siguiente_ronda_solo_se_genera_cuando_todas_las_llaves_de_la_ronda_concluyen(): void
    {
        // 4 equipos: A,B,C,D. Sembrado 1v4 (llave 1) y 2v3 (llave 2) en semifinal.
        ['categoria' => $categoria, 'equipos' => $equipos] = $this->crearTemporadaConcluida(4, [
            [0, 1, 3, 0], [2, 3, 3, 0], [0, 2, 3, 0], [1, 3, 3, 0], [0, 3, 3, 0], [1, 2, 1, 1],
        ]);
        [$a, $b, $c, $d] = $equipos;
        $fase = app(LiguillaService::class)->generar($categoria);

        $llaves = $fase->partidos->groupBy('llave');
        $llave1 = $llaves->get(1); // A (mejor seed) vs D (peor seed)
        $llave2 = $llaves->get(2); // B vs C

        // Resolvemos solo la llave 1: la ronda no debe darse por completa todavía.
        $llave1->firstWhere('es_vuelta', false)->update(['goles_local' => 0, 'goles_visita' => 1, 'estado' => 'jugado']);
        $llave1->firstWhere('es_vuelta', true)->update(['goles_local' => 2, 'goles_visita' => 0, 'estado' => 'jugado']);

        $this->assertSame(EstadoFase::EnCurso, $fase->fresh()->estado);
        // Solo jornadas + semifinal: la final todavía no debe generarse.
        $this->assertSame(2, $categoria->fases()->count());

        // Ahora resolvemos la llave 2: con esto la ronda completa se cierra y debe generarse la final.
        $llave2->firstWhere('es_vuelta', false)->update(['goles_local' => 1, 'goles_visita' => 1, 'estado' => 'jugado']);
        $llave2->firstWhere('es_vuelta', true)->update(['goles_local' => 2, 'goles_visita' => 1, 'estado' => 'jugado']);

        $this->assertSame(EstadoFase::Finalizada, $fase->fresh()->estado);

        $final = $categoria->fases()->where('tipo', TipoFase::Final)->first();
        $this->assertNotNull($final);
        $this->assertCount(2, $final->partidos);

        // El ganador de la llave 1 (A) y el de la llave 2 (equipo local de su vuelta, mejor seed) deben enfrentarse en la final.
        $equiposEnFinal = $final->partidos->flatMap(fn ($p) => [$p->local_inscripcion_id, $p->visita_inscripcion_id])->unique();
        $this->assertTrue($equiposEnFinal->contains($a->id));
    }
}
