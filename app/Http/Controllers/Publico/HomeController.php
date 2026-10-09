<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Torneo;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $torneos = Torneo::with('categorias')
            ->orderByDesc('fecha_inicio')
            ->get();

        return view('publico.home', compact('torneos'));
    }
}
