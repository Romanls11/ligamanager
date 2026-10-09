<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jugador;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class JugadorController extends Controller
{
    public function index(Request $request): View
    {
        $jugadores = Jugador::query()
            ->when($request->filled('buscar'), fn ($query) => $query->where('nombre_completo', 'like', '%'.$request->string('buscar').'%'))
            ->orderBy('nombre_completo')
            ->paginate(15)
            ->withQueryString();

        return view('admin.jugadores.index', compact('jugadores'));
    }

    public function create(): View
    {
        return view('admin.jugadores.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatos($request);

        if ($request->hasFile('foto')) {
            $datos['foto'] = $request->file('foto')->store('jugadores', 'public');
        }

        Jugador::create($datos);

        return redirect()->route('admin.jugadores.index')->with('status', 'Jugador creado correctamente.');
    }

    public function edit(Jugador $jugador): View
    {
        return view('admin.jugadores.edit', compact('jugador'));
    }

    public function update(Request $request, Jugador $jugador): RedirectResponse
    {
        $datos = $this->validarDatos($request, $jugador);

        if ($request->hasFile('foto')) {
            if ($jugador->foto) {
                Storage::disk('public')->delete($jugador->foto);
            }
            $datos['foto'] = $request->file('foto')->store('jugadores', 'public');
        }

        $jugador->update($datos);

        return redirect()->route('admin.jugadores.index')->with('status', 'Jugador actualizado correctamente.');
    }

    public function destroy(Jugador $jugador): RedirectResponse
    {
        if ($jugador->foto) {
            Storage::disk('public')->delete($jugador->foto);
        }

        $jugador->delete();

        return redirect()->route('admin.jugadores.index')->with('status', 'Jugador eliminado.');
    }

    private function validarDatos(Request $request, ?Jugador $jugador = null): array
    {
        return $request->validate([
            'nombre_completo' => ['required', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'curp' => ['nullable', 'string', 'max:18', 'unique:jugadores,curp,'.($jugador?->id)],
            'telefono' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
