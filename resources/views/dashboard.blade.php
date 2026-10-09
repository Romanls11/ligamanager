<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid px-3 px-lg-4">
            @role('admin')
                <div class="row g-3">
                    @php
                        $accesos = [
                            ['ruta' => 'admin.torneos.index', 'titulo' => 'Torneos', 'descripcion' => 'Administra los torneos'],
                            ['ruta' => 'admin.categorias.index', 'titulo' => 'Categorías', 'descripcion' => 'Configura categorías por torneo'],
                            ['ruta' => 'admin.equipos.index', 'titulo' => 'Equipos', 'descripcion' => 'Administra equipos y delegados'],
                            ['ruta' => 'admin.jugadores.index', 'titulo' => 'Jugadores', 'descripcion' => 'Catálogo global de jugadores'],
                            ['ruta' => 'admin.inscripciones.index', 'titulo' => 'Inscripciones', 'descripcion' => 'Plantillas por categoría'],
                            ['ruta' => 'admin.arbitros.index', 'titulo' => 'Árbitros', 'descripcion' => 'Administra árbitros'],
                            ['ruta' => 'admin.conceptos-cobro.index', 'titulo' => 'Conceptos de cobro', 'descripcion' => 'Configura montos y cargos automáticos'],
                            ['ruta' => 'admin.cargos.index', 'titulo' => 'Cargos', 'descripcion' => 'Cargos manuales y estado de cuenta'],
                            ['ruta' => 'admin.pagos-arbitro.index', 'titulo' => 'Pagos a árbitros', 'descripcion' => 'Registra pagos por partido arbitrado'],
                            ['ruta' => 'admin.solicitudes-alta.index', 'titulo' => 'Solicitudes de alta', 'descripcion' => 'Altas propuestas por los delegados'],
                            ['ruta' => 'admin.usuarios.index', 'titulo' => 'Usuarios', 'descripcion' => 'Cuentas y roles del sistema'],
                        ];
                    @endphp

                    @foreach ($accesos as $acceso)
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="{{ route($acceso['ruta']) }}" class="d-block bg-white p-4 shadow-sm rounded-3 h-100 text-decoration-none hover-shadow">
                                <h3 class="h6 fw-semibold text-dark mb-1">{{ $acceso['titulo'] }}</h3>
                                <p class="small text-secondary mb-0">{{ $acceso['descripcion'] }}</p>
                            </a>
                        </div>
                    @endforeach
                </div>
            @elseif (auth()->user()->hasRole('delegado'))
                @php
                    $misEquipos = \App\Models\Inscripcion::whereHas('equipo', fn ($q) => $q->where('delegado_user_id', auth()->id()))
                        ->with(['equipo', 'categoria.torneo'])
                        ->get();
                @endphp

                <div class="d-flex flex-column gap-3">
                    @forelse ($misEquipos as $inscripcion)
                        <div class="bg-white p-4 shadow-sm rounded-3">
                            <h3 class="h6 fw-semibold text-dark mb-1">{{ $inscripcion->equipo->nombre }}</h3>
                            <p class="small text-secondary mb-3">{{ $inscripcion->categoria->nombre }} &middot; {{ $inscripcion->categoria->torneo->nombre }}</p>
                            <div class="d-flex flex-wrap gap-3 small">
                                <a href="{{ route('delegado.inscripciones.estado-cuenta.show', $inscripcion) }}" class="link-primary">Estado de cuenta</a>
                                <a href="{{ route('delegado.inscripciones.rol-de-juegos.show', $inscripcion) }}" class="link-primary">Rol de juegos</a>
                                <a href="{{ route('delegado.inscripciones.jugadores.index', $inscripcion) }}" class="link-primary">Jugadores</a>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white shadow-sm rounded-3">
                            <div class="p-4 text-dark">Aún no tienes ningún equipo asignado. Contacta al administrador.</div>
                        </div>
                    @endforelse
                </div>
            @else
                <div class="bg-white shadow-sm rounded-3">
                    <div class="p-4 text-dark">
                        Bienvenido a LigaManager. Tu panel se habilitará próximamente.
                    </div>
                </div>
            @endrole
        </div>
    </div>
</x-app-layout>
