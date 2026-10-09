<?php

namespace Tests\Feature\Admin;

use App\Models\Categoria;
use App\Models\Equipo;
use App\Models\Inscripcion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActuaComoAdmin;
use Tests\TestCase;

class InscripcionCrudTest extends TestCase
{
    use RefreshDatabase;
    use ActuaComoAdmin;

    public function test_inscribir_un_equipo_en_una_categoria(): void
    {
        $this->iniciarSesionComoAdmin();
        $categoria = Categoria::factory()->create();
        $equipo = Equipo::factory()->create();

        $response = $this->post(route('admin.inscripciones.store'), [
            'categoria_id' => $categoria->id,
            'equipo_id' => $equipo->id,
            'estado' => 'activa',
        ]);

        $response->assertRedirect(route('admin.inscripciones.index'));
        $this->assertDatabaseHas('inscripciones', ['categoria_id' => $categoria->id, 'equipo_id' => $equipo->id]);
    }

    public function test_un_equipo_no_puede_inscribirse_dos_veces_en_la_misma_categoria(): void
    {
        $this->iniciarSesionComoAdmin();
        $categoria = Categoria::factory()->create();
        $equipo = Equipo::factory()->create();
        Inscripcion::factory()->create(['categoria_id' => $categoria->id, 'equipo_id' => $equipo->id]);

        $response = $this->post(route('admin.inscripciones.store'), [
            'categoria_id' => $categoria->id,
            'equipo_id' => $equipo->id,
            'estado' => 'activa',
        ]);

        $response->assertSessionHasErrors('equipo_id');
    }

    public function test_el_mismo_equipo_si_puede_inscribirse_en_dos_categorias_distintas(): void
    {
        $this->iniciarSesionComoAdmin();
        $equipo = Equipo::factory()->create();
        $categoriaA = Categoria::factory()->create();
        $categoriaB = Categoria::factory()->create();
        Inscripcion::factory()->create(['categoria_id' => $categoriaA->id, 'equipo_id' => $equipo->id]);

        $response = $this->post(route('admin.inscripciones.store'), [
            'categoria_id' => $categoriaB->id,
            'equipo_id' => $equipo->id,
            'estado' => 'activa',
        ]);

        $response->assertRedirect(route('admin.inscripciones.index'));
        $this->assertDatabaseHas('inscripciones', ['categoria_id' => $categoriaB->id, 'equipo_id' => $equipo->id]);
    }

    public function test_eliminar_una_inscripcion(): void
    {
        $this->iniciarSesionComoAdmin();
        $inscripcion = Inscripcion::factory()->create();

        $response = $this->delete(route('admin.inscripciones.destroy', $inscripcion));

        $response->assertRedirect(route('admin.inscripciones.index'));
        $this->assertDatabaseMissing('inscripciones', ['id' => $inscripcion->id]);
    }
}
