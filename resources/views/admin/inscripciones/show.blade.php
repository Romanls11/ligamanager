<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">
            Plantilla — {{ $inscripcion->equipo->nombre }} ({{ $inscripcion->categoria->nombre }})
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-4xl mx-auto px-3 px-lg-4 d-flex flex-column gap-4">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul >
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($inscripcion->estado->value === 'suspendida_por_adeudo')
                <div class="alert alert-danger">
                    Este equipo está suspendido por adeudo vencido. No se pueden registrar altas hasta que regularice su situación.
                </div>
            @endif

            <div class="bg-white p-4 shadow-sm rounded-3">
                <h3 class="h5 fw-semibold text-dark mb-3">Agregar jugador a la plantilla</h3>
                <form method="POST" action="{{ route('admin.inscripciones.registros.store', $inscripcion) }}" class="d-flex flex-column flex-sm-row gap-3 align-items-start">
                    @csrf
                    <div class="flex-grow-1">
                        <select name="jugador_id" class="form-select" required>
                            <option value="">Selecciona un jugador disponible</option>
                            @foreach ($jugadoresDisponibles as $jugador)
                                <option value="{{ $jugador->id }}">{{ $jugador->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="max-width: 8rem;">
                        <x-text-input name="numero_camiseta" type="number" min="0" max="99"  placeholder="Número" />
                    </div>
                    <x-primary-button type="submit">Agregar</x-primary-button>
                </form>
                @if ($jugadoresDisponibles->isEmpty())
                    <p class="small text-secondary mt-2">No hay jugadores disponibles (todos ya tienen un registro activo en otra plantilla). Crea nuevos jugadores desde la sección de Jugadores.</p>
                @endif
            </div>

            <div class="bg-white shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small text-secondary text-uppercase">Jugador</th>
                                <th class="small text-secondary text-uppercase">Número</th>
                                <th class="small text-secondary text-uppercase">Amarillas</th>
                                <th class="small text-secondary text-uppercase">Estado</th>
                                <th ></th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($inscripcion->registrosJugador as $registro)
                                <tr>
                                    <td >{{ $registro->jugador->nombre_completo }}</td>
                                    <td >
                                        <form method="POST" action="{{ route('admin.inscripciones.registros.update', [$inscripcion, $registro]) }}" class="d-flex align-items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="{{ $registro->estado->value }}">
                                            <input type="number" name="numero_camiseta" min="0" max="99" value="{{ $registro->numero_camiseta }}"
                                                class="form-select form-select-sm" style="max-width: 4rem;" />
                                            <button type="submit" class="link-primary small">Guardar</button>
                                        </form>
                                    </td>
                                    <td >{{ $registro->amarillas_acumuladas }}</td>
                                    <td class="text-capitalize">{{ $registro->estado->value }}</td>
                                    <td class="text-end">
                                        @if ($registro->estado->value === 'activo')
                                            <form action="{{ route('admin.inscripciones.registros.destroy', [$inscripcion, $registro]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Dar de baja a este jugador de la plantilla?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="link-danger">Dar de baja</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.inscripciones.registros.update', [$inscripcion, $registro]) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="numero_camiseta" value="{{ $registro->numero_camiseta }}">
                                                <input type="hidden" name="estado" value="activo">
                                                <button type="submit" class="link-success">Reactivar</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary">Esta plantilla no tiene jugadores registrados.</td>
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
