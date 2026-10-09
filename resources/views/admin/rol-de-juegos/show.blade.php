<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">
            Rol de juegos — {{ $categoria->nombre }} ({{ $categoria->torneo->nombre }})
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-5xl mx-auto px-3 px-lg-4 d-flex flex-column gap-4">
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

            @if (! $fase)
                <div class="bg-white p-4 shadow-sm rounded-3">
                    <p class="text-dark mb-3">
                        Esta categoría aún no tiene rol de juegos generado.
                        Equipos con inscripción activa: <strong>{{ $totalInscripcionesActivas }}</strong>.
                    </p>
                    @if ($totalInscripcionesActivas < 2)
                        <p class="small text-danger">Se necesitan al menos 2 equipos con inscripción activa para generar el rol de juegos.</p>
                    @else
                        <form method="POST" action="{{ route('admin.categorias.rol-de-juegos.generar', $categoria) }}">
                            @csrf
                            <x-primary-button type="submit">Generar rol de juegos</x-primary-button>
                        </form>
                    @endif
                </div>
            @else
                <div class="bg-white p-4 shadow-sm rounded-3 d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-3">
                    <p class="text-dark">
                        Fase de jornadas generada, estado: <span class="text-capitalize">{{ str_replace('_', ' ', $fase->estado->value) }}</span>.
                    </p>
                    <div class="d-flex gap-3">
                        <form method="POST" action="{{ route('admin.categorias.rol-de-juegos.generar', $categoria) }}" onsubmit="return confirm('¿Regenerar el rol de juegos? Esto reemplazará el calendario actual.');">
                            @csrf
                            <x-secondary-button type="submit">Regenerar</x-secondary-button>
                        </form>
                        <form method="POST" action="{{ route('admin.categorias.rol-de-juegos.destroy', $categoria) }}" onsubmit="return confirm('¿Eliminar el rol de juegos de esta categoría?');">
                            @csrf
                            @method('DELETE')
                            <x-danger-button type="submit">Eliminar</x-danger-button>
                        </form>
                    </div>
                </div>

                @foreach ($partidosPorJornada as $jornada => $partidos)
                    <div class="bg-white shadow-sm rounded-3">
                        <div class="px-3 py-2 bg-light border-bottom">
                            <h3 class="fw-semibold text-dark">Jornada {{ $jornada }}</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small text-secondary text-uppercase">Partido</th>
                                        <th class="small text-secondary text-uppercase">Fecha / cancha</th>
                                        <th class="small text-secondary text-uppercase">Árbitros</th>
                                        <th class="small text-secondary text-uppercase">Estado</th>
                                        <th ></th>
                                    </tr>
                                </thead>
                                <tbody >
                                    @foreach ($partidos as $partido)
                                        <tr>
                                            <td >
                                                {{ $partido->local->equipo->nombre }} vs {{ $partido->visita->equipo->nombre }}
                                                @if ($partido->es_vuelta)
                                                    <span class="small text-muted">(vuelta)</span>
                                                @endif
                                            </td>
                                            <td >
                                                @if ($partido->fecha_hora)
                                                    {{ $partido->fecha_hora->format('d/m/Y H:i') }}
                                                    @if ($partido->cancha)
                                                        &middot; {{ $partido->cancha }}
                                                    @endif
                                                @else
                                                    <span class="text-muted">Sin programar</span>
                                                @endif
                                            </td>
                                            <td >
                                                @forelse ($partido->asignacionesArbitro as $asignacion)
                                                    <span class="small">{{ $asignacion->arbitro->nombre }} ({{ $asignacion->rol->value }})</span><br>
                                                @empty
                                                    <span class="text-muted">Sin asignar</span>
                                                @endforelse
                                            </td>
                                            <td class="text-capitalize">{{ str_replace('_', ' ', $partido->estado->value) }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.partidos.edit', $partido) }}" class="link-primary">Programar</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif

            <a href="{{ route('admin.categorias.index') }}" class="small link-secondary">&larr; Volver a categorías</a>
        </div>
    </div>
</x-app-layout>
