<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConceptoCobro>
 */
class ConceptoCobroFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'categoria_id' => Categoria::factory(),
            'nombre' => 'Concepto de prueba',
            'monto' => 100,
            'tipo' => 'otro',
            'automatico' => false,
            'evento_disparador' => null,
            'dias_plazo' => 15,
        ];
    }
}
