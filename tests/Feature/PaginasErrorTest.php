<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PaginasErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_una_ruta_inexistente_muestra_la_pagina_404_personalizada(): void
    {
        $response = $this->get('/esta-ruta-no-existe');

        $response->assertStatus(404);
        $response->assertSee('Página no encontrada');
    }

    public function test_un_error_500_en_produccion_muestra_la_pagina_personalizada(): void
    {
        config(['app.debug' => false]);

        Route::get('/ruta-de-prueba-500', function () {
            throw new \RuntimeException('Fallo forzado para la prueba.');
        });

        $response = $this->get('/ruta-de-prueba-500');

        $response->assertStatus(500);
        $response->assertSee('Ocurrió un error inesperado');
        $response->assertDontSee('Fallo forzado para la prueba.');
    }
}
