<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark mb-0">Conceptos de cobro</h2>
            <a href="{{ route('admin.conceptos-cobro.create') }}" class="btn btn-dark btn-sm">
                Nuevo concepto
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
                                <th class="small text-secondary text-uppercase">Categoría</th>
                                <th class="small text-secondary text-uppercase">Monto</th>
                                <th class="small text-secondary text-uppercase">Tipo</th>
                                <th class="small text-secondary text-uppercase">Automático</th>
                                <th ></th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($conceptos as $concepto)
                                <tr>
                                    <td >{{ $concepto->nombre }}</td>
                                    <td >{{ $concepto->categoria?->nombre ?? 'Todas' }}</td>
                                    <td >${{ number_format($concepto->monto, 2) }}</td>
                                    <td class="text-capitalize">{{ $concepto->tipo->value }}</td>
                                    <td >{{ $concepto->automatico ? 'Sí' : 'No' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.conceptos-cobro.edit', $concepto) }}" class="link-primary">Editar</a>
                                        <form action="{{ route('admin.conceptos-cobro.destroy', $concepto) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este concepto?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="link-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary">No hay conceptos de cobro registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $conceptos->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
