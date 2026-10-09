<?php

namespace Tests\Feature\Admin;

use App\Models\Categoria;
use App\Models\Torneo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActuaComoAdmin;
use Tests\TestCase;

class CategoriaCrudTest extends TestCase
{
    use RefreshDatabase;
    use ActuaComoAdmin;

    private function datosValidos(Torneo $torneo, array $overrides = []): array
    {
        return array_merge([
            'torneo_id' => $torneo->id,
            'nombre' => 'Libre',
            'min_jugadores' => 16,
            'max_jugadores' => 30,
            'amarillas_para_suspension' => 5,
            'partidos_castigo_roja' => 1,
            'jornadas_amnistia' => 3,
            'fecha_cierre_registros' => now()->addMonth()->toDateString(),
            'vueltas' => 2,
            'equipos_a_liguilla' => 8,
        ], $overrides);
    }

    public function test_crear_una_categoria(): void
    {
        $this->iniciarSesionComoAdmin();
        $torneo = Torneo::factory()->create();

        $response = $this->post(route('admin.categorias.store'), $this->datosValidos($torneo));

        $response->assertRedirect(route('admin.categorias.index'));
        $this->assertDatabaseHas('categorias', ['nombre' => 'Libre', 'torneo_id' => $torneo->id]);
    }

    public function test_max_jugadores_no_puede_ser_menor_que_min_jugadores(): void
    {
        $this->iniciarSesionComoAdmin();
        $torneo = Torneo::factory()->create();

        $response = $this->post(route('admin.categorias.store'), $this->datosValidos($torneo, [
            'min_jugadores' => 20,
            'max_jugadores' => 10,
        ]));

        $response->assertSessionHasErrors('max_jugadores');
    }

    public function test_equipos_a_liguilla_solo_acepta_potencias_validas(): void
    {
        $this->iniciarSesionComoAdmin();
        $torneo = Torneo::factory()->create();

        $response = $this->post(route('admin.categorias.store'), $this->datosValidos($torneo, [
            'equipos_a_liguilla' => 6,
        ]));

        $response->assertSessionHasErrors('equipos_a_liguilla');
    }

    public function test_se_puede_actualizar_una_categoria_sin_jornadas_de_amnistia(): void
    {
        // Regresión: la columna es nullable en BD (categorías sin política de amnistía),
        // pero la validación exigía el campo y bloqueaba guardar el formulario.
        $this->iniciarSesionComoAdmin();
        $categoria = Categoria::factory()->create(['jornadas_amnistia' => null]);

        $response = $this->put(route('admin.categorias.update', $categoria), $this->datosValidos($categoria->torneo, [
            'torneo_id' => $categoria->torneo_id,
            'nombre' => $categoria->nombre,
            'jornadas_amnistia' => '',
        ]));

        $response->assertSessionHasNoErrors();
        $this->assertNull($categoria->fresh()->jornadas_amnistia);
    }

    public function test_eliminar_una_categoria(): void
    {
        $this->iniciarSesionComoAdmin();
        $categoria = Categoria::factory()->create();

        $response = $this->delete(route('admin.categorias.destroy', $categoria));

        $response->assertRedirect(route('admin.categorias.index'));
        $this->assertDatabaseMissing('categorias', ['id' => $categoria->id]);
    }
}
