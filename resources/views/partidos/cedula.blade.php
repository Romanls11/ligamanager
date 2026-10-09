<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">
            Cédula arbitral — {{ $partido->local->equipo->nombre }} vs {{ $partido->visita->equipo->nombre }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-3xl mx-auto px-3 px-lg-4 d-flex flex-column gap-4">
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

            <div class="bg-white p-4 shadow-sm rounded-3">
                <p class="text-dark">
                    Categoría: <strong>{{ $partido->fase->categoria->nombre }}</strong>
                    &middot; Jornada {{ $partido->jornada ?? '—' }}
                    &middot; Estado: <span class="text-capitalize">{{ str_replace('_', ' ', $partido->estado->value) }}</span>
                </p>
                @if ($partido->goles_local !== null)
                    <p class="h3 fw-bold text-dark mt-2">
                        {{ $partido->local->equipo->nombre }} {{ $partido->goles_local }} - {{ $partido->goles_visita }} {{ $partido->visita->equipo->nombre }}
                    </p>
                @endif
            </div>

            @if ($partido->estado->value === 'programado')
                <div class="bg-white p-4 shadow-sm rounded-3">
                    <h3 class="h5 fw-semibold text-dark mb-3">Registrar evento</h3>
                    <form method="POST" action="{{ route("{$prefijo}.partidos.cedula.eventos.store", $partido) }}" class="d-flex flex-column flex-sm-row gap-3 align-items-start">
                        @csrf
                        <select name="registro_jugador_id" class="form-select flex-grow-1" required>
                            <option value="">Selecciona un jugador</option>
                            <optgroup label="{{ $partido->local->equipo->nombre }}">
                                @foreach ($jugadoresLocal as $registro)
                                    <option value="{{ $registro->id }}">{{ $registro->jugador->nombre_completo }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="{{ $partido->visita->equipo->nombre }}">
                                @foreach ($jugadoresVisita as $registro)
                                    <option value="{{ $registro->id }}">{{ $registro->jugador->nombre_completo }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                        <select name="tipo" class="form-select" style="max-width: 9rem;" required>
                            @foreach ($tipos as $tipo)
                                <option value="{{ $tipo->value }}">{{ ucfirst($tipo->value) }}</option>
                            @endforeach
                        </select>
                        <input name="minuto" type="number" min="0" max="120" placeholder="Minuto"
                            class="form-select" style="max-width: 7rem;" />
                        <x-primary-button type="submit">Registrar</x-primary-button>
                    </form>
                </div>
            @endif

            <div class="bg-white p-4 shadow-sm rounded-3">
                <h3 class="h5 fw-semibold text-dark mb-3">Eventos capturados</h3>
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small text-secondary text-uppercase">Jugador</th>
                            <th class="small text-secondary text-uppercase">Tipo</th>
                            <th class="small text-secondary text-uppercase">Minuto</th>
                            <th ></th>
                        </tr>
                    </thead>
                    <tbody >
                        @forelse ($partido->eventos as $evento)
                            <tr>
                                <td >{{ $evento->registroJugador->jugador->nombre_completo }}</td>
                                <td class="text-capitalize">{{ $evento->tipo->value }}</td>
                                <td >{{ $evento->minuto ?? '—' }}</td>
                                <td class="text-end">
                                    <form action="{{ route("{$prefijo}.partidos.cedula.eventos.destroy", [$partido, $evento]) }}" method="POST" onsubmit="return confirm('¿Quitar este evento?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="link-danger small">Quitar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary">Sin eventos capturados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($partido->estado->value === 'programado')
                <div class="bg-white p-4 shadow-sm rounded-3">
                    <h3 class="h5 fw-semibold text-dark mb-3">Finalizar partido</h3>
                    <form method="POST" action="{{ route("{$prefijo}.partidos.cedula.finalizar", $partido) }}" class="d-flex flex-column flex-sm-row gap-3 align-items-end">
                        @csrf
                        <div style="max-width: 8rem;">
                            <x-input-label value="{{ $partido->local->equipo->nombre }}" />
                            <x-text-input name="goles_local" type="number" min="0" class="mt-1" required />
                        </div>
                        <div style="max-width: 8rem;">
                            <x-input-label value="{{ $partido->visita->equipo->nombre }}" />
                            <x-text-input name="goles_visita" type="number" min="0" class="mt-1" required />
                        </div>
                        <x-primary-button type="submit">Finalizar partido</x-primary-button>
                    </form>
                </div>

                <div class="bg-white p-4 shadow-sm rounded-3">
                    <h3 class="h5 fw-semibold text-dark mb-3">Marcar default</h3>
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <form method="POST" action="{{ route("{$prefijo}.partidos.cedula.default", $partido) }}" onsubmit="return confirm('¿Marcar default de {{ $partido->local->equipo->nombre }}?');">
                            @csrf
                            <input type="hidden" name="equipo" value="local">
                            <x-danger-button type="submit">Default de {{ $partido->local->equipo->nombre }}</x-danger-button>
                        </form>
                        <form method="POST" action="{{ route("{$prefijo}.partidos.cedula.default", $partido) }}" onsubmit="return confirm('¿Marcar default de {{ $partido->visita->equipo->nombre }}?');">
                            @csrf
                            <input type="hidden" name="equipo" value="visita">
                            <x-danger-button type="submit">Default de {{ $partido->visita->equipo->nombre }}</x-danger-button>
                        </form>
                    </div>
                </div>
            @endif

            <a href="{{ route("{$prefijo}.partidos.index") }}" class="small link-secondary">&larr; Volver a partidos</a>
        </div>
    </div>
</x-app-layout>
