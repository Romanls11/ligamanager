<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Inscripcion;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

trait AutorizaEquipoDelegado
{
    /**
     * Verifica que la inscripción pertenezca a un equipo del delegado autenticado.
     */
    protected function autorizarInscripcion(Inscripcion $inscripcion): void
    {
        $inscripcion->loadMissing('equipo');

        if ($inscripcion->equipo->delegado_user_id !== Auth::id()) {
            throw new AccessDeniedHttpException('Este equipo no está a tu cargo.');
        }
    }

    /**
     * Inscripciones (equipos, en cualquier categoría) del delegado autenticado.
     */
    protected function inscripcionesDelDelegado()
    {
        return Inscripcion::whereHas('equipo', fn ($query) => $query->where('delegado_user_id', Auth::id()))
            ->with(['equipo', 'categoria.torneo'])
            ->get();
    }
}
