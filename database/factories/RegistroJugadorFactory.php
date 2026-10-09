<?php

namespace Database\Factories;

use App\Models\Inscripcion;
use App\Models\Jugador;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RegistroJugador>
 */
class RegistroJugadorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inscripcion_id' => Inscripcion::factory(),
            'jugador_id' => Jugador::factory(),
            'numero_camiseta' => $this->faker->unique()->numberBetween(1, 99),
            'amarillas_acumuladas' => 0,
            'jornadas_sin_tarjeta' => 0,
            'estado' => 'activo',
        ];
    }
}
