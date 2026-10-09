<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">
            Liguilla — {{ $categoria->nombre }} ({{ $categoria->torneo->nombre }})
        </h2>
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

            <div class="bg-white shadow-sm rounded-3">
                <div class="px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center">
                    <h3 class="fw-semibold text-dark">Tabla general</h3>
                    <span class="small text-secondary">Clasifican los primeros {{ $categoria->equipos_a_liguilla }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small text-secondary text-uppercase">#</th>
                                <th class="small text-secondary text-uppercase">Equipo</th>
                                <th class="small text-secondary text-uppercase text-center">PJ</th>
                                <th class="small text-secondary text-uppercase text-center">PG</th>
                                <th class="small text-secondary text-uppercase text-center">PE</th>
                                <th class="small text-secondary text-uppercase text-center">PP</th>
                                <th class="small text-secondary text-uppercase text-center">GF</th>
                                <th class="small text-secondary text-uppercase text-center">GC</th>
                                <th class="small text-secondary text-uppercase text-center">DG</th>
                                <th class="small text-secondary text-uppercase text-center">PTS</th>
                            </tr>
                        </thead>
                        <tbody >
                            @foreach ($tabla as $i => $fila)
                                <tr class="{{ in_array($fila['inscripcion']->id, $clasificados) ? 'table-success' : '' }}">
                                    <td >{{ $i + 1 }}</td>
                                    <td >{{ $fila['inscripcion']->equipo->nombre }}</td>
                                    <td class="text-center">{{ $fila['pj'] }}</td>
                                    <td class="text-center">{{ $fila['pg'] }}</td>
                                    <td class="text-center">{{ $fila['pe'] }}</td>
                                    <td class="text-center">{{ $fila['pp'] }}</td>
                                    <td class="text-center">{{ $fila['gf'] }}</td>
                                    <td class="text-center">{{ $fila['gc'] }}</td>
                                    <td class="text-center">{{ $fila['dg'] }}</td>
                                    <td class="text-center fw-semibold">{{ $fila['pts'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($rondas->isEmpty())
                <div class="bg-white p-4 shadow-sm rounded-3">
                    @if ($categoria->equipos_a_liguilla > 0)
                        <form method="POST" action="{{ route('admin.categorias.liguilla.generar', $categoria) }}" onsubmit="return confirm('¿Generar la liguilla con los equipos clasificados?');">
                            @csrf
                            <x-primary-button type="submit">Generar liguilla</x-primary-button>
                        </form>
                    @else
                        <p class="text-secondary">Esta categoría no tiene liguilla configurada.</p>
                    @endif
                </div>
            @else
                @foreach ($rondas as $ronda)
                    <div class="bg-white shadow-sm rounded-3">
                        <div class="px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center">
                            <h3 class="fw-semibold text-dark text-capitalize">{{ $ronda->tipo->value }}</h3>
                            <span class="small text-secondary text-capitalize">{{ str_replace('_', ' ', $ronda->estado->value) }}</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small text-secondary text-uppercase">Llave</th>
                                        <th class="small text-secondary text-uppercase">Partido</th>
                                        <th class="small text-secondary text-uppercase">Marcador</th>
                                        <th class="small text-secondary text-uppercase">Estado</th>
                                        <th ></th>
                                    </tr>
                                </thead>
                                <tbody >
                                    @foreach ($ronda->partidos as $partido)
                                        <tr>
                                            <td >{{ $partido->llave }}</td>
                                            <td >
                                                {{ $partido->local->equipo->nombre }} vs {{ $partido->visita->equipo->nombre }}
                                                <span class="small text-muted">({{ $partido->es_vuelta ? 'vuelta' : 'ida' }})</span>
                                            </td>
                                            <td >
                                                @if ($partido->goles_local !== null)
                                                    {{ $partido->goles_local }} - {{ $partido->goles_visita }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-capitalize">{{ str_replace('_', ' ', $partido->estado->value) }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.partidos.edit', $partido) }}" class="link-primary">Programar</a>
                                                <a href="{{ route('admin.partidos.cedula.edit', $partido) }}" class="link-success">Cédula</a>
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
