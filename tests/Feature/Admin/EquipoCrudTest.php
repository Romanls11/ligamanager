<?php

namespace Tests\Feature\Admin;

use App\Models\Equipo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\Concerns\ActuaComoAdmin;
use Tests\TestCase;

class EquipoCrudTest extends TestCase
{
    use RefreshDatabase;
    use ActuaComoAdmin;

    public function test_crear_un_equipo_con_logo(): void
    {
        Storage::fake('public');
        $this->iniciarSesionComoAdmin();

        $response = $this->post(route('admin.equipos.store'), [
            'nombre' => 'Halcones FC',
            'logo' => UploadedFile::fake()->image('logo.png'),
            'color_uniforme' => 'Rojo',
        ]);

        $response->assertRedirect(route('admin.equipos.index'));
        $equipo = Equipo::where('nombre', 'Halcones FC')->firstOrFail();
        Storage::disk('public')->assertExists($equipo->logo);
    }

    public function test_solo_se_listan_como_delegado_usuarios_con_ese_rol(): void
    {
        Role::findOrCreate('delegado');
        Role::findOrCreate('arbitro');
        $this->iniciarSesionComoAdmin();

        $delegado = User::factory()->create(['name' => 'Es Delegado']);
        $delegado->assignRole('delegado');
        $arbitro = User::factory()->create(['name' => 'Es Arbitro']);
        $arbitro->assignRole('arbitro');

        $response = $this->get(route('admin.equipos.create'));

        $response->assertSee('Es Delegado');
        $response->assertDontSee('Es Arbitro');
    }

    public function test_actualizar_el_logo_borra_el_anterior(): void
    {
        Storage::fake('public');
        $this->iniciarSesionComoAdmin();
        $equipo = Equipo::factory()->create(['logo' => 'equipos/viejo.png']);
        Storage::disk('public')->put('equipos/viejo.png', 'contenido');

        $response = $this->put(route('admin.equipos.update', $equipo), [
            'nombre' => $equipo->nombre,
            'logo' => UploadedFile::fake()->image('nuevo.png'),
        ]);

        $response->assertRedirect(route('admin.equipos.index'));
        Storage::disk('public')->assertMissing('equipos/viejo.png');
        Storage::disk('public')->assertExists($equipo->fresh()->logo);
    }

    public function test_eliminar_un_equipo_borra_su_logo(): void
    {
        Storage::fake('public');
        $this->iniciarSesionComoAdmin();
        $equipo = Equipo::factory()->create(['logo' => 'equipos/foto.png']);
        Storage::disk('public')->put('equipos/foto.png', 'contenido');

        $response = $this->delete(route('admin.equipos.destroy', $equipo));

        $response->assertRedirect(route('admin.equipos.index'));
        Storage::disk('public')->assertMissing('equipos/foto.png');
    }
}
