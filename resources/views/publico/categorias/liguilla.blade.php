<x-publico-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">
            {{ $categoria->nombre }} — {{ $categoria->torneo->nombre }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-5xl mx-auto px-3 px-lg-4 d-flex flex-column gap-4">
            @include('publico.categorias._nav')

            @forelse ($rondas as $ronda)
                <div class="bg-white shadow-sm rounded-3">
                    <div class="px-3 py-2 bg-light border-bottom">
                        <h3 class="fw-semibold text-dark text-capitalize">{{ $ronda->tipo->value }}</h3>
                    </div>
                    <div >
                        @foreach ($ronda->partidos as $partido)
                            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-1 p-3">
                                <span>
                                    {{ $partido->local->equipo->nombre }} vs {{ $partido->visita->equipo->nombre }}
                                    <span class="small text-muted">({{ $partido->es_vuelta ? 'vuelta' : 'ida' }})</span>
                                </span>
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
                    Esta categoría aún no tiene liguilla.
                </div>
            @endforelse
        </div>
    </div>
</x-publico-layout>
