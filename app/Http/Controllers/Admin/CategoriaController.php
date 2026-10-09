<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Torneo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(): View
    {
        $categorias = Categoria::with('torneo')->orderBy('nombre')->paginate(15);

        return view('admin.categorias.index', compact('categorias'));
    }

    public function create(): View
    {
        return view('admin.categorias.create', ['torneos' => Torneo::orderBy('nombre')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatos($request);

        Categoria::create($datos);

        return redirect()->route('admin.categorias.index')->with('status', 'Categoría creada correctamente.');
    }

    public function edit(Categoria $categoria): View
    {
        return view('admin.categorias.edit', [
            'categoria' => $categoria,
            'torneos' => Torneo::orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $datos = $this->validarDatos($request);

        $categoria->update($datos);

        return redirect()->route('admin.categorias.index')->with('status', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        $categoria->delete();

        return redirect()->route('admin.categorias.index')->with('status', 'Categoría eliminada.');
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'torneo_id' => ['required', 'exists:torneos,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'min_jugadores' => ['required', 'integer', 'min:1'],
            'max_jugadores' => ['required', 'integer', 'gte:min_jugadores'],
            'amarillas_para_suspension' => ['required', 'integer', 'min:1'],
            'partidos_castigo_roja' => ['required', 'integer', 'min:0'],
            'jornadas_amnistia' => ['nullable', 'integer', 'min:0'],
            'fecha_cierre_registros' => ['required', 'date'],
            'vueltas' => ['required', 'integer', 'in:1,2'],
            'equipos_a_liguilla' => ['required', 'integer', 'in:0,2,4,8,16'],
        ]);
    }
}
