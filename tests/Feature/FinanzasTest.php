<?php

namespace Tests\Feature;

use App\Enums\EstadoCargo;
use App\Enums\EstadoInscripcion;
use App\Enums\MetodoPago;
use App\Enums\RolAsignacionArbitro;
use App\Models\Arbitro;
use App\Models\AsignacionArbitro;
use App\Models\Cargo;
use App\Models\Categoria;
use App\Models\ConceptoCobro;
use App\Models\Fase;
use App\Models\Inscripcion;
use App\Models\Partido;
use App\Models\User;
use App\Services\BloqueoAdeudoService;
use App\Services\PagoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class FinanzasTest extends TestCase
{
    use RefreshDatabase;

    private function crearCargo(array $atributos = []): Cargo
    {
        $inscripcion = Inscripcion::factory()->create();
        $concepto = ConceptoCobro::factory()->create(['categoria_id' => $inscripcion->categoria_id]);

        return Cargo::create(array_merge([
            'inscripcion_id' => $inscripcion->id,
            'concepto_cobro_id' => $concepto->id,
            'monto' => 100,
            'estado' => EstadoCargo::Pendiente,
        ], $atributos));
    }

    public function test_registrar_pago_total_marca_el_cargo_como_pagado_y_genera_folio(): void
    {
        $cargo = $this->crearCargo(['monto' => 100]);
        $usuario = User::factory()->create();

        $pago = app(PagoService::class)->registrarPago($cargo, 100, MetodoPago::Efectivo, null, $usuario);

        $this->assertSame(EstadoCargo::Pagado, $cargo->fresh()->estado);
        $this->assertMatchesRegularExpression('/^REC-\d{6}$/', $pago->folio_recibo);
    }

    public function test_registrar_pago_parcial_marca_el_cargo_como_parcial(): void
    {
        $cargo = $this->crearCargo(['monto' => 100]);
        $usuario = User::factory()->create();

        app(PagoService::class)->registrarPago($cargo, 40, MetodoPago::Efectivo, null, $usuario);

        $this->assertSame(EstadoCargo::Parcial, $cargo->fresh()->estado);
        $this->assertSame(60.0, $cargo->fresh()->saldoPendiente());
    }

    public function test_no_se_puede_pagar_mas_del_saldo_pendiente(): void
    {
        $cargo = $this->crearCargo(['monto' => 100]);
        $usuario = User::factory()->create();

        $this->expectException(RuntimeException::class);

        app(PagoService::class)->registrarPago($cargo, 150, MetodoPago::Efectivo, null, $usuario);
    }

    public function test_no_se_puede_pagar_un_cargo_condonado(): void
    {
        $cargo = $this->crearCargo(['monto' => 100, 'estado' => EstadoCargo::Condonado]);
        $usuario = User::factory()->create();

        $this->expectException(RuntimeException::class);

        app(PagoService::class)->registrarPago($cargo, 50, MetodoPago::Efectivo, null, $usuario);
    }

    public function test_no_se_puede_condonar_un_cargo_ya_pagado(): void
    {
        $cargo = $this->crearCargo(['monto' => 100, 'estado' => EstadoCargo::Pagado]);

        $this->expectException(RuntimeException::class);

        app(PagoService::class)->condonarCargo($cargo);
    }

    public function test_inscripcion_se_suspende_por_adeudo_vencido_al_correr_la_evaluacion(): void
    {
        $inscripcion = Inscripcion::factory()->create(['estado' => EstadoInscripcion::Activa]);
        $this->crearCargo([
            'inscripcion_id' => $inscripcion->id,
            'fecha_vencimiento' => now()->subDays(5),
        ]);

        app(BloqueoAdeudoService::class)->evaluar($inscripcion);

        $this->assertSame(EstadoInscripcion::SuspendidaPorAdeudo, $inscripcion->fresh()->estado);
    }

    public function test_inscripcion_se_reactiva_al_pagar_el_adeudo_vencido(): void
    {
        $inscripcion = Inscripcion::factory()->create(['estado' => EstadoInscripcion::Activa]);
        $cargo = $this->crearCargo([
            'inscripcion_id' => $inscripcion->id,
            'monto' => 100,
            'fecha_vencimiento' => now()->subDays(5),
        ]);
        app(BloqueoAdeudoService::class)->evaluar($inscripcion);
        $this->assertSame(EstadoInscripcion::SuspendidaPorAdeudo, $inscripcion->fresh()->estado);

        app(PagoService::class)->registrarPago($cargo, 100, MetodoPago::Efectivo, null, User::factory()->create());

        $this->assertSame(EstadoInscripcion::Activa, $inscripcion->fresh()->estado);
    }

    public function test_no_se_puede_dar_de_alta_un_jugador_en_un_equipo_suspendido_por_adeudo(): void
    {
        $inscripcion = Inscripcion::factory()->create(['estado' => EstadoInscripcion::SuspendidaPorAdeudo]);
        $jugador = \App\Models\Jugador::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('suspendido por adeudo vencido');

        app(\App\Services\AltaJugadorService::class)->registrar($inscripcion, $jugador);
    }

    public function test_no_reactiva_una_inscripcion_retirada(): void
    {
        $inscripcion = Inscripcion::factory()->create(['estado' => EstadoInscripcion::Retirada]);

        app(BloqueoAdeudoService::class)->evaluar($inscripcion);

        $this->assertSame(EstadoInscripcion::Retirada, $inscripcion->fresh()->estado);
    }

    public function test_no_se_puede_pagar_a_un_arbitro_mas_de_su_tarifa(): void
    {
        $categoria = Categoria::factory()->create();
        $local = Inscripcion::factory()->create(['categoria_id' => $categoria->id]);
        $visita = Inscripcion::factory()->create(['categoria_id' => $categoria->id]);
        $fase = Fase::create(['categoria_id' => $categoria->id, 'tipo' => 'jornadas', 'orden' => 1, 'estado' => 'en_curso']);
        $partido = Partido::create([
            'fase_id' => $fase->id,
            'jornada' => 1,
            'local_inscripcion_id' => $local->id,
            'visita_inscripcion_id' => $visita->id,
            'estado' => 'programado',
        ]);
        $asignacion = AsignacionArbitro::create([
            'partido_id' => $partido->id,
            'arbitro_id' => Arbitro::factory()->create()->id,
            'rol' => RolAsignacionArbitro::Central,
            'tarifa' => 200,
        ]);

        $this->expectException(RuntimeException::class);

        app(PagoService::class)->registrarPagoArbitro($asignacion, 250, null, User::factory()->create());
    }
}
