<?php

namespace App\Observers;

use App\Enums\EstadoCargo;
use App\Enums\EstadoPartido;
use App\Enums\EventoDisparador;
use App\Models\Cargo;
use App\Models\ConceptoCobro;
use App\Models\Inscripcion;
use App\Models\Partido;
use App\Models\Suspension;
use App\Services\LiguillaService;

class PartidoObserver
{
    private const ESTADOS_CONCLUIDOS = [
        EstadoPartido::Jugado,
        EstadoPartido::DefaultLocal,
        EstadoPartido::DefaultVisita,
    ];

    public function __construct(private readonly LiguillaService $liguilla) {}

    public function updated(Partido $partido): void
    {
        if ($partido->wasChanged('fecha_hora') && is_null($partido->getOriginal('fecha_hora')) && $partido->fecha_hora) {
            $this->generarCargosArbitraje($partido);
        }

        if ($partido->wasChanged('estado')) {
            $estadoAnterior = $partido->getOriginal('estado');
            $estadoAnterior = $estadoAnterior instanceof EstadoPartido ? $estadoAnterior : EstadoPartido::from($estadoAnterior);

            if ($estadoAnterior === EstadoPartido::Programado) {
                if (in_array($partido->estado, [EstadoPartido::DefaultLocal, EstadoPartido::DefaultVisita], true)) {
                    $this->procesarDefault($partido);
                }

                if (in_array($partido->estado, self::ESTADOS_CONCLUIDOS, true)) {
                    $this->aplicarCumplimientoSuspensiones($partido);
                    $this->liguilla->avanzarSiRondaCompleta($partido);
                }
            }
        }
    }

    private function generarCargosArbitraje(Partido $partido): void
    {
        $categoriaId = $partido->fase->categoria_id;
        $concepto = ConceptoCobro::automaticoPara($categoriaId, EventoDisparador::PartidoProgramado)->first();

        if (! $concepto) {
            return;
        }

        foreach ([$partido->local_inscripcion_id, $partido->visita_inscripcion_id] as $inscripcionId) {
            $yaExiste = Cargo::where('partido_id', $partido->id)
                ->where('concepto_cobro_id', $concepto->id)
                ->where('inscripcion_id', $inscripcionId)
                ->exists();

            if ($yaExiste) {
                continue;
            }

            Cargo::create([
                'inscripcion_id' => $inscripcionId,
                'concepto_cobro_id' => $concepto->id,
                'partido_id' => $partido->id,
                'monto' => $concepto->monto,
                'descripcion' => $concepto->nombre.' - jornada '.$partido->jornada,
                'estado' => EstadoCargo::Pendiente,
                'fecha_vencimiento' => $concepto->dias_plazo ? now()->addDays($concepto->dias_plazo) : null,
            ]);
        }
    }

    private function procesarDefault(Partido $partido): void
    {
        $marcador = config('liga.marcador_default');
        $inscripcionResponsable = $partido->estado === EstadoPartido::DefaultLocal
            ? $partido->local_inscripcion_id
            : $partido->visita_inscripcion_id;

        $partido->updateQuietly([
            'goles_local' => $partido->estado === EstadoPartido::DefaultLocal ? $marcador['goles_contra'] : $marcador['goles_favor'],
            'goles_visita' => $partido->estado === EstadoPartido::DefaultLocal ? $marcador['goles_favor'] : $marcador['goles_contra'],
        ]);

        $categoriaId = $partido->fase->categoria_id;
        $concepto = ConceptoCobro::automaticoPara($categoriaId, EventoDisparador::DefaultPartido)->first();

        if (! $concepto) {
            return;
        }

        $yaExiste = Cargo::where('partido_id', $partido->id)
            ->where('concepto_cobro_id', $concepto->id)
            ->where('inscripcion_id', $inscripcionResponsable)
            ->exists();

        if ($yaExiste) {
            return;
        }

        Cargo::create([
            'inscripcion_id' => $inscripcionResponsable,
            'concepto_cobro_id' => $concepto->id,
            'partido_id' => $partido->id,
            'monto' => $concepto->monto,
            'descripcion' => $concepto->nombre.' - jornada '.$partido->jornada,
            'estado' => EstadoCargo::Pendiente,
            'fecha_vencimiento' => $concepto->dias_plazo ? now()->addDays($concepto->dias_plazo) : null,
        ]);
    }

    private function aplicarCumplimientoSuspensiones(Partido $partido): void
    {
        foreach ([$partido->local_inscripcion_id, $partido->visita_inscripcion_id] as $inscripcionId) {
            $inscripcion = Inscripcion::find($inscripcionId);

            if (! $inscripcion) {
                continue;
            }

            $registroIds = $inscripcion->registrosJugador()->pluck('id');

            Suspension::whereIn('registro_jugador_id', $registroIds)
                ->where('cumplida', false)
                // El partido donde se originó la suspensión no cuenta como cumplido: se sirve a partir del siguiente.
                ->where(function ($query) use ($partido) {
                    $query->whereNull('partido_origen_id')->orWhere('partido_origen_id', '!=', $partido->id);
                })
                ->get()
                ->each(function (Suspension $suspension) {
                    $suspension->increment('partidos_cumplidos');

                    if ($suspension->partidos_cumplidos >= $suspension->partidos_castigo) {
                        $suspension->update(['cumplida' => true]);
                    }
                });
        }
    }
}
