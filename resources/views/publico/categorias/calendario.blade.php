<x-publico-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">
            {{ $categoria->nombre }} — {{ $categoria->torneo->nombre }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-5xl mx-auto px-3 px-lg-4 d-flex flex-column gap-4">
            @include('publico.categorias._nav')

            @forelse ($partidosPorJornada as $jornada => $partidos)
                <div class="bg-white shadow-sm rounded-3">
                    <div class="px-3 py-2 bg-light border-bottom">
                        <h3 class="fw-semibold text-dark">Jornada {{ $jornada }}</h3>
                    </div>
                    <div >
                        @foreach ($partidos as $partido)
                            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-1 p-3">
                                <span>{{ $partido->local->equipo->nombre }} vs {{ $partido->visita->equipo->nombre }}</span>
                                <span class="small text-secondary">
                                    @if ($partido->goles_local !== null)
                                        <strong>{{ $partido->goles_local }} - {{ $partido->goles_visita }}</strong>
                                    @elseif ($partido->fecha_hora)
                                        {{ $partido->fecha_hora->format('d/m/Y H:i') }}
                                    @else
                                        Por programar
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white p-4 shadow-sm rounded-3 text-secondary">
                    El calendario de esta categoría aún no ha sido publicado.
                </div>
            @endforelse
        </div>
    </div>
</x-publico-layout>
