<nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
            <x-application-logo style="width: 1.75rem; height: 1.75rem;" />
            LigaManager
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPrincipal"
            aria-controls="navbarPrincipal" aria-expanded="false" aria-label="Mostrar navegación">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarPrincipal">
            <ul class="navbar-nav me-auto flex-nowrap overflow-x-auto">
                <li class="nav-item">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </li>

                @role('admin')
                    <li class="nav-item">
                        <x-nav-link :href="route('admin.torneos.index')" :active="request()->routeIs('admin.torneos.*')">
                            Torneos
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('admin.categorias.index')" :active="request()->routeIs('admin.categorias.*')">
                            Categorías
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('admin.equipos.index')" :active="request()->routeIs('admin.equipos.*')">
                            Equipos
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('admin.jugadores.index')" :active="request()->routeIs('admin.jugadores.*')">
                            Jugadores
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('admin.inscripciones.index')" :active="request()->routeIs('admin.inscripciones.*')">
                            Inscripciones
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('admin.partidos.index')" :active="request()->routeIs('admin.partidos.*') || request()->routeIs('admin.categorias.rol-de-juegos.*')">
                            Partidos
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('admin.arbitros.index')" :active="request()->routeIs('admin.arbitros.*')">
                            Árbitros
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('admin.conceptos-cobro.index')" :active="request()->routeIs('admin.conceptos-cobro.*')">
                            Conceptos de cobro
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('admin.cargos.index')" :active="request()->routeIs('admin.cargos.*') || request()->routeIs('admin.inscripciones.estado-cuenta.*')">
                            Cargos
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('admin.pagos-arbitro.index')" :active="request()->routeIs('admin.pagos-arbitro.*')">
                            Pagos árbitros
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('admin.solicitudes-alta.index')" :active="request()->routeIs('admin.solicitudes-alta.*')">
                            Solicitudes
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('admin.usuarios.index')" :active="request()->routeIs('admin.usuarios.*')">
                            Usuarios
                        </x-nav-link>
                    </li>
                @endrole

                @role('arbitro')
                    <li class="nav-item">
                        <x-nav-link :href="route('arbitro.partidos.index')" :active="request()->routeIs('arbitro.*')">
                            Mis partidos
                        </x-nav-link>
                    </li>
                @endrole

                @role('delegado')
                    <li class="nav-item">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('delegado.*')">
                            Mis equipos
                        </x-nav-link>
                    </li>
                @endrole
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <span class="nav-link d-flex align-items-center gap-1">
                                {{ Auth::user()->name }}
                                <svg class="flex-shrink-0" width="14" height="14" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        {{ __('Log Out') }}
                                    </button>
                                </form>
                            </li>
                        </x-slot>
                    </x-dropdown>
                </li>
            </ul>
        </div>
    </div>
</nav>
