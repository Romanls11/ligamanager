<?php

namespace Database\Seeders;

use App\Models\Arbitro;
use Illuminate\Database\Seeder;

class ArbitroSeeder extends Seeder
{
    public function run(): void
    {
        Arbitro::factory()->count(3)->create();
    }
}
