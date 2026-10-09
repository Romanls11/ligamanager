<?php

namespace App\Services;

use App\Enums\EstadoSolicitudAlta;
use App\Models\Inscripcion;
use App\Models\Jugador;
use App\Models\SolicitudAlta;
use App\Models\User;
use RuntimeException;

class SolicitudAltaService
{
    public function __construct(private readonly AltaJugadorService $altaJugador) {}

    /**
     * El delegado propone dar de alta a un jugador. No valida cupo/cierre aquí:
     * esas reglas se evalúan hasta la aprobación, cuando de verdad se registra.
     */
    public function solicitar(Inscripcion $inscripcion, Jugador $jugador, ?int $numeroCamiseta, User $solicitante): SolicitudAlta
    {
        $yaPendiente = SolicitudAlta::where('inscripcion_id', $inscripcion->id)
            ->where('jugador_id', $jugador->id)
            ->where('estado', EstadoSolicitudAlta::Pendiente)
            ->exists();

        if ($yaPendiente) {
            throw new RuntimeException('Ya existe una solicitud pendiente para este jugador en esta plantilla.');
        }

        return SolicitudAlta::create([
            'inscripcion_id' => $inscripcion->id,
            'jugador_id' => $jugador->id,
            'numero_camiseta' => $numeroCamiseta,
            'estado' => EstadoSolicitudAlta::Pendiente,
            'solicitado_por_user_id' => $solicitante->id,
        ]);
    }

    /**
     * Aprueba la solicitud y da de alta al jugador aplicando las mismas reglas
     * que una alta directa del admin. Si la validación falla (por ejemplo, cupo
     * lleno o el jugador ya quedó activo en otro lado mientras tanto), la
     * solicitud se queda en pendiente para que el admin decida qué hacer.
     */
    public function aprobar(SolicitudAlta $solicitud, User $revisor): void
    {
        if ($solicitud->estado !== EstadoSolicitudAlta::Pendiente) {
            throw new RuntimeException('Esta solicitud ya fue revisada.');
        }

        $this->altaJugador->registrar($solicitud->inscripcion, $solicitud->jugador, $solicitud->numero_camiseta);

        $solicitud->update([
            'estado' => EstadoSolicitudAlta::Aprobada,
            'revisado_por_user_id' => $revisor->id,
            'revisado_at' => now(),
        ]);
    }

    public function rechazar(SolicitudAlta $solicitud, User $revisor, ?string $motivo): void
    {
        if ($solicitud->estado !== EstadoSolicitudAlta::Pendiente) {
            throw new RuntimeException('Esta solicitud ya fue revisada.');
        }

        $solicitud->update([
            'estado' => EstadoSolicitudAlta::Rechazada,
            'motivo_rechazo' => $motivo,
            'revisado_por_user_id' => $revisor->id,
            'revisado_at' => now(),
        ]);
    }
}
