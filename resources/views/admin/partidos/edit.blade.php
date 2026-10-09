<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">
            Programar partido — {{ $partido->local->equipo->nombre }} vs {{ $partido->visita->equipo->nombre }}
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
                <h3 class="h5 fw-semibold text-dark mb-3">Fecha, hora y cancha</h3>
                <form method="POST" action="{{ route('admin.partidos.update', $partido) }}" class="d-flex flex-column gap-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label for="fecha_hora" value="Fecha y hora" />
                        <input id="fecha_hora" name="fecha_hora" type="datetime-local"
                            value="{{ old('fecha_hora', $partido->fecha_hora?->format('Y-m-d\TH:i')) }}"
                            class="form-select mt-1" />
                        <x-input-error :messages="$errors->get('fecha_hora')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="cancha" value="Cancha" />
                        <x-text-input id="cancha" name="cancha" type="text" class="mt-1"
                            value="{{ old('cancha', $partido->cancha) }}" />
                        <x-input-error :messages="$errors->get('cancha')" class="mt-2" />
                    </div>
                    <div class="d-flex justify-content-end">
                        <x-primary-button type="submit">Guardar</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white p-4 shadow-sm rounded-3">
                <h3 class="h5 fw-semibold text-dark mb-3">Asignar árbitro</h3>
                <form method="POST" action="{{ route('admin.partidos.arbitros.store', $partido) }}" class="d-flex flex-column flex-sm-row gap-3 align-items-start">
                    @csrf
                    <select name="arbitro_id" class="form-select flex-grow-1" required>
                        <option value="">Selecciona un árbitro</option>
                        @foreach ($arbitros as $arbitro)
                            <option value="{{ $arbitro->id }}" data-tarifa="{{ $arbitro->tarifa_default }}">{{ $arbitro->nombre }}</option>
                        @endforeach
                    </select>
                    <select name="rol" class="form-select" style="max-width: 10rem;" required>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->value }}">{{ ucfirst($rol->value) }}</option>
                        @endforeach
                    </select>
                    <input name="tarifa" type="number" step="0.01" min="0" placeholder="Tarifa" required
                        class="form-select" style="max-width: 8rem;" />
                    <x-primary-button type="submit">Asignar</x-primary-button>
                </form>

                <table class="table table-hover align-middle mb-0 mt-4">
                    <thead class="table-light">
                        <tr>
                            <th class="small text-secondary text-uppercase">Árbitro</th>
                            <th class="small text-secondary text-uppercase">Rol</th>
                            <th class="small text-secondary text-uppercase">Tarifa</th>
                            <th ></th>
                        </tr>
                    </thead>
                    <tbody >
                        @forelse ($partido->asignacionesArbitro as $asignacion)
                            <tr>
                                <td >{{ $asignacion->arbitro->nombre }}</td>
                                <td class="text-capitalize">{{ $asignacion->rol->value }}</td>
                                <td >${{ number_format($asignacion->tarifa, 2) }}</td>
                                <td class="text-end">
                                    <form action="{{ route('admin.partidos.arbitros.destroy', [$partido, $asignacion]) }}" method="POST" onsubmit="return confirm('¿Quitar esta asignación?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="link-danger small">Quitar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary">Sin árbitros asignados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.partidos.index') }}" class="small link-secondary">&larr; Volver a partidos</a>
                <a href="{{ route('admin.partidos.cedula.edit', $partido) }}" class="small link-success">Ir a la cédula arbitral &rarr;</a>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('select[name="arbitro_id"]')?.addEventListener('change', function (e) {
            const tarifaInput = document.querySelector('input[name="tarifa"]');
            const tarifa = e.target.selectedOptions[0]?.dataset.tarifa;
            if (tarifa && !tarifaInput.value) {
                tarifaInput.value = tarifa;
            }
        });
    </script>
</x-app-layout>
