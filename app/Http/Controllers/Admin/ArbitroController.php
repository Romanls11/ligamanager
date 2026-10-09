<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Arbitro;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArbitroController extends Controller
{
    public function index(): View
    {
        $arbitros = Arbitro::with('user')->orderBy('nombre')->paginate(15);

        return view('admin.arbitros.index', compact('arbitros'));
    }

    public function create(): View
    {
        return view('admin.arbitros.create', ['usuarios' => $this->usuariosDisponibles()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatos($request);

        Arbitro::create($datos);

        return redirect()->route('admin.arbitros.index')->with('status', 'Árbitro creado correctamente.');
    }

    public function edit(Arbitro $arbitro): View
    {
        return view('admin.arbitros.edit', [
            'arbitro' => $arbitro,
            'usuarios' => $this->usuariosDisponibles($arbitro),
        ]);
    }

    public function update(Request $request, Arbitro $arbitro): RedirectResponse
    {
        $datos = $this->validarDatos($request, $arbitro);

        $arbitro->update($datos);

        return redirect()->route('admin.arbitros.index')->with('status', 'Árbitro actualizado correctamente.');
    }

    public function destroy(Arbitro $arbitro): RedirectResponse
    {
        $arbitro->delete();

        return redirect()->route('admin.arbitros.index')->with('status', 'Árbitro eliminado.');
    }

    private function usuariosDisponibles(?Arbitro $arbitro = null)
    {
        return User::role('arbitro')
            ->where(function ($query) use ($arbitro) {
                $query->whereDoesntHave('arbitro');
                if ($arbitro?->user_id) {
                    $query->orWhere('id', $arbitro->user_id);
                }
            })
            ->orderBy('name')
            ->get();
    }

    private function validarDatos(Request $request, ?Arbitro $arbitro = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'tarifa_default' => ['required', 'numeric', 'min:0'],
            'user_id' => [
                'nullable',
                Rule::exists('users', 'id'),
                Rule::unique('arbitros', 'user_id')->ignore($arbitro?->id),
            ],
        ]);

        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }
}
