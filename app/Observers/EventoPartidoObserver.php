<?php

namespace App\Observers;

use App\Enums\EstadoCargo;
use App\Enums\EventoDisparador;
use App\Enums\MotivoSuspension;
use App\Enums\TipoEventoPartido;
use App\Models\Cargo;
use App\Models\Categoria;
use App\Models\ConceptoCobro;
use App\Models\EventoPartido;
use App\Models\Suspension;
use RuntimeException;

class EventoPartidoObserver
{
    public function created(EventoPartido $evento): void
    {
        if ($evento->tipo === TipoEventoPartido::Amarilla) {
            $this->procesarAmarilla($evento);
        } elseif ($evento->tipo === TipoEventoPartido::Roja) {
            $this->procesarRoja($evento);
        }
    }

    /**
     * Valida que el evento se pueda eliminar de forma segura y revierte sus
     * efectos (cargo, suspensión, contador de amarillas) antes de borrarlo.
     */
    public function deleting(EventoPartido $evento): void
    {
        $cargo = $evento->cargo;
        $suspension = $evento->suspension;

        if ($cargo && $cargo->estado !== EstadoCargo::Pendiente) {
            throw new RuntimeException('No se puede eliminar: el cargo generado por este evento ya fue pagado o abonado.');
        }

        if ($suspension && ($suspension->cumplida || $suspension->partidos_cumplidos > 0)) {
            throw new RuntimeException('No se puede eliminar: la suspensión generada por este evento ya está en curso o cumplida.');
        }

        if ($evento->tipo === TipoEventoPartido::Amarilla) {
            $esLaMasReciente = ! EventoPartido::where('registro_jugador_id', $evento->registro_jugador_id)
                ->where('tipo', TipoEventoPartido::Amarilla)
                ->where('id', '>', $evento->id)
                ->exists();

            if (! $esLaMasReciente) {
                throw new RuntimeException('Solo se puede eliminar la tarjeta amarilla más reciente de este jugador.');
            }

            $registro = $evento->registroJugador;

            if ($suspension) {
                $categoria = $registro->inscripcion->categoria;
                $registro->increment('amarillas_acumuladas', $categoria->amarillas_para_suspension);
                $suspension->delete();
            }

            $registro->decrement('amarillas_acumuladas');
        } elseif ($evento->tipo === TipoEventoPartido::Roja) {
            $suspension?->delete();
        }

        $cargo?->delete();
    }

    private function procesarAmarilla(EventoPartido $evento): void
    {
        $registro = $evento->registroJugador;
        $categoria = $registro->inscripcion->categoria;

        $registro->increment('amarillas_acumuladas');

        $this->crearCargoAutomatico($evento, $categoria, EventoDisparador::TarjetaAmarilla);

        $registro->refresh();

        if ($registro->amarillas_acumuladas >= $categoria->amarillas_para_suspension) {
            Suspension::create([
                'registro_jugador_id' => $registro->id,
                'motivo' => MotivoSuspension::AcumulacionAmarillas,
                'partidos_castigo' => 1,
                'partido_origen_id' => $evento->partido_id,
                'evento_partido_id' => $evento->id,
            ]);

            $registro->decrement('amarillas_acumuladas', $categoria->amarillas_para_suspension);
        }
    }

    private function procesarRoja(EventoPartido $evento): void
    {
        $registro = $evento->registroJugador;
        $categoria = $registro->inscripcion->categoria;

        $this->crearCargoAutomatico($evento, $categoria, EventoDisparador::TarjetaRoja);

        Suspension::create([
            'registro_jugador_id' => $registro->id,
            'motivo' => MotivoSuspension::RojaDirecta,
            'partidos_castigo' => $categoria->partidos_castigo_roja,
            'partido_origen_id' => $evento->partido_id,
            'evento_partido_id' => $evento->id,
        ]);
    }

    private function crearCargoAutomatico(EventoPartido $evento, Categoria $categoria, EventoDisparador $disparador): void
    {
        $concepto = $this->buscarConcepto($categoria->id, $disparador);

        if (! $concepto) {
            return;
        }

        $registro = $evento->registroJugador;

        Cargo::create([
            'inscripcion_id' => $registro->inscripcion_id,
            'concepto_cobro_id' => $concepto->id,
            'partido_id' => $evento->partido_id,
            'evento_partido_id' => $evento->id,
            'monto' => $concepto->monto,
            'descripcion' => $concepto->nombre.' - jornada '.$evento->partido->jornada,
            'estado' => EstadoCargo::Pendiente,
            'fecha_vencimiento' => $concepto->dias_plazo ? now()->addDays($concepto->dias_plazo) : null,
        ]);
    }

    private function buscarConcepto(int $categoriaId, EventoDisparador $disparador): ?ConceptoCobro
    {
        return ConceptoCobro::automaticoPara($categoriaId, $disparador)->first();
    }
}
