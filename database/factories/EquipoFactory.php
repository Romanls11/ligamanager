<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipo>
 */
class EquipoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $nombres = [
            'Deportivo Águilas', 'Real Halcones', 'Atlético Norte', 'Club Tigres FC',
            'Unión Deportiva Sur', 'Rayos del Valle', 'Leones FC', 'Independiente Central',
            'Club Cóndores', 'Deportivo Rivera', 'Atlético Fénix', 'Lobos Bravos FC',
        ];

        return [
            'nombre' => $this->faker->unique()->randomElement($nombres),
            'logo' => null,
            'color_uniforme' => $this->faker->safeColorName(),
            'delegado_user_id' => null,
            'telefono_contacto' => $this->faker->numerify('55########'),
        ];
    }
}
