<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark mb-0">Usuarios</h2>
            <a href="{{ route('admin.usuarios.create') }}" class="btn btn-dark btn-sm">
                Nuevo usuario
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
                                <th class="small text-secondary text-uppercase">Correo</th>
                                <th class="small text-secondary text-uppercase">Rol</th>
                                <th ></th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($usuarios as $usuario)
                                <tr>
                                    <td >{{ $usuario->name }}</td>
                                    <td >{{ $usuario->email }}</td>
                                    <td class="text-capitalize">{{ $usuario->roles->pluck('name')->join(', ') ?: 'sin rol' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="link-primary">Editar</a>
                                        <form action="{{ route('admin.usuarios.destroy', $usuario) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este usuario?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="link-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary">No hay usuarios registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $usuarios->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
