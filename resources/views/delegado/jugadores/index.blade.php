<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">
            Jugadores — {{ $inscripcion->equipo->nombre }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-4xl mx-auto px-3 px-lg-4 d-flex flex-column gap-4">
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

            @if ($inscripcion->estado->value === 'suspendida_por_adeudo')
                <div class="alert alert-danger">
                    Tu equipo está suspendido por adeudo vencido. Las solicitudes de alta no podrán aprobarse hasta que se regularice el pago. Consulta el estado de cuenta.
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-3">
                <div class="px-3 py-2 bg-light border-bottom">
                    <h3 class="fw-semibold text-dark">Plantilla actual</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small text-secondary text-uppercase">Jugador</th>
                                <th class="small text-secondary text-uppercase">Número</th>
                                <th class="small text-secondary text-uppercase">Amarillas</th>
                                <th class="small text-secondary text-uppercase">Estado</th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($inscripcion->registrosJugador as $registro)
                                <tr>
                                    <td >{{ $registro->jugador->nombre_completo }}</td>
                                    <td >{{ $registro->numero_camiseta ?? '—' }}</td>
                                    <td >{{ $registro->amarillas_acumuladas }}</td>
                                    <td class="text-capitalize">{{ $registro->estado->value }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary">Tu plantilla no tiene jugadores registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white p-4 shadow-sm rounded-3">
                <h3 class="h5 fw-semibold text-dark mb-3">Proponer jugador ya registrado en el catálogo</h3>
                <form method="POST" action="{{ route('delegado.inscripciones.jugadores.store', $inscripcion) }}" class="d-flex flex-column flex-sm-row gap-3 align-items-start">
                    @csrf
                    <select name="jugador_id" class="form-select flex-grow-1" required>
                        <option value="">Selecciona un jugador disponible</option>
                        @foreach ($jugadoresDisponibles as $jugador)
                            <option value="{{ $jugador->id }}">{{ $jugador->nombre_completo }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="numero_camiseta" min="0" max="99" placeholder="Número"
                        class="form-select" style="max-width: 8rem;" />
                    <x-primary-button type="submit">Proponer alta</x-primary-button>
                </form>
            </div>

            <div class="bg-white p-4 shadow-sm rounded-3">
                <h3 class="h5 fw-semibold text-dark mb-3">Proponer jugador nuevo</h3>
                <form method="POST" action="{{ route('delegado.inscripciones.jugadores.store-nuevo', $inscripcion) }}" class="d-flex flex-column gap-3">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <x-input-label for="nombre_completo" value="Nombre completo" />
                            <x-text-input id="nombre_completo" name="nombre_completo" type="text" class="mt-1" required />
                        </div>
                        <div class="col-md-6">
                            <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
                            <x-text-input id="fecha_nacimiento" name="fecha_nacimiento" type="date" class="mt-1" required />
                        </div>
                        <div class="col-md-6">
                            <x-input-label for="curp" value="CURP (opcional)" />
                            <x-text-input id="curp" name="curp" type="text" class="mt-1 text-uppercase" maxlength="18" />
                        </div>
                        <div class="col-md-6">
                            <x-input-label for="telefono" value="Teléfono (opcional)" />
                            <x-text-input id="telefono" name="telefono" type="text" class="mt-1" />
                        </div>
                        <div class="col-md-6">
                            <x-input-label for="numero_camiseta_nuevo" value="Número (opcional)" />
                            <x-text-input id="numero_camiseta_nuevo" name="numero_camiseta" type="number" min="0" max="99" class="mt-1" />
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <x-primary-button type="submit">Registrar y proponer alta</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-3">
                <div class="px-3 py-2 bg-light border-bottom">
                    <h3 class="fw-semibold text-dark">Solicitudes</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small text-secondary text-uppercase">Jugador</th>
                                <th class="small text-secondary text-uppercase">Estado</th>
                                <th class="small text-secondary text-uppercase">Motivo</th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($solicitudes as $solicitud)
                                <tr>
                                    <td >{{ $solicitud->jugador->nombre_completo }}</td>
                                    <td >
                                        @if ($solicitud->estado->value === 'pendiente')
                                            <span class="badge bg-warning-subtle text-warning-emphasis">Pendiente</span>
                                        @elseif ($solicitud->estado->value === 'aprobada')
                                            <span class="badge bg-success-subtle text-success-emphasis">Aprobada</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger-emphasis">Rechazada</span>
                                        @endif
                                    </td>
                                    <td class="text-secondary">{{ $solicitud->motivo_rechazo ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-secondary">Aún no has enviado solicitudes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
