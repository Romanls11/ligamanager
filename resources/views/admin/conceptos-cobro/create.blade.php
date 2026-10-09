<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">Nuevo concepto de cobro</h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-2xl mx-auto px-3 px-lg-4">
            <div class="bg-white p-4 shadow-sm rounded-3">
                <form method="POST" action="{{ route('admin.conceptos-cobro.store') }}">
                    @csrf
                    @include('admin.conceptos-cobro._form', ['concepto' => null])

                    <div class="d-flex justify-content-end mt-4">
                        <x-primary-button>Guardar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
