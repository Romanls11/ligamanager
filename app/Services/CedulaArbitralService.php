<?php

namespace App\Services;

use App\Enums\EstadoPartido;
use App\Enums\EstadoRegistroJugador;
use App\Enums\TipoEventoPartido;
use App\Models\EventoPartido;
use App\Models\Partido;
use App\Models\RegistroJugador;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CedulaArbitralService
{
    /**
     * Registra un evento (gol, autogol, amarilla o roja) de un jugador en el partido.
     */
    public function registrarEvento(Partido $partido, RegistroJugador $registro, TipoEventoPartido $tipo, ?int $minuto): EventoPartido
    {
        $this->validarPartidoAbierto($partido);
        $this->validarJugadorPerteneceAlPartido($partido, $registro);

        return EventoPartido::create([
            'partido_id' => $partido->id,
            'registro_jugador_id' => $registro->id,
            'tipo' => $tipo,
            'minuto' => $minuto,
        ]);
    }

    /**
     * Elimina un evento capturado por error. El observer valida que no tenga
     * cargos/suspensiones ya en curso antes de permitir la eliminación.
     */
    public function eliminarEvento(EventoPartido $evento): void
    {
        $evento->delete();
    }

    /**
     * Cierra el partido con marcador final.
     */
    public function finalizar(Partido $partido, int $golesLocal, int $golesVisita): void
    {
        $this->validarPartidoAbierto($partido);

        $partido->update([
            'goles_local' => $golesLocal,
            'goles_visita' => $golesVisita,
            'estado' => EstadoPartido::Jugado,
        ]);
    }

    /**
     * Marca el partido como default de uno de los dos equipos.
     * El marcador y el cargo automático los aplica PartidoObserver.
     */
    public function marcarDefault(Partido $partido, string $equipoFalta): void
    {
        $this->validarPartidoAbierto($partido);

        if (! in_array($equipoFalta, ['local', 'visita'], true)) {
            throw new RuntimeException('Equipo inválido para marcar default.');
        }

        $partido->update([
            'estado' => $equipoFalta === 'local' ? EstadoPartido::DefaultLocal : EstadoPartido::DefaultVisita,
        ]);
    }

    private function validarPartidoAbierto(Partido $partido): void
    {
        if ($partido->estado !== EstadoPartido::Programado) {
            throw new RuntimeException('Este partido ya fue cerrado ('.$partido->estado->value.'). No se puede modificar la cédula.');
        }
    }

    private function validarJugadorPerteneceAlPartido(Partido $partido, RegistroJugador $registro): void
    {
        if (! in_array($registro->inscripcion_id, [$partido->local_inscripcion_id, $partido->visita_inscripcion_id], true)) {
            throw new RuntimeException('El jugador no pertenece a ninguno de los dos equipos de este partido.');
        }

        if ($registro->estado !== EstadoRegistroJugador::Activo) {
            throw new RuntimeException('El jugador no tiene un registro activo en la plantilla.');
        }
    }
}
