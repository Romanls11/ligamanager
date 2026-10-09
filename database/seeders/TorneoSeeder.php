<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Torneo;
use Illuminate\Database\Seeder;

class TorneoSeeder extends Seeder
{
    public function run(): void
    {
        $torneo = Torneo::create([
            'nombre' => 'Apertura 2026',
            'fecha_inicio' => now()->subWeeks(3),
            'estado' => 'activo',
        ]);

        Categoria::create([
            'torneo_id' => $torneo->id,
            'nombre' => 'Libre',
            'min_jugadores' => 16,
            'max_jugadores' => 40,
            'amarillas_para_suspension' => 5,
            'partidos_castigo_roja' => 1,
            'jornadas_amnistia' => 3,
            'fecha_cierre_registros' => now()->addMonths(2),
            'vueltas' => 2,
            'equipos_a_liguilla' => 8,
        ]);

        Categoria::create([
            'torneo_id' => $torneo->id,
            'nombre' => 'Veteranos',
            'min_jugadores' => 16,
            'max_jugadores' => 30,
            'amarillas_para_suspension' => 5,
            'partidos_castigo_roja' => 1,
            'jornadas_amnistia' => null,
            'fecha_cierre_registros' => now()->addMonths(2),
            'vueltas' => 1,
            'equipos_a_liguilla' => 4,
        ]);
    }
}
