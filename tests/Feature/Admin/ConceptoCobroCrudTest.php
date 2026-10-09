<?php

namespace Tests\Feature\Admin;

use App\Models\ConceptoCobro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActuaComoAdmin;
use Tests\TestCase;

class ConceptoCobroCrudTest extends TestCase
{
    use RefreshDatabase;
    use ActuaComoAdmin;

    public function test_crear_un_concepto_automatico_con_evento_disparador(): void
    {
        $this->iniciarSesionComoAdmin();

        $response = $this->post(route('admin.conceptos-cobro.store'), [
            'nombre' => 'Multa amarilla',
            'monto' => 30,
            'tipo' => 'multa',
            'automatico' => '1',
            'evento_disparador' => 'tarjeta_amarilla',
        ]);

        $response->assertRedirect(route('admin.conceptos-cobro.index'));
        $this->assertDatabaseHas('conceptos_cobro', [
            'nombre' => 'Multa amarilla',
            'automatico' => true,
            'evento_disparador' => 'tarjeta_amarilla',
        ]);
    }

    public function test_crear_un_concepto_manual_sin_marcar_automatico_queda_en_false(): void
    {
        $this->iniciarSesionComoAdmin();

        $response = $this->post(route('admin.conceptos-cobro.store'), [
            'nombre' => 'Inscripción',
            'monto' => 1500,
            'tipo' => 'inscripcion',
        ]);

        $response->assertRedirect(route('admin.conceptos-cobro.index'));
        $this->assertDatabaseHas('conceptos_cobro', ['nombre' => 'Inscripción', 'automatico' => false]);
    }

    public function test_no_se_puede_usar_un_tipo_invalido(): void
    {
        $this->iniciarSesionComoAdmin();

        $response = $this->post(route('admin.conceptos-cobro.store'), [
            'nombre' => 'Concepto inválido',
            'monto' => 100,
            'tipo' => 'no-existe',
        ]);

        $response->assertSessionHasErrors('tipo');
    }

    public function test_eliminar_un_concepto(): void
    {
        $this->iniciarSesionComoAdmin();
        $concepto = ConceptoCobro::factory()->create();

        $response = $this->delete(route('admin.conceptos-cobro.destroy', $concepto));

        $response->assertRedirect(route('admin.conceptos-cobro.index'));
        $this->assertDatabaseMissing('conceptos_cobro', ['id' => $concepto->id]);
    }
}
