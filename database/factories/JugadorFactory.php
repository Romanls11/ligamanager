<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Jugador>
 */
class JugadorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre_completo' => $this->faker->name('male'),
            'fecha_nacimiento' => $this->faker->dateTimeBetween('-45 years', '-16 years'),
            'foto' => null,
            'curp' => strtoupper($this->faker->bothify('????######???##?')),
            'telefono' => $this->faker->numerify('55########'),
        ];
    }
}
