<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AutorizacionAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_invitado_no_puede_acceder_al_panel_admin(): void
    {
        $response = $this->get('/admin/torneos');

        $response->assertRedirect('/login');
    }

    public function test_un_usuario_sin_rol_admin_no_puede_acceder_al_panel_admin(): void
    {
        Role::findOrCreate('delegado');
        $usuario = User::factory()->create();
        $usuario->assignRole('delegado');

        $response = $this->actingAs($usuario)->get('/admin/torneos');

        $response->assertForbidden();
    }
}
