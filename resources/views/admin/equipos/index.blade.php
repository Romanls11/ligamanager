<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark mb-0">Equipos</h2>
            <a href="{{ route('admin.equipos.create') }}" class="btn btn-dark btn-sm">
                Nuevo equipo
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
                                <th class="small text-secondary text-uppercase">Logo</th>
                                <th class="small text-secondary text-uppercase">Nombre</th>
                                <th class="small text-secondary text-uppercase">Delegado</th>
                                <th class="small text-secondary text-uppercase">Teléfono</th>
                                <th ></th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($equipos as $equipo)
                                <tr>
                                    <td >
                                        @if ($equipo->logo)
                                            <img src="{{ Storage::url($equipo->logo) }}" alt="{{ $equipo->nombre }}" class="rounded-circle" style="width: 2.5rem; height: 2.5rem; object-fit: cover;">
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td >{{ $equipo->nombre }}</td>
                                    <td >{{ $equipo->delegado?->name ?? 'Sin asignar' }}</td>
                                    <td >{{ $equipo->telefono_contacto ?? '—' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.equipos.edit', $equipo) }}" class="link-primary">Editar</a>
                                        <form action="{{ route('admin.equipos.destroy', $equipo) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este equipo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="link-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary">No hay equipos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $equipos->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
