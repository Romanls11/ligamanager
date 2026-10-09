<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// El hosting no tiene workers de cola persistentes: este cron diario es lo único
// que necesita configurarse en el servidor (php artisan schedule:run cada minuto).
Schedule::command('liga:actualizar-bloqueos')->daily();
