<?php

namespace Database\Factories;

use App\Models\Torneo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categoria>
 */
class CategoriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'torneo_id' => Torneo::factory(),
            'nombre' => $this->faker->randomElement(['Libre', 'Veteranos', 'Femenil']),
            'min_jugadores' => 16,
            'max_jugadores' => 40,
            'amarillas_para_suspension' => 5,
            'partidos_castigo_roja' => 1,
            'jornadas_amnistia' => null,
            'fecha_cierre_registros' => $this->faker->dateTimeBetween('+2 weeks', '+2 months'),
            'vueltas' => 2,
            'equipos_a_liguilla' => 8,
        ];
    }
}
