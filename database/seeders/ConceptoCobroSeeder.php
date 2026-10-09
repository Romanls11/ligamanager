<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\ConceptoCobro;
use Illuminate\Database\Seeder;

class ConceptoCobroSeeder extends Seeder
{
    public function run(): void
    {
        $categoriaLibre = Categoria::where('nombre', 'Libre')->firstOrFail();

        $conceptos = [
            [
                'nombre' => 'Inscripción',
                'monto' => 1500,
                'tipo' => 'inscripcion',
                'automatico' => false,
                'evento_disparador' => null,
                'dias_plazo' => 30,
            ],
            [
                'nombre' => 'Arbitraje por partido',
                'monto' => 200,
                'tipo' => 'arbitraje',
                'automatico' => true,
                'evento_disparador' => 'partido_programado',
                'dias_plazo' => 7,
            ],
            [
                'nombre' => 'Multa amarilla',
                'monto' => 30,
                'tipo' => 'multa',
                'automatico' => true,
                'evento_disparador' => 'tarjeta_amarilla',
                'dias_plazo' => 15,
            ],
            [
                'nombre' => 'Multa roja',
                'monto' => 100,
                'tipo' => 'multa',
                'automatico' => true,
                'evento_disparador' => 'tarjeta_roja',
                'dias_plazo' => 15,
            ],
            [
                'nombre' => 'Multa por default',
                'monto' => 500,
                'tipo' => 'multa',
                'automatico' => true,
                'evento_disparador' => 'default_partido',
                'dias_plazo' => 15,
            ],
        ];

        foreach ($conceptos as $concepto) {
            ConceptoCobro::create($concepto + ['categoria_id' => $categoriaLibre->id]);
        }
    }
}
