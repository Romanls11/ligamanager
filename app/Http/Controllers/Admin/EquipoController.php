<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EquipoController extends Controller
{
    public function index(): View
    {
        $equipos = Equipo::with('delegado')->orderBy('nombre')->paginate(15);

        return view('admin.equipos.index', compact('equipos'));
    }

    public function create(): View
    {
        return view('admin.equipos.create', ['delegados' => $this->delegadosDisponibles()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatos($request);

        if ($request->hasFile('logo')) {
            $datos['logo'] = $request->file('logo')->store('equipos', 'public');
        }

        Equipo::create($datos);

        return redirect()->route('admin.equipos.index')->with('status', 'Equipo creado correctamente.');
    }

    public function edit(Equipo $equipo): View
    {
        return view('admin.equipos.edit', [
            'equipo' => $equipo,
            'delegados' => $this->delegadosDisponibles(),
        ]);
    }

    public function update(Request $request, Equipo $equipo): RedirectResponse
    {
        $datos = $this->validarDatos($request);

        if ($request->hasFile('logo')) {
            if ($equipo->logo) {
                Storage::disk('public')->delete($equipo->logo);
            }
            $datos['logo'] = $request->file('logo')->store('equipos', 'public');
        }

        $equipo->update($datos);

        return redirect()->route('admin.equipos.index')->with('status', 'Equipo actualizado correctamente.');
    }

    public function destroy(Equipo $equipo): RedirectResponse
    {
        if ($equipo->logo) {
            Storage::disk('public')->delete($equipo->logo);
        }

        $equipo->delete();

        return redirect()->route('admin.equipos.index')->with('status', 'Equipo eliminado.');
    }

    private function delegadosDisponibles()
    {
        return User::role('delegado')->orderBy('name')->get();
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'color_uniforme' => ['nullable', 'string', 'max:255'],
            'delegado_user_id' => ['nullable', 'exists:users,id'],
            'telefono_contacto' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
