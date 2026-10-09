<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark mb-0">Torneos</h2>
            <a href="{{ route('admin.torneos.create') }}" class="btn btn-dark btn-sm">
                Nuevo torneo
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
                                <th class="small text-secondary text-uppercase">Fecha de inicio</th>
                                <th class="small text-secondary text-uppercase">Estado</th>
                                <th class="small text-secondary text-uppercase">Categorías</th>
                                <th ></th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($torneos as $torneo)
                                <tr>
                                    <td >{{ $torneo->nombre }}</td>
                                    <td >{{ $torneo->fecha_inicio->format('d/m/Y') }}</td>
                                    <td class="text-capitalize">{{ $torneo->estado->value }}</td>
                                    <td >{{ $torneo->categorias_count }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.torneos.edit', $torneo) }}" class="link-primary">Editar</a>
                                        <form action="{{ route('admin.torneos.destroy', $torneo) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este torneo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="link-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary">No hay torneos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $torneos->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
