<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Equipo;
use App\Models\Inscripcion;
use App\Models\Jugador;
use App\Models\RegistroJugador;
use Illuminate\Database\Seeder;

class EquipoSeeder extends Seeder
{
    public function run(): void
    {
        $categoriaLibre = Categoria::where('nombre', 'Libre')->firstOrFail();

        $equipos = Equipo::factory()->count(8)->create();

        foreach ($equipos as $equipo) {
            $inscripcion = Inscripcion::create([
                'categoria_id' => $categoriaLibre->id,
                'equipo_id' => $equipo->id,
                'estado' => 'activa',
            ]);

            for ($numero = 1; $numero <= 18; $numero++) {
                $jugador = Jugador::factory()->create();

                RegistroJugador::create([
                    'inscripcion_id' => $inscripcion->id,
                    'jugador_id' => $jugador->id,
                    'numero_camiseta' => $numero,
                    'amarillas_acumuladas' => 0,
                    'jornadas_sin_tarjeta' => 0,
                    'estado' => 'activo',
                ]);
            }
        }
    }
}
