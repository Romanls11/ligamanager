<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoTorneo;
use App\Http\Controllers\Controller;
use App\Models\Torneo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TorneoController extends Controller
{
    public function index(): View
    {
        $torneos = Torneo::withCount('categorias')->orderByDesc('fecha_inicio')->paginate(15);

        return view('admin.torneos.index', compact('torneos'));
    }

    public function create(): View
    {
        return view('admin.torneos.create', ['estados' => EstadoTorneo::cases()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatos($request);

        Torneo::create($datos);

        return redirect()->route('admin.torneos.index')->with('status', 'Torneo creado correctamente.');
    }

    public function edit(Torneo $torneo): View
    {
        return view('admin.torneos.edit', [
            'torneo' => $torneo,
            'estados' => EstadoTorneo::cases(),
        ]);
    }

    public function update(Request $request, Torneo $torneo): RedirectResponse
    {
        $datos = $this->validarDatos($request);

        $torneo->update($datos);

        return redirect()->route('admin.torneos.index')->with('status', 'Torneo actualizado correctamente.');
    }

    public function destroy(Torneo $torneo): RedirectResponse
    {
        $torneo->delete();

        return redirect()->route('admin.torneos.index')->with('status', 'Torneo eliminado.');
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'fecha_inicio' => ['required', 'date'],
            'estado' => ['required', Rule::enum(EstadoTorneo::class)],
        ]);
    }
}
