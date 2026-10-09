<?php

namespace Tests\Feature;

use App\Enums\EstadoPartido;
use App\Enums\TipoEventoPartido;
use App\Models\Cargo;
use App\Models\Categoria;
use App\Models\ConceptoCobro;
use App\Models\Fase;
use App\Models\Inscripcion;
use App\Models\Partido;
use App\Models\RegistroJugador;
use App\Models\Suspension;
use App\Services\CedulaArbitralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ReglasNegocioAutomaticasTest extends TestCase
{
    use RefreshDatabase;

    private function crearPartido(array $atributosCategoria = []): array
    {
        $categoria = Categoria::factory()->create(array_merge([
            'amarillas_para_suspension' => 3,
            'partidos_castigo_roja' => 2,
        ], $atributosCategoria));

        $local = Inscripcion::factory()->create(['categoria_id' => $categoria->id]);
        $visita = Inscripcion::factory()->create(['categoria_id' => $categoria->id]);

        $registroLocal = RegistroJugador::factory()->create(['inscripcion_id' => $local->id]);
        $registroVisita = RegistroJugador::factory()->create(['inscripcion_id' => $visita->id]);

        $fase = Fase::create([
            'categoria_id' => $categoria->id,
            'tipo' => 'jornadas',
            'orden' => 1,
            'estado' => 'en_curso',
        ]);

        $partido = Partido::create([
            'fase_id' => $fase->id,
            'jornada' => 1,
            'local_inscripcion_id' => $local->id,
            'visita_inscripcion_id' => $visita->id,
            'estado' => EstadoPartido::Programado,
        ]);

        ConceptoCobro::factory()->create([
            'categoria_id' => $categoria->id,
            'nombre' => 'Arbitraje por partido',
            'tipo' => 'arbitraje',
            'automatico' => true,
            'evento_disparador' => 'partido_programado',
            'monto' => 200,
        ]);
        ConceptoCobro::factory()->create([
            'categoria_id' => $categoria->id,
            'nombre' => 'Multa amarilla',
            'tipo' => 'multa',
            'automatico' => true,
            'evento_disparador' => 'tarjeta_amarilla',
            'monto' => 30,
        ]);
        ConceptoCobro::factory()->create([
            'categoria_id' => $categoria->id,
            'nombre' => 'Multa roja',
            'tipo' => 'multa',
            'automatico' => true,
            'evento_disparador' => 'tarjeta_roja',
            'monto' => 100,
        ]);
        ConceptoCobro::factory()->create([
            'categoria_id' => $categoria->id,
            'nombre' => 'Multa por default',
            'tipo' => 'multa',
            'automatico' => true,
            'evento_disparador' => 'default_partido',
            'monto' => 500,
        ]);

        return compact('categoria', 'local', 'visita', 'registroLocal', 'registroVisita', 'partido');
    }

    public function test_tarjeta_amarilla_genera_cargo_automatico(): void
    {
        ['registroLocal' => $registro, 'partido' => $partido] = $this->crearPartido();

        app(CedulaArbitralService::class)->registrarEvento($partido, $registro, TipoEventoPartido::Amarilla, 30);

        $this->assertSame(1, $registro->fresh()->amarillas_acumuladas);
        $this->assertDatabaseHas('cargos', [
            'inscripcion_id' => $registro->inscripcion_id,
            'monto' => 30,
        ]);
    }

    public function test_acumular_amarillas_hasta_el_umbral_genera_suspension_y_reinicia_contador(): void
    {
        ['registroLocal' => $registro, 'partido' => $partido] = $this->crearPartido(['amarillas_para_suspension' => 3]);
        $cedula = app(CedulaArbitralService::class);

        $cedula->registrarEvento($partido, $registro, TipoEventoPartido::Amarilla, 10);
        $cedula->registrarEvento($partido, $registro, TipoEventoPartido::Amarilla, 20);
        $this->assertSame(2, $registro->fresh()->amarillas_acumuladas);
        $this->assertDatabaseCount('suspensiones', 0);

        $cedula->registrarEvento($partido, $registro, TipoEventoPartido::Amarilla, 30);

        $this->assertSame(0, $registro->fresh()->amarillas_acumuladas);
        $this->assertDatabaseHas('suspensiones', [
            'registro_jugador_id' => $registro->id,
            'motivo' => 'acumulacion_amarillas',
            'partidos_castigo' => 1,
        ]);
    }

    public function test_tarjeta_roja_genera_cargo_y_suspension_con_partidos_de_castigo_de_la_categoria(): void
    {
        ['registroVisita' => $registro, 'partido' => $partido] = $this->crearPartido(['partidos_castigo_roja' => 2]);

        app(CedulaArbitralService::class)->registrarEvento($partido, $registro, TipoEventoPartido::Roja, 60);

        $this->assertDatabaseHas('cargos', ['inscripcion_id' => $registro->inscripcion_id, 'monto' => 100]);
        $this->assertDatabaseHas('suspensiones', [
            'registro_jugador_id' => $registro->id,
            'motivo' => 'roja_directa',
            'partidos_castigo' => 2,
        ]);
    }

    public function test_no_se_puede_registrar_evento_de_un_jugador_ajeno_al_partido(): void
    {
        ['partido' => $partido] = $this->crearPartido();
        $jugadorAjeno = RegistroJugador::factory()->create();

        $this->expectException(RuntimeException::class);

        app(CedulaArbitralService::class)->registrarEvento($partido, $jugadorAjeno, TipoEventoPartido::Gol, 10);
    }

    public function test_eliminar_la_tarjeta_amarilla_mas_reciente_revierte_el_contador_y_el_cargo(): void
    {
        ['registroLocal' => $registro, 'partido' => $partido] = $this->crearPartido();
        $cedula = app(CedulaArbitralService::class);

        $evento = $cedula->registrarEvento($partido, $registro, TipoEventoPartido::Amarilla, 10);
        $this->assertSame(1, $registro->fresh()->amarillas_acumuladas);

        $cedula->eliminarEvento($evento);

        $this->assertSame(0, $registro->fresh()->amarillas_acumuladas);
        $this->assertDatabaseCount('cargos', 0);
    }

    public function test_no_se_puede_eliminar_una_amarilla_que_no_es_la_mas_reciente(): void
    {
        ['registroLocal' => $registro, 'partido' => $partido] = $this->crearPartido();
        $cedula = app(CedulaArbitralService::class);

        $primera = $cedula->registrarEvento($partido, $registro, TipoEventoPartido::Amarilla, 10);
        $cedula->registrarEvento($partido, $registro, TipoEventoPartido::Amarilla, 20);

        $this->expectException(RuntimeException::class);

        $cedula->eliminarEvento($primera);
    }

    public function test_no_se_puede_eliminar_evento_si_el_cargo_ya_fue_pagado(): void
    {
        ['registroLocal' => $registro, 'partido' => $partido] = $this->crearPartido();
        $cedula = app(CedulaArbitralService::class);

        $evento = $cedula->registrarEvento($partido, $registro, TipoEventoPartido::Amarilla, 10);
        $evento->cargo->update(['estado' => 'pagado']);

        $this->expectException(RuntimeException::class);

        $cedula->eliminarEvento($evento);
    }

    public function test_programar_partido_genera_cargo_de_arbitraje_para_ambos_equipos_una_sola_vez(): void
    {
        ['local' => $local, 'visita' => $visita, 'partido' => $partido] = $this->crearPartido();

        $partido->update(['fecha_hora' => now()->addDay(), 'cancha' => 'Cancha 1']);

        $this->assertDatabaseHas('cargos', ['inscripcion_id' => $local->id, 'monto' => 200]);
        $this->assertDatabaseHas('cargos', ['inscripcion_id' => $visita->id, 'monto' => 200]);
        $this->assertSame(2, Cargo::where('partido_id', $partido->id)->count());

        // Cambiar solo la cancha (fecha_hora ya estaba fijada) no debe duplicar el cargo.
        $partido->update(['cancha' => 'Cancha 2']);

        $this->assertSame(2, Cargo::where('partido_id', $partido->id)->count());
    }

    public function test_marcar_default_asigna_marcador_automatico_y_cobra_solo_al_equipo_responsable(): void
    {
        ['local' => $local, 'visita' => $visita, 'partido' => $partido] = $this->crearPartido();

        app(CedulaArbitralService::class)->marcarDefault($partido, 'local');
        $partido->refresh();

        $this->assertSame(EstadoPartido::DefaultLocal, $partido->estado);
        $this->assertSame(0, $partido->goles_local);
        $this->assertSame(3, $partido->goles_visita);
        $this->assertDatabaseHas('cargos', ['inscripcion_id' => $local->id, 'monto' => 500]);
        $this->assertDatabaseMissing('cargos', ['inscripcion_id' => $visita->id, 'monto' => 500]);
    }

    public function test_el_cumplimiento_de_suspension_no_cuenta_el_partido_de_origen_pero_si_el_siguiente(): void
    {
        ['registroVisita' => $registro, 'visita' => $visita, 'categoria' => $categoria, 'partido' => $partidoOrigen] = $this->crearPartido(['partidos_castigo_roja' => 1]);
        $cedula = app(CedulaArbitralService::class);

        $cedula->registrarEvento($partidoOrigen, $registro, TipoEventoPartido::Roja, 80);
        $cedula->finalizar($partidoOrigen, 1, 0);

        $suspension = Suspension::where('registro_jugador_id', $registro->id)->firstOrFail();
        $this->assertSame(0, $suspension->partidos_cumplidos);
        $this->assertFalse($suspension->cumplida);

        // Un segundo partido del mismo equipo sí debe contar como cumplimiento.
        $rival = Inscripcion::factory()->create(['categoria_id' => $categoria->id]);
        $fase = Fase::first();
        $segundoPartido = Partido::create([
            'fase_id' => $fase->id,
            'jornada' => 2,
            'local_inscripcion_id' => $visita->id,
            'visita_inscripcion_id' => $rival->id,
            'estado' => EstadoPartido::Programado,
        ]);

        $cedula->finalizar($segundoPartido, 2, 1);

        $this->assertSame(1, $suspension->fresh()->partidos_cumplidos);
        $this->assertTrue($suspension->fresh()->cumplida);
    }
}
