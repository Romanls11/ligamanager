<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">
            Rol de juegos — {{ $inscripcion->equipo->nombre }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-4xl mx-auto px-3 px-lg-4 d-flex flex-column gap-4">
            @forelse ($partidosPorJornada as $jornada => $partidos)
                <div class="bg-white shadow-sm rounded-3">
                    <div class="px-3 py-2 bg-light border-bottom">
                        <h3 class="fw-semibold text-dark">Jornada {{ $jornada }}</h3>
                    </div>
                    <div >
                        @foreach ($partidos as $partido)
                            @php
                                $esMiEquipo = in_array($inscripcion->id, [$partido->local_inscripcion_id, $partido->visita_inscripcion_id]);
                            @endphp
                            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-1 p-3 {{ $esMiEquipo ? 'bg-primary-subtle' : '' }}">
                                <span>{{ $partido->local->equipo->nombre }} vs {{ $partido->visita->equipo->nombre }}</span>
                                <span class="small text-secondary">
                                    @if ($partido->goles_local !== null)
                                        <strong>{{ $partido->goles_local }} - {{ $partido->goles_visita }}</strong>
                                    @elseif ($partido->fecha_hora)
                                        {{ $partido->fecha_hora->format('d/m/Y H:i') }} @if ($partido->cancha) &middot; {{ $partido->cancha }} @endif
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
                    El calendario de tu categoría aún no ha sido generado.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
