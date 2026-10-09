<?php

namespace Database\Seeders;

use App\Enums\TipoEventoPartido;
use App\Models\Partido;
use App\Models\RegistroJugador;
use App\Services\CedulaArbitralService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EventosDemoSeeder extends Seeder
{
    /**
     * Simula la captura real de cédula arbitral para las jornadas 1 y 2 de Libre,
     * usando el mismo servicio y observers que usaría un árbitro en producción
     * (cargos automáticos de arbitraje/multas y suspensiones se generan solos).
     */
    public function run(): void
    {
        $cedula = app(CedulaArbitralService::class);

        $partidos = Partido::whereIn('jornada', [1, 2])->get();

        $huboRoja = false;

        foreach ($partidos as $partido) {
            // Programar el partido dispara el cargo automático de arbitraje para ambos equipos.
            $partido->update([
                'fecha_hora' => Carbon::now()->subWeeks(3 - (int) $partido->jornada),
                'cancha' => 'Cancha 1',
            ]);

            $titularesLocal = RegistroJugador::where('inscripcion_id', $partido->local_inscripcion_id)
                ->inRandomOrder()->limit(11)->get();
            $titularesVisita = RegistroJugador::where('inscripcion_id', $partido->visita_inscripcion_id)
                ->inRandomOrder()->limit(11)->get();

            $golesLocal = random_int(0, 3);
            $golesVisita = random_int(0, 3);

            for ($i = 0; $i < $golesLocal; $i++) {
                $cedula->registrarEvento($partido, $titularesLocal->random(), TipoEventoPartido::Gol, random_int(1, 90));
            }

            for ($i = 0; $i < $golesVisita; $i++) {
                $cedula->registrarEvento($partido, $titularesVisita->random(), TipoEventoPartido::Gol, random_int(1, 90));
            }

            // Una amarilla por partido, alternando de equipo (el observer aplica el cargo automático).
            $registroAmarilla = random_int(0, 1) === 0 ? $titularesLocal->random() : $titularesVisita->random();
            $cedula->registrarEvento($partido, $registroAmarilla, TipoEventoPartido::Amarilla, random_int(1, 90));

            // Una sola roja directa en toda la demo, en el primer partido de la jornada 1
            // (el observer aplica el cargo automático y crea la suspensión).
            if (! $huboRoja && (int) $partido->jornada === 1) {
                $registroRoja = $titularesVisita->random();
                $cedula->registrarEvento($partido, $registroRoja, TipoEventoPartido::Roja, random_int(1, 90));
                $huboRoja = true;
            }

            // Finalizar dispara el cumplimiento de suspensiones para ambos equipos.
            $cedula->finalizar($partido, $golesLocal, $golesVisita);
        }
    }
}
