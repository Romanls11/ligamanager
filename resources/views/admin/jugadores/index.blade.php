<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark mb-0">Jugadores</h2>
            <a href="{{ route('admin.jugadores.create') }}" class="btn btn-dark btn-sm">
                Nuevo jugador
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="mw-7xl mx-auto px-3 px-lg-4">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="bg-white p-3 shadow-sm rounded-3 mb-3">
                <form method="GET" class="d-flex gap-2">
                    <x-text-input name="buscar" type="text" class="mw-sm" placeholder="Buscar por nombre..." value="{{ request('buscar') }}" />
                    <x-secondary-button type="submit">Buscar</x-secondary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small text-secondary text-uppercase">Foto</th>
                                <th class="small text-secondary text-uppercase">Nombre completo</th>
                                <th class="small text-secondary text-uppercase">Fecha de nacimiento</th>
                                <th class="small text-secondary text-uppercase">CURP</th>
                                <th class="small text-secondary text-uppercase">Teléfono</th>
                                <th ></th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($jugadores as $jugador)
                                <tr>
                                    <td >
                                        @if ($jugador->foto)
                                            <img src="{{ Storage::url($jugador->foto) }}" alt="{{ $jugador->nombre_completo }}" class="rounded-circle" style="width: 2.5rem; height: 2.5rem; object-fit: cover;">
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td >{{ $jugador->nombre_completo }}</td>
                                    <td >{{ $jugador->fecha_nacimiento->format('d/m/Y') }}</td>
                                    <td >{{ $jugador->curp ?? '—' }}</td>
                                    <td >{{ $jugador->telefono ?? '—' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.jugadores.edit', $jugador) }}" class="link-primary">Editar</a>
                                        <form action="{{ route('admin.jugadores.destroy', $jugador) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este jugador?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="link-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary">No hay jugadores registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $jugadores->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
