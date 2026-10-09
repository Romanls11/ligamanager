<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\EstadoRegistroJugador;
use App\Enums\TipoEventoPartido;
use App\Models\EventoPartido;
use App\Models\Partido;
use App\Models\RegistroJugador;
use App\Services\CedulaArbitralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

trait GestionaCedulaArbitral
{
    private function cedulaService(): CedulaArbitralService
    {
        return app(CedulaArbitralService::class);
    }

    /**
     * Verifica que el usuario autenticado pueda operar la cédula de este partido.
     * Los controladores que usan este trait deben sobrescribirlo si necesitan restringir el acceso.
     */
    protected function autorizarPartido(Partido $partido): void
    {
        //
    }

    /**
     * Nombre del prefijo de rutas ('admin' o 'arbitro') usado por la vista compartida
     * para construir los enlaces de las acciones (route("{$prefijo}.partidos.cedula...")).
     */
    abstract protected function prefijoRutas(): string;

    public function edit(Partido $partido): View
    {
        $this->autorizarPartido($partido);

        $partido->load(['fase.categoria', 'local.equipo', 'visita.equipo', 'eventos.registroJugador.jugador']);

        $jugadoresLocal = RegistroJugador::where('inscripcion_id', $partido->local_inscripcion_id)
            ->where('estado', EstadoRegistroJugador::Activo)
            ->with('jugador')
            ->get();

        $jugadoresVisita = RegistroJugador::where('inscripcion_id', $partido->visita_inscripcion_id)
            ->where('estado', EstadoRegistroJugador::Activo)
            ->with('jugador')
            ->get();

        return view('partidos.cedula', [
            'partido' => $partido,
            'jugadoresLocal' => $jugadoresLocal,
            'jugadoresVisita' => $jugadoresVisita,
            'tipos' => TipoEventoPartido::cases(),
            'prefijo' => $this->prefijoRutas(),
        ]);
    }

    public function store(Request $request, Partido $partido): RedirectResponse
    {
        $this->autorizarPartido($partido);

        $datos = $request->validate([
            'registro_jugador_id' => ['required', 'exists:registros_jugador,id'],
            'tipo' => ['required', Rule::enum(TipoEventoPartido::class)],
            'minuto' => ['nullable', 'integer', 'min:0', 'max:120'],
        ]);

        try {
            $this->cedulaService()->registrarEvento(
                $partido,
                RegistroJugador::findOrFail($datos['registro_jugador_id']),
                TipoEventoPartido::from($datos['tipo']),
                $datos['minuto'] ?? null,
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['registro_jugador_id' => $e->getMessage()]);
        }

        return back()->with('status', 'Evento registrado.');
    }

    public function destroyEvento(Partido $partido, EventoPartido $evento): RedirectResponse
    {
        $this->autorizarPartido($partido);

        abort_if($evento->partido_id !== $partido->id, 404);

        try {
            $this->cedulaService()->eliminarEvento($evento);
        } catch (RuntimeException $e) {
            return back()->withErrors(['evento' => $e->getMessage()]);
        }

        return back()->with('status', 'Evento eliminado.');
    }

    public function finalizar(Request $request, Partido $partido): RedirectResponse
    {
        $this->autorizarPartido($partido);

        $datos = $request->validate([
            'goles_local' => ['required', 'integer', 'min:0'],
            'goles_visita' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $this->cedulaService()->finalizar($partido, $datos['goles_local'], $datos['goles_visita']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['partido' => $e->getMessage()]);
        }

        return back()->with('status', 'Partido finalizado.');
    }

    public function marcarDefault(Request $request, Partido $partido): RedirectResponse
    {
        $this->autorizarPartido($partido);

        $datos = $request->validate([
            'equipo' => ['required', Rule::in(['local', 'visita'])],
        ]);

        try {
            $this->cedulaService()->marcarDefault($partido, $datos['equipo']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['partido' => $e->getMessage()]);
        }

        return back()->with('status', 'Default registrado.');
    }
}
