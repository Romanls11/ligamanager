<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark mb-0">Categorías</h2>
            <a href="{{ route('admin.categorias.create') }}" class="btn btn-dark btn-sm">
                Nueva categoría
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
                                <th class="small text-secondary text-uppercase">Torneo</th>
                                <th class="small text-secondary text-uppercase">Jugadores (min-max)</th>
                                <th class="small text-secondary text-uppercase">Vueltas</th>
                                <th class="small text-secondary text-uppercase">Cierre de registros</th>
                                <th ></th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($categorias as $categoria)
                                <tr>
                                    <td >{{ $categoria->nombre }}</td>
                                    <td >{{ $categoria->torneo->nombre }}</td>
                                    <td >{{ $categoria->min_jugadores }}-{{ $categoria->max_jugadores }}</td>
                                    <td >{{ $categoria->vueltas }}</td>
                                    <td >{{ $categoria->fecha_cierre_registros->format('d/m/Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.categorias.rol-de-juegos.show', $categoria) }}" class="link-success">Rol de juegos</a>
                                        <a href="{{ route('admin.categorias.liguilla.show', $categoria) }}" class="link-warning">Liguilla</a>
                                        <a href="{{ route('admin.categorias.edit', $categoria) }}" class="link-primary">Editar</a>
                                        <form action="{{ route('admin.categorias.destroy', $categoria) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta categoría?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="link-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary">No hay categorías registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $categorias->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
