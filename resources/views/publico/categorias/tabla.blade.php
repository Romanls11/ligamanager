<x-publico-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">
            {{ $categoria->nombre }} — {{ $categoria->torneo->nombre }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-5xl mx-auto px-3 px-lg-4 d-flex flex-column gap-4">
            @include('publico.categorias._nav')

            <div class="bg-white shadow-sm rounded-3">
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
                            @forelse ($tabla as $i => $fila)
                                <tr>
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
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-secondary">Aún no hay partidos concluidos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-publico-layout>
