<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">Partidos</h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-7xl mx-auto px-3 px-lg-4 d-flex flex-column gap-3">
            <div class="bg-white p-3 shadow-sm rounded-3">
                <form method="GET" class="d-flex flex-column flex-sm-row gap-3">
                    <select name="categoria_id" class="form-select" style="max-width: 16rem;">
                        <option value="">Todas las categorías</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ (int) request('categoria_id') === $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nombre }} ({{ $categoria->torneo->nombre }})
                            </option>
                        @endforeach
                    </select>
                    <select name="estado" class="form-select" style="max-width: 12rem;">
                        <option value="">Todos los estados</option>
                        @foreach (['programado', 'jugado', 'default_local', 'default_visita', 'suspendido'] as $estado)
                            <option value="{{ $estado }}" {{ request('estado') === $estado ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($estado)) }}</option>
                        @endforeach
                    </select>
                    <x-secondary-button type="submit">Filtrar</x-secondary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small text-secondary text-uppercase">Categoría</th>
                                <th class="small text-secondary text-uppercase">Jornada</th>
                                <th class="small text-secondary text-uppercase">Partido</th>
                                <th class="small text-secondary text-uppercase">Fecha / cancha</th>
                                <th class="small text-secondary text-uppercase">Estado</th>
                                <th ></th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($partidos as $partido)
                                <tr>
                                    <td >{{ $partido->fase->categoria->nombre }}</td>
                                    <td >{{ $partido->jornada ?? '—' }}</td>
                                    <td >{{ $partido->local->equipo->nombre }} vs {{ $partido->visita->equipo->nombre }}</td>
                                    <td >
                                        @if ($partido->fecha_hora)
                                            {{ $partido->fecha_hora->format('d/m/Y H:i') }} @if ($partido->cancha) &middot; {{ $partido->cancha }} @endif
                                        @else
                                            <span class="text-muted">Sin programar</span>
                                        @endif
                                    </td>
                                    <td class="text-capitalize">{{ str_replace('_', ' ', $partido->estado->value) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.partidos.edit', $partido) }}" class="link-primary">Programar</a>
                                        <a href="{{ route('admin.partidos.cedula.edit', $partido) }}" class="link-success">Cédula</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary">No hay partidos que coincidan con el filtro.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $partidos->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
