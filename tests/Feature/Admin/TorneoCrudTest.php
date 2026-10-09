<?php

namespace Tests\Feature\Admin;

use App\Models\Torneo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActuaComoAdmin;
use Tests\TestCase;

class TorneoCrudTest extends TestCase
{
    use RefreshDatabase;
    use ActuaComoAdmin;

    public function test_crear_un_torneo(): void
    {
        $this->iniciarSesionComoAdmin();

        $response = $this->post(route('admin.torneos.store'), [
            'nombre' => 'Clausura 2027',
            'fecha_inicio' => '2027-01-15',
            'estado' => 'planeacion',
        ]);

        $response->assertRedirect(route('admin.torneos.index'));
        $this->assertDatabaseHas('torneos', ['nombre' => 'Clausura 2027', 'estado' => 'planeacion']);
    }

    public function test_no_se_puede_crear_un_torneo_con_estado_invalido(): void
    {
        $this->iniciarSesionComoAdmin();

        $response = $this->post(route('admin.torneos.store'), [
            'nombre' => 'Torneo Inválido',
            'fecha_inicio' => '2027-01-15',
            'estado' => 'no-existe',
        ]);

        $response->assertSessionHasErrors('estado');
        $this->assertDatabaseMissing('torneos', ['nombre' => 'Torneo Inválido']);
    }

    public function test_actualizar_un_torneo(): void
    {
        $this->iniciarSesionComoAdmin();
        $torneo = Torneo::factory()->create();

        $response = $this->put(route('admin.torneos.update', $torneo), [
            'nombre' => 'Nombre Actualizado',
            'fecha_inicio' => $torneo->fecha_inicio->toDateString(),
            'estado' => 'finalizado',
        ]);

        $response->assertRedirect(route('admin.torneos.index'));
        $this->assertSame('Nombre Actualizado', $torneo->fresh()->nombre);
        $this->assertSame('finalizado', $torneo->fresh()->estado->value);
    }

    public function test_eliminar_un_torneo(): void
    {
        $this->iniciarSesionComoAdmin();
        $torneo = Torneo::factory()->create();

        $response = $this->delete(route('admin.torneos.destroy', $torneo));

        $response->assertRedirect(route('admin.torneos.index'));
        $this->assertDatabaseMissing('torneos', ['id' => $torneo->id]);
    }
}
