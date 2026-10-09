<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark mb-0">Inscripciones</h2>
            <a href="{{ route('admin.inscripciones.create') }}" class="btn btn-dark btn-sm">
                Nueva inscripción
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="mw-7xl mx-auto px-3 px-lg-4">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small text-secondary text-uppercase">Equipo</th>
                                <th class="small text-secondary text-uppercase">Categoría</th>
                                <th class="small text-secondary text-uppercase">Torneo</th>
                                <th class="small text-secondary text-uppercase">Jugadores</th>
                                <th class="small text-secondary text-uppercase">Estado</th>
                                <th ></th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($inscripciones as $inscripcion)
                                <tr>
                                    <td >{{ $inscripcion->equipo->nombre }}</td>
                                    <td >{{ $inscripcion->categoria->nombre }}</td>
                                    <td >{{ $inscripcion->categoria->torneo->nombre }}</td>
                                    <td >{{ $inscripcion->registros_jugador_count }}</td>
                                    <td >
                                        @if ($inscripcion->estado->value === 'suspendida_por_adeudo')
                                            <span class="badge bg-danger-subtle text-danger-emphasis">Suspendida por adeudo</span>
                                        @else
                                            <span class="text-capitalize">{{ str_replace('_', ' ', $inscripcion->estado->value) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.inscripciones.show', $inscripcion) }}" class="link-success">Plantilla</a>
                                        <a href="{{ route('admin.inscripciones.estado-cuenta.show', $inscripcion) }}" class="link-warning">Estado de cuenta</a>
                                        <a href="{{ route('admin.inscripciones.edit', $inscripcion) }}" class="link-primary">Editar</a>
                                        <form action="{{ route('admin.inscripciones.destroy', $inscripcion) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta inscripción?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="link-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary">No hay inscripciones registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $inscripciones->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
