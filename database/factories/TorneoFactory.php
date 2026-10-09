<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Torneo>
 */
class TorneoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Apertura '.$this->faker->year(),
            'fecha_inicio' => $this->faker->dateTimeBetween('-2 months', '+1 month'),
            'estado' => 'activo',
        ];
    }
}
