<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\GestionaCedulaArbitral;
use App\Http\Controllers\Controller;

class CedulaController extends Controller
{
    use GestionaCedulaArbitral;

    protected function prefijoRutas(): string
    {
        return 'admin';
    }
}
