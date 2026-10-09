<x-publico-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">Torneos</h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-6xl mx-auto px-3 px-lg-4 d-flex flex-column gap-4">
            @forelse ($torneos as $torneo)
                <div class="bg-white p-4 shadow-sm rounded-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h5 fw-semibold text-dark">{{ $torneo->nombre }}</h3>
                        <span class="small text-secondary text-capitalize">{{ $torneo->estado->value }}</span>
                    </div>
                    <div class="row g-3">
                        @foreach ($torneo->categorias as $categoria)
                            <div class="col-12 col-sm-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <h4 class="fw-medium text-dark mb-2">{{ $categoria->nombre }}</h4>
                                    <div class="d-flex flex-wrap gap-3 small">
                                        <a href="{{ route('publico.categorias.tabla', $categoria) }}" class="link-primary">Tabla</a>
                                        <a href="{{ route('publico.categorias.calendario', $categoria) }}" class="link-primary">Calendario</a>
                                        <a href="{{ route('publico.categorias.goleo', $categoria) }}" class="link-primary">Goleo</a>
                                        <a href="{{ route('publico.categorias.liguilla', $categoria) }}" class="link-primary">Liguilla</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white p-4 shadow-sm rounded-3 text-secondary">
                    Aún no hay torneos publicados.
                </div>
            @endforelse
        </div>
    </div>
</x-publico-layout>
