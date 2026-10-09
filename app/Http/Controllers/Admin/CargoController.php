<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoCargo;
use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\ConceptoCobro;
use App\Models\Inscripcion;
use App\Services\PagoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CargoController extends Controller
{
    public function index(Request $request): View
    {
        $cargos = Cargo::query()
            ->with(['inscripcion.equipo', 'inscripcion.categoria', 'conceptoCobro'])
            ->when($request->filled('inscripcion_id'), fn ($query) => $query->where('inscripcion_id', $request->integer('inscripcion_id')))
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->string('estado')))
            ->when($request->boolean('vencidos'), fn ($query) => $query
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->whereNotNull('fecha_vencimiento')
                ->where('fecha_vencimiento', '<', now()->toDateString()))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.cargos.index', [
            'cargos' => $cargos,
            'inscripciones' => Inscripcion::with('equipo')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.cargos.create', $this->datosFormulario());
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'inscripcion_id' => ['required', 'exists:inscripciones,id'],
            'concepto_cobro_id' => ['required', 'exists:conceptos_cobro,id'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'fecha_vencimiento' => ['nullable', 'date'],
        ]);

        Cargo::create($datos + ['estado' => EstadoCargo::Pendiente]);

        return redirect()->route('admin.cargos.index')->with('status', 'Cargo registrado correctamente.');
    }

    public function condonar(Cargo $cargo): RedirectResponse
    {
        try {
            app(PagoService::class)->condonarCargo($cargo);
        } catch (RuntimeException $e) {
            return back()->withErrors(['cargo' => $e->getMessage()]);
        }

        return back()->with('status', 'Cargo condonado.');
    }

    public function destroy(Cargo $cargo): RedirectResponse
    {
        if ($cargo->pagos()->exists()) {
            return back()->withErrors(['cargo' => 'No se puede eliminar: este cargo ya tiene pagos registrados.']);
        }

        if ($cargo->evento_partido_id) {
            return back()->withErrors(['cargo' => 'No se puede eliminar: este cargo fue generado automáticamente por un evento del partido. Corrige el evento en la cédula arbitral.']);
        }

        $cargo->delete();

        return back()->with('status', 'Cargo eliminado.');
    }

    private function datosFormulario(): array
    {
        return [
            'inscripciones' => Inscripcion::with(['equipo', 'categoria'])->get(),
            'conceptos' => ConceptoCobro::where('automatico', false)->orderBy('nombre')->get(),
        ];
    }
}
