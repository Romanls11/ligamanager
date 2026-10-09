<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">Pagos a árbitros</h2>
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

            <div class="bg-white p-3 shadow-sm rounded-3">
                <form method="GET" class="d-flex gap-3">
                    <select name="arbitro_id" class="form-select" style="max-width: 16rem;">
                        <option value="">Todos los árbitros</option>
                        @foreach ($arbitros as $arbitro)
                            <option value="{{ $arbitro->id }}" {{ (int) request('arbitro_id') === $arbitro->id ? 'selected' : '' }}>{{ $arbitro->nombre }}</option>
                        @endforeach
                    </select>
                    <x-secondary-button type="submit">Filtrar</x-secondary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small text-secondary text-uppercase">Árbitro</th>
                                <th class="small text-secondary text-uppercase">Partido</th>
                                <th class="small text-secondary text-uppercase">Rol</th>
                                <th class="small text-secondary text-uppercase">Tarifa</th>
                                <th class="small text-secondary text-uppercase">Saldo</th>
                                <th ></th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($asignaciones as $asignacion)
                                @php $saldo = max(0, $asignacion->tarifa - $asignacion->pagos->sum('monto')); @endphp
                                <tr>
                                    <td >{{ $asignacion->arbitro->nombre }}</td>
                                    <td >
                                        {{ $asignacion->partido->local->equipo->nombre }} vs {{ $asignacion->partido->visita->equipo->nombre }}
                                    </td>
                                    <td class="text-capitalize">{{ $asignacion->rol->value }}</td>
                                    <td >${{ number_format($asignacion->tarifa, 2) }}</td>
                                    <td >
                                        @if ($saldo <= 0)
                                            <span class="badge bg-success-subtle text-success-emphasis">Pagado</span>
                                        @else
                                            ${{ number_format($saldo, 2) }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($saldo > 0)
                                            <details class="d-inline-block text-start">
                                                <summary class="link-primary small" style="cursor: pointer;">Registrar pago</summary>
                                                <form method="POST" action="{{ route('admin.asignaciones.pagos.store', $asignacion) }}" class="mt-2 d-flex flex-column gap-2 bg-light p-3 rounded-3">
                                                    @csrf
                                                    <input type="number" name="monto" step="0.01" min="0.01" max="{{ $saldo }}" value="{{ $saldo }}" required
                                                        class="form-select form-select-sm" style="max-width: 8rem;" />
                                                    <input type="date" name="fecha_pago" value="{{ now()->toDateString() }}"
                                                        class="form-select form-select-sm" />
                                                    <button type="submit" class="btn btn-primary btn-sm w-100">Confirmar pago</button>
                                                </form>
                                            </details>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary">No hay asignaciones de árbitro registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $asignaciones->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
