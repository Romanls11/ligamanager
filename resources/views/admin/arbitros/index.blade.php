<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark mb-0">Árbitros</h2>
            <a href="{{ route('admin.arbitros.create') }}" class="btn btn-dark btn-sm">
                Nuevo árbitro
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
                                <th class="small text-secondary text-uppercase">Nombre</th>
                                <th class="small text-secondary text-uppercase">Teléfono</th>
                                <th class="small text-secondary text-uppercase">Tarifa</th>
                                <th class="small text-secondary text-uppercase">Cuenta</th>
                                <th class="small text-secondary text-uppercase">Estado</th>
                                <th ></th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($arbitros as $arbitro)
                                <tr>
                                    <td >{{ $arbitro->nombre }}</td>
                                    <td >{{ $arbitro->telefono ?? '—' }}</td>
                                    <td >${{ number_format($arbitro->tarifa_default, 2) }}</td>
                                    <td >{{ $arbitro->user?->email ?? 'Sin cuenta' }}</td>
                                    <td >
                                        @if ($arbitro->activo)
                                            <span class="badge bg-success-subtle text-success-emphasis">Activo</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.arbitros.edit', $arbitro) }}" class="link-primary">Editar</a>
                                        <form action="{{ route('admin.arbitros.destroy', $arbitro) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este árbitro?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="link-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary">No hay árbitros registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $arbitros->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
