<?php

namespace App\Services;

use App\Enums\EstadoInscripcion;
use App\Enums\EstadoRegistroJugador;
use App\Models\Inscripcion;
use App\Models\Jugador;
use App\Models\RegistroJugador;
use RuntimeException;

class AltaJugadorService
{
    /**
     * Da de alta a un jugador en la plantilla de una inscripción, validando las
     * reglas acordadas: cierre de registros, cupo máximo, y que el jugador no
     * tenga ya un registro activo en otra plantilla del sistema.
     */
    public function registrar(Inscripcion $inscripcion, Jugador $jugador, ?int $numeroCamiseta = null): RegistroJugador
    {
        $this->validar($inscripcion, $jugador->id);

        return RegistroJugador::create([
            'inscripcion_id' => $inscripcion->id,
            'jugador_id' => $jugador->id,
            'numero_camiseta' => $numeroCamiseta,
            'amarillas_acumuladas' => 0,
            'jornadas_sin_tarjeta' => 0,
            'estado' => EstadoRegistroJugador::Activo,
        ]);
    }

    /**
     * Reactiva un registro que estaba de baja, aplicando las mismas validaciones que un alta nueva.
     */
    public function reactivar(RegistroJugador $registro): void
    {
        $this->validar($registro->inscripcion, $registro->jugador_id, ignorarRegistroId: $registro->id);

        $registro->update(['estado' => EstadoRegistroJugador::Activo]);
    }

    public function validar(Inscripcion $inscripcion, int $jugadorId, ?int $ignorarRegistroId = null): void
    {
        $inscripcion->loadMissing('categoria');
        $categoria = $inscripcion->categoria;

        if ($inscripcion->estado === EstadoInscripcion::SuspendidaPorAdeudo) {
            throw new RuntimeException('Este equipo está suspendido por adeudo vencido. No se pueden registrar altas hasta regularizar su situación.');
        }

        if ($categoria->fecha_cierre_registros && now()->toDateString() > $categoria->fecha_cierre_registros->toDateString()) {
            throw new RuntimeException('Ya pasó la fecha de cierre de registros de esta categoría ('.$categoria->fecha_cierre_registros->format('d/m/Y').').');
        }

        $totalActivos = $inscripcion->registrosJugador()
            ->where('estado', EstadoRegistroJugador::Activo)
            ->when($ignorarRegistroId, fn ($query) => $query->where('id', '!=', $ignorarRegistroId))
            ->count();

        if ($totalActivos >= $categoria->max_jugadores) {
            throw new RuntimeException('Esta plantilla ya alcanzó el máximo de '.$categoria->max_jugadores.' jugadores permitidos.');
        }

        $yaActivoEnOtroLado = RegistroJugador::where('jugador_id', $jugadorId)
            ->where('estado', EstadoRegistroJugador::Activo)
            ->when($ignorarRegistroId, fn ($query) => $query->where('id', '!=', $ignorarRegistroId))
            ->exists();

        if ($yaActivoEnOtroLado) {
            throw new RuntimeException('Este jugador ya tiene un registro activo en otra plantilla. No puede jugar en dos equipos a la vez.');
        }
    }
}
