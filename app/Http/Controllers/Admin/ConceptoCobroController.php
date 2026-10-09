<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EventoDisparador;
use App\Enums\TipoConceptoCobro;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\ConceptoCobro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConceptoCobroController extends Controller
{
    public function index(): View
    {
        $conceptos = ConceptoCobro::with('categoria')->orderBy('nombre')->paginate(15);

        return view('admin.conceptos-cobro.index', compact('conceptos'));
    }

    public function create(): View
    {
        return view('admin.conceptos-cobro.create', $this->datosFormulario());
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatos($request);

        ConceptoCobro::create($datos);

        return redirect()->route('admin.conceptos-cobro.index')->with('status', 'Concepto de cobro creado correctamente.');
    }

    public function edit(ConceptoCobro $concepto): View
    {
        return view('admin.conceptos-cobro.edit', [
            'concepto' => $concepto,
            ...$this->datosFormulario(),
        ]);
    }

    public function update(Request $request, ConceptoCobro $concepto): RedirectResponse
    {
        $datos = $this->validarDatos($request);

        $concepto->update($datos);

        return redirect()->route('admin.conceptos-cobro.index')->with('status', 'Concepto de cobro actualizado correctamente.');
    }

    public function destroy(ConceptoCobro $concepto): RedirectResponse
    {
        $concepto->delete();

        return redirect()->route('admin.conceptos-cobro.index')->with('status', 'Concepto de cobro eliminado.');
    }

    private function datosFormulario(): array
    {
        return [
            'categorias' => Categoria::orderBy('nombre')->get(),
            'tipos' => TipoConceptoCobro::cases(),
            'eventos' => EventoDisparador::cases(),
        ];
    }

    private function validarDatos(Request $request): array
    {
        $datos = $request->validate([
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0'],
            'tipo' => ['required', Rule::enum(TipoConceptoCobro::class)],
            'automatico' => ['sometimes'],
            'evento_disparador' => ['nullable', Rule::enum(EventoDisparador::class)],
            'dias_plazo' => ['nullable', 'integer', 'min:0'],
        ]);

        $datos['automatico'] = $request->boolean('automatico');

        return $datos;
    }
}
