<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">Solicitudes de alta</h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-6xl mx-auto px-3 px-lg-4 d-flex flex-column gap-3">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small text-secondary text-uppercase">Equipo</th>
                                <th class="small text-secondary text-uppercase">Jugador</th>
                                <th class="small text-secondary text-uppercase">Solicitado por</th>
                                <th class="small text-secondary text-uppercase">Estado</th>
                                <th ></th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($solicitudes as $solicitud)
                                <tr>
                                    <td >
                                        {{ $solicitud->inscripcion->equipo->nombre }}
                                        <span class="small text-muted">({{ $solicitud->inscripcion->categoria->nombre }})</span>
                                    </td>
                                    <td >
                                        {{ $solicitud->jugador->nombre_completo }}
                                        @if ($solicitud->numero_camiseta !== null)
                                            <span class="small text-muted">#{{ $solicitud->numero_camiseta }}</span>
                                        @endif
                                    </td>
                                    <td >{{ $solicitud->solicitadoPor->name }}</td>
                                    <td >
                                        @if ($solicitud->estado->value === 'pendiente')
                                            <span class="badge bg-warning-subtle text-warning-emphasis">Pendiente</span>
                                        @elseif ($solicitud->estado->value === 'aprobada')
                                            <span class="badge bg-success-subtle text-success-emphasis">Aprobada</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger-emphasis">Rechazada</span>
                                            @if ($solicitud->motivo_rechazo)
                                                <p class="small text-muted mt-1">{{ $solicitud->motivo_rechazo }}</p>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($solicitud->estado->value === 'pendiente')
                                            <form action="{{ route('admin.solicitudes-alta.aprobar', $solicitud) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="link-success me-3">Aprobar</button>
                                            </form>
                                            <details class="d-inline-block text-start">
                                                <summary class="link-danger" style="cursor: pointer;">Rechazar</summary>
                                                <form action="{{ route('admin.solicitudes-alta.rechazar', $solicitud) }}" method="POST" class="mt-2 d-flex flex-column gap-2 bg-light p-3 rounded-3">
                                                    @csrf
                                                    <input type="text" name="motivo_rechazo" placeholder="Motivo (opcional)"
                                                        class="form-select form-select-sm" style="max-width: 12rem;" />
                                                    <button type="submit" class="btn btn-danger btn-sm w-100">Confirmar rechazo</button>
                                                </form>
                                            </details>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary">No hay solicitudes de alta.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $solicitudes->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
