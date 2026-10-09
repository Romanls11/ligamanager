<?php

namespace Tests\Feature\Admin;

use App\Models\Arbitro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Concerns\ActuaComoAdmin;
use Tests\TestCase;

class ArbitroCrudTest extends TestCase
{
    use RefreshDatabase;
    use ActuaComoAdmin;

    public function test_crear_un_arbitro(): void
    {
        $this->iniciarSesionComoAdmin();

        $response = $this->post(route('admin.arbitros.store'), [
            'nombre' => 'Juan Pérez',
            'tarifa_default' => 250,
            'activo' => '1',
        ]);

        $response->assertRedirect(route('admin.arbitros.index'));
        $this->assertDatabaseHas('arbitros', ['nombre' => 'Juan Pérez', 'activo' => true]);
    }

    public function test_no_se_puede_vincular_el_mismo_usuario_a_dos_arbitros(): void
    {
        Role::findOrCreate('arbitro');
        $this->iniciarSesionComoAdmin();
        $usuario = User::factory()->create();
        $usuario->assignRole('arbitro');
        Arbitro::factory()->create(['user_id' => $usuario->id]);

        $response = $this->post(route('admin.arbitros.store'), [
            'nombre' => 'Segundo Árbitro',
            'tarifa_default' => 200,
            'user_id' => $usuario->id,
        ]);

        $response->assertSessionHasErrors('user_id');
    }

    public function test_actualizar_un_arbitro_conserva_su_propio_usuario_vinculado(): void
    {
        Role::findOrCreate('arbitro');
        $this->iniciarSesionComoAdmin();
        $usuario = User::factory()->create();
        $usuario->assignRole('arbitro');
        $arbitro = Arbitro::factory()->create(['user_id' => $usuario->id]);

        $response = $this->put(route('admin.arbitros.update', $arbitro), [
            'nombre' => 'Nombre Actualizado',
            'tarifa_default' => 300,
            'user_id' => $usuario->id,
        ]);

        $response->assertRedirect(route('admin.arbitros.index'));
        $this->assertSame('Nombre Actualizado', $arbitro->fresh()->nombre);
    }

    public function test_eliminar_un_arbitro(): void
    {
        $this->iniciarSesionComoAdmin();
        $arbitro = Arbitro::factory()->create();

        $response = $this->delete(route('admin.arbitros.destroy', $arbitro));

        $response->assertRedirect(route('admin.arbitros.index'));
        $this->assertDatabaseMissing('arbitros', ['id' => $arbitro->id]);
    }
}
