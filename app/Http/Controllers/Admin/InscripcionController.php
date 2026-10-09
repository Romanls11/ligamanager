<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoInscripcion;
use App\Enums\EstadoRegistroJugador;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Equipo;
use App\Models\Inscripcion;
use App\Models\Jugador;
use App\Models\RegistroJugador;
use App\Services\AltaJugadorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class InscripcionController extends Controller
{
    public function __construct(private readonly AltaJugadorService $altaJugador) {}

    public function index(): View
    {
        $inscripciones = Inscripcion::with(['categoria.torneo', 'equipo'])
            ->withCount('registrosJugador')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.inscripciones.index', compact('inscripciones'));
    }

    public function create(): View
    {
        return view('admin.inscripciones.create', $this->datosFormulario());
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'categoria_id' => ['required', 'exists:categorias,id'],
            'equipo_id' => [
                'required',
                'exists:equipos,id',
                Rule::unique('inscripciones')->where('categoria_id', $request->input('categoria_id')),
            ],
            'estado' => ['required', Rule::enum(EstadoInscripcion::class)],
        ]);

        Inscripcion::create($datos);

        return redirect()->route('admin.inscripciones.index')->with('status', 'Inscripción creada correctamente.');
    }

    public function show(Inscripcion $inscripcion): View
    {
        $inscripcion->load(['categoria.torneo', 'equipo', 'registrosJugador.jugador']);

        $jugadoresDisponibles = Jugador::whereDoesntHave('registros', function ($query) {
            $query->where('estado', EstadoRegistroJugador::Activo);
        })->orderBy('nombre_completo')->get();

        return view('admin.inscripciones.show', [
            'inscripcion' => $inscripcion,
            'jugadoresDisponibles' => $jugadoresDisponibles,
        ]);
    }

    public function edit(Inscripcion $inscripcion): View
    {
        return view('admin.inscripciones.edit', [
            'inscripcion' => $inscripcion,
            ...$this->datosFormulario(),
        ]);
    }

    public function update(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        $datos = $request->validate([
            'categoria_id' => ['required', 'exists:categorias,id'],
            'equipo_id' => [
                'required',
                'exists:equipos,id',
                Rule::unique('inscripciones')->where('categoria_id', $request->input('categoria_id'))->ignore($inscripcion->id),
            ],
            'estado' => ['required', Rule::enum(EstadoInscripcion::class)],
        ]);

        $inscripcion->update($datos);

        return redirect()->route('admin.inscripciones.index')->with('status', 'Inscripción actualizada correctamente.');
    }

    public function destroy(Inscripcion $inscripcion): RedirectResponse
    {
        $inscripcion->delete();

        return redirect()->route('admin.inscripciones.index')->with('status', 'Inscripción eliminada.');
    }

    public function agregarJugador(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        $datos = $request->validate([
            'jugador_id' => ['required', 'exists:jugadores,id'],
            'numero_camiseta' => ['nullable', 'integer', 'min:0', 'max:99'],
        ]);

        try {
            $this->altaJugador->registrar($inscripcion, Jugador::findOrFail($datos['jugador_id']), $datos['numero_camiseta'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withErrors(['jugador_id' => $e->getMessage()])->withInput();
        }

        return back()->with('status', 'Jugador agregado a la plantilla.');
    }

    public function actualizarJugador(Request $request, Inscripcion $inscripcion, RegistroJugador $registro): RedirectResponse
    {
        abort_if($registro->inscripcion_id !== $inscripcion->id, 404);

        $datos = $request->validate([
            'numero_camiseta' => ['nullable', 'integer', 'min:0', 'max:99'],
            'estado' => ['required', Rule::enum(EstadoRegistroJugador::class)],
        ]);

        if ($datos['estado'] === EstadoRegistroJugador::Activo->value && $registro->estado !== EstadoRegistroJugador::Activo) {
            try {
                $this->altaJugador->validar($inscripcion, $registro->jugador_id, ignorarRegistroId: $registro->id);
            } catch (RuntimeException $e) {
                return back()->withErrors(['jugador_id' => $e->getMessage()])->withInput();
            }
        }

        $registro->update($datos);

        return back()->with('status', 'Jugador actualizado.');
    }

    public function quitarJugador(Inscripcion $inscripcion, RegistroJugador $registro): RedirectResponse
    {
        abort_if($registro->inscripcion_id !== $inscripcion->id, 404);

        $registro->update(['estado' => EstadoRegistroJugador::Baja]);

        return back()->with('status', 'Jugador dado de baja de la plantilla.');
    }

    private function datosFormulario(): array
    {
        return [
            'categorias' => Categoria::with('torneo')->orderBy('nombre')->get(),
            'equipos' => Equipo::orderBy('nombre')->get(),
            'estados' => EstadoInscripcion::cases(),
        ];
    }
}
