<x-publico-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">
            {{ $categoria->nombre }} — {{ $categoria->torneo->nombre }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-3xl mx-auto px-3 px-lg-4 d-flex flex-column gap-4">
            @include('publico.categorias._nav')

            <div class="bg-white shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small text-secondary text-uppercase">#</th>
                                <th class="small text-secondary text-uppercase">Jugador</th>
                                <th class="small text-secondary text-uppercase">Equipo</th>
                                <th class="small text-secondary text-uppercase text-center">Goles</th>
                            </tr>
                        </thead>
                        <tbody >
                            @forelse ($goleadores as $i => $fila)
                                <tr>
                                    <td >{{ $i + 1 }}</td>
                                    <td >{{ $fila['registro']->jugador->nombre_completo }}</td>
                                    <td >{{ $fila['registro']->inscripcion->equipo->nombre }}</td>
                                    <td class="text-center fw-semibold">{{ $fila['goles'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary">Aún no hay goles registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-publico-layout>
