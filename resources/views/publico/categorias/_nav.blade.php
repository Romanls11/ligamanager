<div class="d-flex flex-wrap gap-4 small border-bottom pb-3">
    <a href="{{ route('publico.categorias.tabla', $categoria) }}" class="{{ request()->routeIs('publico.categorias.tabla') ? 'link-primary fw-semibold' : 'link-secondary' }}">Tabla</a>
    <a href="{{ route('publico.categorias.calendario', $categoria) }}" class="{{ request()->routeIs('publico.categorias.calendario') ? 'link-primary fw-semibold' : 'link-secondary' }}">Calendario</a>
    <a href="{{ route('publico.categorias.goleo', $categoria) }}" class="{{ request()->routeIs('publico.categorias.goleo') ? 'link-primary fw-semibold' : 'link-secondary' }}">Goleo</a>
    <a href="{{ route('publico.categorias.liguilla', $categoria) }}" class="{{ request()->routeIs('publico.categorias.liguilla') ? 'link-primary fw-semibold' : 'link-secondary' }}">Liguilla</a>
</div>
