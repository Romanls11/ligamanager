<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark mb-0">Cargos</h2>
            <a href="{{ route('admin.cargos.create') }}" class="btn btn-dark btn-sm">
                Nuevo cargo manual
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="mw-7xl mx-auto px-3 px-lg-4 d-flex flex-column gap-3">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="bg-white p-3 shadow-sm rounded-3">
                <form method="GET" class="d-flex flex-column flex-sm-row gap-3">
                    <select name="inscripcion_id" class="form-select" style="max-width: 16rem;">
                        <option value="">Todos los equipos</option>
                        @foreach ($inscripciones as $inscripcion)
                            <option value="{{ $inscripcion->id }}" {{ (int) request('inscripcion_id') === $inscripcion->id ? 'selected' : '' }}>
                                {{ $inscripcion->equipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <select name="estado" class="form-select" style="max-width: 12rem;">
                        <option value="">Todos los estados</option>
                        @foreach (['pendiente', 'parcial', 'pagado', 'condonado'] as $estado)
                            <option value="{{ $estado }}" {{ request('estado') === $estado ? 'selected' : '' }}>{{ ucfirst($estado) }}</option>
                        @endforeach
                    </select>
                    <label class="d-flex align-items-center gap-2 small text-dark">
                        <input type="checkbox" name="vencidos" value="1" {{ request('vencidos') ? 'checked' : '' }} class="form-check-input">
                        Solo vencidos
                    </label>
                    <x-secondary-button type="submit">Filtrar</x-secondary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small text-secondary text-uppercase">Equipo</th>
                                <th class="small text-secondary text-uppercase">Concepto</th>
                                <th class="small text-secondary text-uppercase">Monto</th>
                                <th class="small text-secondary text-uppercase">Vencimiento</th>
                                <th class="small text-secondary text-uppercase">Estado</th>
                                <th ></th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($cargos as $cargo)
                                <tr>
                                    <td >{{ $cargo->inscripcion->equipo->nombre }}</td>
                                    <td >{{ $cargo->conceptoCobro->nombre }}</td>
                                    <td >${{ number_format($cargo->monto, 2) }}</td>
                                    <td >
                                        {{ $cargo->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
                                        @if ($cargo->estaVencido())
                                            <span class="badge bg-danger-subtle text-danger-emphasis ms-1">Vencido</span>
                                        @endif
                                    </td>
                                    <td class="text-capitalize">{{ $cargo->estado->value }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.inscripciones.estado-cuenta.show', $cargo->inscripcion) }}" class="link-warning">Estado de cuenta</a>
                                        @if (! in_array($cargo->estado->value, ['pagado', 'condonado']))
                                            <form action="{{ route('admin.cargos.condonar', $cargo) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Condonar este cargo?');">
                                                @csrf
                                                <button type="submit" class="link-secondary">Condonar</button>
                                            </form>
                                        @endif
                                        @if ($cargo->totalPagado() == 0 && ! $cargo->evento_partido_id)
                                            <form action="{{ route('admin.cargos.destroy', $cargo) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este cargo?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="link-danger">Eliminar</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary">No hay cargos que coincidan con el filtro.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $cargos->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
