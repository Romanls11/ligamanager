<?php

namespace Tests\Feature\Admin;

use App\Models\Jugador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActuaComoAdmin;
use Tests\TestCase;

class JugadorCrudTest extends TestCase
{
    use RefreshDatabase;
    use ActuaComoAdmin;

    public function test_crear_un_jugador(): void
    {
        $this->iniciarSesionComoAdmin();

        $response = $this->post(route('admin.jugadores.store'), [
            'nombre_completo' => 'Carlos Ramírez',
            'fecha_nacimiento' => '1998-04-12',
        ]);

        $response->assertRedirect(route('admin.jugadores.index'));
        $this->assertDatabaseHas('jugadores', ['nombre_completo' => 'Carlos Ramírez']);
    }

    public function test_no_se_puede_repetir_curp(): void
    {
        $this->iniciarSesionComoAdmin();
        Jugador::factory()->create(['curp' => 'RAMC980412HDFXXX01']);

        $response = $this->post(route('admin.jugadores.store'), [
            'nombre_completo' => 'Otro Jugador',
            'fecha_nacimiento' => '1990-01-01',
            'curp' => 'RAMC980412HDFXXX01',
        ]);

        $response->assertSessionHasErrors('curp');
    }

    public function test_actualizar_un_jugador_ignora_su_propia_curp_al_validar_unicidad(): void
    {
        $this->iniciarSesionComoAdmin();
        $jugador = Jugador::factory()->create(['curp' => 'GARC900101HDFXXX02']);

        $response = $this->put(route('admin.jugadores.update', $jugador), [
            'nombre_completo' => 'Nombre Actualizado',
            'fecha_nacimiento' => $jugador->fecha_nacimiento->toDateString(),
            'curp' => 'GARC900101HDFXXX02',
        ]);

        $response->assertRedirect(route('admin.jugadores.index'));
        $this->assertSame('Nombre Actualizado', $jugador->fresh()->nombre_completo);
    }

    public function test_el_buscador_filtra_por_nombre(): void
    {
        $this->iniciarSesionComoAdmin();
        Jugador::factory()->create(['nombre_completo' => 'Jugador Encontrable']);
        Jugador::factory()->create(['nombre_completo' => 'Otro Jugador']);

        $response = $this->get(route('admin.jugadores.index', ['buscar' => 'Encontrable']));

        $response->assertSee('Jugador Encontrable');
        $response->assertDontSee('Otro Jugador');
    }

    public function test_eliminar_un_jugador(): void
    {
        $this->iniciarSesionComoAdmin();
        $jugador = Jugador::factory()->create();

        $response = $this->delete(route('admin.jugadores.destroy', $jugador));

        $response->assertRedirect(route('admin.jugadores.index'));
        $this->assertDatabaseMissing('jugadores', ['id' => $jugador->id]);
    }
}
