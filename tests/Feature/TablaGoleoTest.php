<?php

namespace Tests\Feature;

use App\Enums\TipoEventoPartido;
use App\Models\Categoria;
use App\Models\EventoPartido;
use App\Models\Fase;
use App\Models\Inscripcion;
use App\Models\Partido;
use App\Models\RegistroJugador;
use App\Services\TablaGoleoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TablaGoleoTest extends TestCase
{
    use RefreshDatabase;

    public function test_calcula_goles_por_jugador_e_ignora_autogoles(): void
    {
        $categoria = Categoria::factory()->create();
        $local = Inscripcion::factory()->create(['categoria_id' => $categoria->id]);
        $visita = Inscripcion::factory()->create(['categoria_id' => $categoria->id]);
        $registroA = RegistroJugador::factory()->create(['inscripcion_id' => $local->id]);
        $registroB = RegistroJugador::factory()->create(['inscripcion_id' => $visita->id]);

        $fase = Fase::create(['categoria_id' => $categoria->id, 'tipo' => 'jornadas', 'orden' => 1, 'estado' => 'en_curso']);
        $partido = Partido::create([
            'fase_id' => $fase->id, 'jornada' => 1,
            'local_inscripcion_id' => $local->id, 'visita_inscripcion_id' => $visita->id,
            'estado' => 'jugado', 'goles_local' => 2, 'goles_visita' => 1,
        ]);

        EventoPartido::create(['partido_id' => $partido->id, 'registro_jugador_id' => $registroA->id, 'tipo' => TipoEventoPartido::Gol]);
        EventoPartido::create(['partido_id' => $partido->id, 'registro_jugador_id' => $registroA->id, 'tipo' => TipoEventoPartido::Gol]);
        EventoPartido::create(['partido_id' => $partido->id, 'registro_jugador_id' => $registroB->id, 'tipo' => TipoEventoPartido::Gol]);
        EventoPartido::create(['partido_id' => $partido->id, 'registro_jugador_id' => $registroB->id, 'tipo' => TipoEventoPartido::Autogol]);

        $tabla = app(TablaGoleoService::class)->calcular($categoria);

        $this->assertSame($registroA->id, $tabla->first()['registro']->id);
        $this->assertSame(2, $tabla->first()['goles']);
        $this->assertSame(1, $tabla->firstWhere('registro.id', $registroB->id)['goles']);
    }
}
