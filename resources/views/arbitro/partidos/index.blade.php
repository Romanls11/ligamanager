<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">Mis partidos asignados</h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-5xl mx-auto px-3 px-lg-4">
            <div class="bg-white shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small text-secondary text-uppercase">Categoría</th>
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
                                        <a href="{{ route('arbitro.partidos.cedula.edit', $partido) }}" class="link-primary">Cédula</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary">No tienes partidos asignados.</td>
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
