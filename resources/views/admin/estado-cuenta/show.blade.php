<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold text-dark mb-0">
                Estado de cuenta — {{ $inscripcion->equipo->nombre }}
            </h2>
            <a href="{{ route('admin.inscripciones.estado-cuenta.pdf', $inscripcion) }}" target="_blank"
                class="btn btn-dark btn-sm">
                Descargar PDF
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="mw-5xl mx-auto px-3 px-lg-4 d-flex flex-column gap-4">
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
                                <th ></th>
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
                                    <td class="text-end">
                                        @if (! in_array($cargo->estado->value, ['pagado', 'condonado']))
                                            <details class="d-inline-block text-start">
                                                <summary class="link-primary small" style="cursor: pointer;">Registrar pago</summary>
                                                <form method="POST" action="{{ route('admin.cargos.pagos.store', $cargo) }}" class="mt-2 d-flex flex-column gap-2 bg-light p-3 rounded-3">
                                                    @csrf
                                                    <input type="number" name="monto" step="0.01" min="0.01" max="{{ $cargo->saldoPendiente() }}"
                                                        placeholder="Monto" required
                                                        class="form-select form-select-sm" />
                                                    <select name="metodo" required class="form-select form-select-sm">
                                                        @foreach ($metodos as $metodo)
                                                            <option value="{{ $metodo->value }}">{{ ucfirst($metodo->value) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="date" name="fecha" value="{{ now()->toDateString() }}"
                                                        class="form-select form-select-sm" />
                                                    <button type="submit" class="btn btn-primary btn-sm w-100">Confirmar pago</button>
                                                </form>
                                            </details>
                                        @endif
                                    </td>
                                </tr>
                                @foreach ($cargo->pagos as $pago)
                                    <tr class="table-light">
                                        <td colspan="4" class="small text-secondary ps-4">
                                            Pago {{ $pago->folio_recibo }} &middot; ${{ number_format($pago->monto, 2) }} &middot; {{ $pago->fecha->format('d/m/Y') }} &middot; {{ ucfirst($pago->metodo->value) }}
                                        </td>
                                        <td colspan="2" class="text-end">
                                            <a href="{{ route('admin.pagos.recibo', $pago) }}" target="_blank" class="small link-success">Ver recibo</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary">Este equipo no tiene cargos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <a href="{{ route('admin.inscripciones.index') }}" class="small link-secondary">&larr; Volver a inscripciones</a>
        </div>
    </div>
</x-app-layout>
