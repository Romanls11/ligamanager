<?php

namespace App\Http\Controllers\Delegado;

use App\Enums\EstadoRegistroJugador;
use App\Http\Controllers\Concerns\AutorizaEquipoDelegado;
use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Models\Jugador;
use App\Models\SolicitudAlta;
use App\Services\SolicitudAltaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

class JugadorController extends Controller
{
    use AutorizaEquipoDelegado;

    public function __construct(private readonly SolicitudAltaService $solicitudes) {}

    public function index(Inscripcion $inscripcion): View
    {
        $this->autorizarInscripcion($inscripcion);

        $inscripcion->load(['equipo', 'categoria', 'registrosJugador.jugador']);

        $jugadoresDisponibles = Jugador::whereDoesntHave('registros', function ($query) {
            $query->where('estado', EstadoRegistroJugador::Activo);
        })->orderBy('nombre_completo')->get();

        $solicitudes = SolicitudAlta::where('inscripcion_id', $inscripcion->id)
            ->with('jugador')
            ->orderByDesc('id')
            ->get();

        return view('delegado.jugadores.index', [
            'inscripcion' => $inscripcion,
            'jugadoresDisponibles' => $jugadoresDisponibles,
            'solicitudes' => $solicitudes,
        ]);
    }

    public function store(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        $this->autorizarInscripcion($inscripcion);

        $datos = $request->validate([
            'jugador_id' => ['required', 'exists:jugadores,id'],
            'numero_camiseta' => ['nullable', 'integer', 'min:0', 'max:99'],
        ]);

        try {
            $this->solicitudes->solicitar(
                $inscripcion,
                Jugador::findOrFail($datos['jugador_id']),
                $datos['numero_camiseta'] ?? null,
                Auth::user(),
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['jugador_id' => $e->getMessage()]);
        }

        return back()->with('status', 'Solicitud enviada. El administrador la revisará.');
    }

    public function storeNuevo(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        $this->autorizarInscripcion($inscripcion);

        $datos = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date'],
            'curp' => ['nullable', 'string', 'max:18', 'unique:jugadores,curp'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'numero_camiseta' => ['nullable', 'integer', 'min:0', 'max:99'],
        ]);

        $jugador = Jugador::create([
            'nombre_completo' => $datos['nombre_completo'],
            'fecha_nacimiento' => $datos['fecha_nacimiento'],
            'curp' => $datos['curp'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
        ]);

        try {
            $this->solicitudes->solicitar($inscripcion, $jugador, $datos['numero_camiseta'] ?? null, Auth::user());
        } catch (RuntimeException $e) {
            return back()->withErrors(['jugador_id' => $e->getMessage()]);
        }

        return back()->with('status', 'Jugador registrado y solicitud enviada. El administrador la revisará.');
    }
}
