<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RolAsignacionArbitro;
use App\Http\Controllers\Controller;
use App\Models\Arbitro;
use App\Models\AsignacionArbitro;
use App\Models\Categoria;
use App\Models\Partido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PartidoController extends Controller
{
    public function index(Request $request): View
    {
        $partidos = Partido::query()
            ->with(['fase.categoria.torneo', 'local.equipo', 'visita.equipo', 'asignacionesArbitro.arbitro'])
            ->when($request->filled('categoria_id'), fn ($query) => $query->whereHas(
                'fase', fn ($fase) => $fase->where('categoria_id', $request->integer('categoria_id'))
            ))
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->string('estado')))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.partidos.index', [
            'partidos' => $partidos,
            'categorias' => Categoria::with('torneo')->orderBy('nombre')->get(),
        ]);
    }

    public function edit(Partido $partido): View
    {
        $partido->load(['fase.categoria', 'local.equipo', 'visita.equipo', 'asignacionesArbitro.arbitro']);

        return view('admin.partidos.edit', [
            'partido' => $partido,
            'arbitros' => Arbitro::where('activo', true)->orderBy('nombre')->get(),
            'roles' => RolAsignacionArbitro::cases(),
        ]);
    }

    public function update(Request $request, Partido $partido): RedirectResponse
    {
        $datos = $request->validate([
            'fecha_hora' => ['nullable', 'date'],
            'cancha' => ['nullable', 'string', 'max:255'],
        ]);

        $partido->update($datos);

        return back()->with('status', 'Programación del partido actualizada.');
    }

    public function asignarArbitro(Request $request, Partido $partido): RedirectResponse
    {
        $datos = $request->validate([
            'arbitro_id' => ['required', 'exists:arbitros,id'],
            'rol' => ['required', Rule::enum(RolAsignacionArbitro::class)],
            'tarifa' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $this->validarAsignacion($partido, (int) $datos['arbitro_id'], $datos['rol']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        AsignacionArbitro::create([
            'partido_id' => $partido->id,
            'arbitro_id' => $datos['arbitro_id'],
            'rol' => $datos['rol'],
            'tarifa' => $datos['tarifa'],
        ]);

        return back()->with('status', 'Árbitro asignado correctamente.');
    }

    public function quitarArbitro(Partido $partido, AsignacionArbitro $asignacion): RedirectResponse
    {
        abort_if($asignacion->partido_id !== $partido->id, 404);

        $asignacion->delete();

        return back()->with('status', 'Asignación de árbitro eliminada.');
    }

    private function validarAsignacion(Partido $partido, int $arbitroId, string $rol): void
    {
        $yaAsignadoEnRol = $partido->asignacionesArbitro()->where('rol', $rol)->exists();

        if ($rol === RolAsignacionArbitro::Central->value && $yaAsignadoEnRol) {
            throw ValidationException::withMessages([
                'arbitro_id' => 'Este partido ya tiene un árbitro central asignado.',
            ]);
        }

        $yaAsignadoEnPartido = $partido->asignacionesArbitro()->where('arbitro_id', $arbitroId)->exists();

        if ($yaAsignadoEnPartido) {
            throw ValidationException::withMessages([
                'arbitro_id' => 'Este árbitro ya está asignado a este partido.',
            ]);
        }

        if ($partido->fecha_hora) {
            $conflicto = AsignacionArbitro::where('arbitro_id', $arbitroId)
                ->whereHas('partido', fn ($query) => $query
                    ->where('id', '!=', $partido->id)
                    ->where('fecha_hora', $partido->fecha_hora))
                ->exists();

            if ($conflicto) {
                throw ValidationException::withMessages([
                    'arbitro_id' => 'Este árbitro ya está asignado a otro partido programado a la misma fecha y hora.',
                ]);
            }
        }
    }
}
