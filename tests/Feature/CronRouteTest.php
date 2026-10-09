<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CronRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_token_correcto_dispara_el_scheduler(): void
    {
        config(['app.cron_token' => 'un-token-secreto']);

        $response = $this->get('/cron/un-token-secreto');

        $response->assertOk();
        $response->assertSeeText('OK');
    }

    public function test_un_token_incorrecto_responde_404(): void
    {
        config(['app.cron_token' => 'un-token-secreto']);

        $response = $this->get('/cron/token-equivocado');

        $response->assertNotFound();
    }

    public function test_sin_cron_token_configurado_la_ruta_responde_404(): void
    {
        config(['app.cron_token' => null]);

        $response = $this->get('/cron/cualquier-cosa');

        $response->assertNotFound();
    }
}
