<x-publico-layout>
    <div class="py-5 text-center">
        <div class="container" style="max-width: 28rem;">
            <p class="display-4 mb-3">🚫</p>
            <h1 class="h3 fw-semibold text-dark">Acceso no autorizado</h1>
            <p class="text-secondary mt-2">
                {{ $exception->getMessage() ?: 'No tienes permiso para ver esta página.' }}
            </p>
            <a href="{{ route('publico.home') }}" class="btn btn-dark btn-sm mt-3">
                Volver al inicio
            </a>
        </div>
    </div>
</x-publico-layout>
