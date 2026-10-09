<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">
            Estado de cuenta — {{ $inscripcion->equipo->nombre }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-4xl mx-auto px-3 px-lg-4 d-flex flex-column gap-4">
            <div class="bg-white p-4 shadow-sm rounded-3">
                <p class="text-dark">
                    {{ $inscripcion->categoria->nombre }} &middot; {{ $inscripcion->categoria->torneo->nombre }}
                </p>
                @if ($inscripcion->estado->value === 'suspendida_por_adeudo')
                    <p class="badge bg-danger-subtle text-danger-emphasis mt-2">
                        Equipo suspendido por adeudo vencido
                    </p>
                @endif
                <p class="h3 fw-bold text-dark mt-3">
                    Saldo pendiente: ${{ number_format($saldoTotal, 2) }}
                </p>
            </div>

            <div class="bg-white shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small text-secondary text-uppercase">Concepto</th>
                                <th class="small text-secondary text-uppercase">Monto</th>
                                <th class="small text-secondary text-uppercase">Saldo</th>
                                <th class="small text-secondary text-uppercase">Vencimiento</th>
                                <th class="small text-secondary text-uppercase">Estado</th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($cargos as $cargo)
                                <tr>
                                    <td >{{ $cargo->conceptoCobro->nombre }}</td>
                                    <td >${{ number_format($cargo->monto, 2) }}</td>
                                    <td >${{ number_format($cargo->saldoPendiente(), 2) }}</td>
                                    <td >
                                        {{ $cargo->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
                                        @if ($cargo->estaVencido())
                                            <span class="badge bg-danger-subtle text-danger-emphasis ms-1">Vencido</span>
                                        @endif
                                    </td>
                                    <td class="text-capitalize">{{ $cargo->estado->value }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary">Tu equipo no tiene cargos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
