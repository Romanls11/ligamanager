<?php

namespace App\Http\Controllers\Arbitro;

use App\Http\Controllers\Concerns\GestionaCedulaArbitral;
use App\Http\Controllers\Controller;
use App\Models\Partido;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CedulaController extends Controller
{
    use GestionaCedulaArbitral;

    protected function prefijoRutas(): string
    {
        return 'arbitro';
    }

    protected function autorizarPartido(Partido $partido): void
    {
        $arbitro = Auth::user()->arbitro;

        $asignado = $arbitro && $partido->asignacionesArbitro()->where('arbitro_id', $arbitro->id)->exists();

        if (! $asignado) {
            throw new AccessDeniedHttpException('No tienes una asignación para este partido.');
        }
    }
}
