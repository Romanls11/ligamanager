<?php

namespace App\Services;

use App\Enums\EstadoInscripcion;
use App\Models\Inscripcion;

class BloqueoAdeudoService
{
    /**
     * Evalúa una sola inscripción: la suspende si tiene adeudo vencido, o la
     * reactiva si ya no tiene ninguno. No toca inscripciones retiradas.
     */
    public function evaluar(Inscripcion $inscripcion): void
    {
        if (! config('liga.bloqueo_por_adeudo')) {
            return;
        }

        if (! in_array($inscripcion->estado, [EstadoInscripcion::Activa, EstadoInscripcion::SuspendidaPorAdeudo], true)) {
            return;
        }

        $tieneAdeudoVencido = $inscripcion->cargos()
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', now()->toDateString())
            ->exists();

        if ($tieneAdeudoVencido && $inscripcion->estado === EstadoInscripcion::Activa) {
            $inscripcion->update(['estado' => EstadoInscripcion::SuspendidaPorAdeudo]);
        } elseif (! $tieneAdeudoVencido && $inscripcion->estado === EstadoInscripcion::SuspendidaPorAdeudo) {
            $inscripcion->update(['estado' => EstadoInscripcion::Activa]);
        }
    }

    /**
     * Evalúa todas las inscripciones activas/suspendidas por adeudo. Pensado para
     * correrse a diario vía cron (el hosting no tiene workers de cola persistentes).
     */
    public function evaluarTodas(): int
    {
        $cambios = 0;

        Inscripcion::whereIn('estado', [EstadoInscripcion::Activa, EstadoInscripcion::SuspendidaPorAdeudo])
            ->get()
            ->each(function (Inscripcion $inscripcion) use (&$cambios) {
                $estadoAnterior = $inscripcion->estado;
                $this->evaluar($inscripcion);

                if ($inscripcion->estado !== $estadoAnterior) {
                    $cambios++;
                }
            });

        return $cambios;
    }
}
