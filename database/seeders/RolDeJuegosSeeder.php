<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Services\RolDeJuegosService;
use Illuminate\Database\Seeder;

class RolDeJuegosSeeder extends Seeder
{
    public function run(): void
    {
        $categoriaLibre = Categoria::where('nombre', 'Libre')->firstOrFail();

        app(RolDeJuegosService::class)->generar($categoriaLibre);
    }
}
