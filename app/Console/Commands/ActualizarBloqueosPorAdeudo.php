<?php

namespace App\Console\Commands;

use App\Services\BloqueoAdeudoService;
use Illuminate\Console\Command;

class ActualizarBloqueosPorAdeudo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'liga:actualizar-bloqueos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Suspende por adeudo vencido (o reactiva) las inscripciones según los cargos pendientes. Pensado para correrse a diario vía cron.';

    public function handle(BloqueoAdeudoService $service): int
    {
        $cambios = $service->evaluarTodas();

        $this->info("Inscripciones actualizadas: {$cambios}.");

        return self::SUCCESS;
    }
}
