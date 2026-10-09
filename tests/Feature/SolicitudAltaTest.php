<?php

namespace Tests\Feature;

use App\Enums\EstadoInscripcion;
use App\Enums\EstadoRegistroJugador;
use App\Enums\EstadoSolicitudAlta;
use App\Models\Categoria;
use App\Models\Inscripcion;
use App\Models\Jugador;
use App\Models\RegistroJugador;
use App\Models\User;
use App\Services\SolicitudAltaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SolicitudAltaTest extends TestCase
{
    use RefreshDatabase;

    public function test_delegado_puede_solicitar_alta_y_admin_aprobarla_da_de_alta_al_jugador(): void
    {
        $categoria = Categoria::factory()->create(['max_jugadores' => 20]);
        $inscripcion = Inscripcion::factory()->create(['categoria_id' => $categoria->id]);
        $jugador = Jugador::factory()->create();
        $delegado = User::factory()->create();
        $admin = User::factory()->create();

        $solicitud = app(SolicitudAltaService::class)->solicitar($inscripcion, $jugador, 10, $delegado);
        $this->assertSame(EstadoSolicitudAlta::Pendiente, $solicitud->estado);

        app(SolicitudAltaService::class)->aprobar($solicitud, $admin);

        $this->assertSame(EstadoSolicitudAlta::Aprobada, $solicitud->fresh()->estado);
        $this->assertDatabaseHas('registros_jugador', [
            'inscripcion_id' => $inscripcion->id,
            'jugador_id' => $jugador->id,
            'numero_camiseta' => 10,
            'estado' => EstadoRegistroJugador::Activo->value,
        ]);
    }

    public function test_no_se_puede_duplicar_una_solicitud_pendiente_para_el_mismo_jugador(): void
    {
        $inscripcion = Inscripcion::factory()->create();
        $jugador = Jugador::factory()->create();
        $delegado = User::factory()->create();

        app(SolicitudAltaService::class)->solicitar($inscripcion, $jugador, null, $delegado);

        $this->expectException(RuntimeException::class);

        app(SolicitudAltaService::class)->solicitar($inscripcion, $jugador, null, $delegado);
    }

    public function test_no_se_puede_aprobar_una_solicitud_de_un_equipo_suspendido_por_adeudo(): void
    {
        $inscripcion = Inscripcion::factory()->create(['estado' => EstadoInscripcion::SuspendidaPorAdeudo]);
        $jugador = Jugador::factory()->create();
        $solicitud = app(SolicitudAltaService::class)->solicitar($inscripcion, $jugador, null, User::factory()->create());

        $this->expectException(RuntimeException::class);

        try {
            app(SolicitudAltaService::class)->aprobar($solicitud, User::factory()->create());
        } finally {
            $this->assertSame(EstadoSolicitudAlta::Pendiente, $solicitud->fresh()->estado);
        }
    }

    public function test_aprobar_respeta_el_cupo_maximo_y_deja_la_solicitud_pendiente_si_falla(): void
    {
        $categoria = Categoria::factory()->create(['max_jugadores' => 1]);
        $inscripcion = Inscripcion::factory()->create(['categoria_id' => $categoria->id]);
        RegistroJugador::factory()->create(['inscripcion_id' => $inscripcion->id, 'estado' => EstadoRegistroJugador::Activo]);

        $jugadorNuevo = Jugador::factory()->create();
        $solicitud = app(SolicitudAltaService::class)->solicitar($inscripcion, $jugadorNuevo, null, User::factory()->create());

        $this->expectException(RuntimeException::class);

        try {
            app(SolicitudAltaService::class)->aprobar($solicitud, User::factory()->create());
        } finally {
            $this->assertSame(EstadoSolicitudAlta::Pendiente, $solicitud->fresh()->estado);
        }
    }

    public function test_rechazar_registra_el_motivo_y_no_da_de_alta_al_jugador(): void
    {
        $inscripcion = Inscripcion::factory()->create();
        $jugador = Jugador::factory()->create();
        $solicitud = app(SolicitudAltaService::class)->solicitar($inscripcion, $jugador, null, User::factory()->create());

        app(SolicitudAltaService::class)->rechazar($solicitud, User::factory()->create(), 'Documentación incompleta');

        $this->assertSame(EstadoSolicitudAlta::Rechazada, $solicitud->fresh()->estado);
        $this->assertSame('Documentación incompleta', $solicitud->fresh()->motivo_rechazo);
        $this->assertDatabaseMissing('registros_jugador', ['jugador_id' => $jugador->id]);
    }

    public function test_no_se_puede_revisar_dos_veces_la_misma_solicitud(): void
    {
        $inscripcion = Inscripcion::factory()->create();
        $jugador = Jugador::factory()->create();
        $admin = User::factory()->create();
        $solicitud = app(SolicitudAltaService::class)->solicitar($inscripcion, $jugador, null, User::factory()->create());
        app(SolicitudAltaService::class)->aprobar($solicitud, $admin);

        $this->expectException(RuntimeException::class);

        app(SolicitudAltaService::class)->rechazar($solicitud, $admin, null);
    }
}
