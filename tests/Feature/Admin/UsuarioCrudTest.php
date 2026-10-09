<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Concerns\ActuaComoAdmin;
use Tests\TestCase;

class UsuarioCrudTest extends TestCase
{
    use RefreshDatabase;
    use ActuaComoAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'delegado', 'arbitro'] as $rol) {
            Role::findOrCreate($rol);
        }
    }

    public function test_crear_un_usuario_le_asigna_el_rol_seleccionado(): void
    {
        $this->iniciarSesionComoAdmin();

        $response = $this->post(route('admin.usuarios.store'), [
            'name' => 'Nuevo Delegado',
            'email' => 'nuevo.delegado@liga.test',
            'password' => 'password123',
            'rol' => 'delegado',
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $usuario = User::where('email', 'nuevo.delegado@liga.test')->firstOrFail();
        $this->assertTrue($usuario->hasRole('delegado'));
    }

    public function test_no_se_puede_crear_un_usuario_con_correo_duplicado(): void
    {
        $this->iniciarSesionComoAdmin();
        User::factory()->create(['email' => 'existente@liga.test']);

        $response = $this->post(route('admin.usuarios.store'), [
            'name' => 'Otro',
            'email' => 'existente@liga.test',
            'password' => 'password123',
            'rol' => 'delegado',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_actualizar_un_usuario_sin_password_no_cambia_su_contrasena(): void
    {
        $this->iniciarSesionComoAdmin();
        $usuario = User::factory()->create(['password' => 'original-password']);
        $usuario->assignRole('arbitro');
        $hashOriginal = $usuario->password;

        $response = $this->put(route('admin.usuarios.update', $usuario), [
            'name' => 'Nombre Actualizado',
            'email' => $usuario->email,
            'password' => '',
            'rol' => 'arbitro',
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $this->assertSame($hashOriginal, $usuario->fresh()->password);
        $this->assertSame('Nombre Actualizado', $usuario->fresh()->name);
    }

    public function test_un_admin_no_puede_eliminar_su_propia_cuenta(): void
    {
        $admin = $this->iniciarSesionComoAdmin();

        $response = $this->delete(route('admin.usuarios.destroy', $admin));

        $response->assertSessionHasErrors('usuario');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_se_puede_eliminar_a_otro_usuario(): void
    {
        $this->iniciarSesionComoAdmin();
        $otro = User::factory()->create();

        $response = $this->delete(route('admin.usuarios.destroy', $otro));

        $response->assertRedirect(route('admin.usuarios.index'));
        $this->assertDatabaseMissing('users', ['id' => $otro->id]);
    }
}
