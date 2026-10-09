<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">Editar torneo</h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-2xl mx-auto px-3 px-lg-4">
            <div class="bg-white p-4 shadow-sm rounded-3">
                <form method="POST" action="{{ route('admin.torneos.update', $torneo) }}">
                    @csrf
                    @method('PUT')
                    @include('admin.torneos._form', ['torneo' => $torneo])

                    <div class="d-flex justify-content-end mt-4">
                        <x-primary-button>Actualizar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
