<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">Nuevo cargo manual</h2>
    </x-slot>

    <div class="py-4">
        <div class="mw-2xl mx-auto px-3 px-lg-4">
            <div class="bg-white p-4 shadow-sm rounded-3">
                <p class="small text-secondary mb-3">
                    Usa esta pantalla para conceptos que no se cobran automáticamente (por ejemplo, la inscripción del equipo).
                    Las multas por tarjetas, arbitraje y default se generan solas.
                </p>
                <form method="POST" action="{{ route('admin.cargos.store') }}">
                    @csrf
                    <div>
                        <x-input-label for="inscripcion_id" value="Equipo" />
                        <select id="inscripcion_id" name="inscripcion_id" class="form-select mt-1" required>
                            <option value="">Selecciona un equipo</option>
                            @foreach ($inscripciones as $inscripcion)
                                <option value="{{ $inscripcion->id }}" {{ (int) old('inscripcion_id') === $inscripcion->id ? 'selected' : '' }}>
                                    {{ $inscripcion->equipo->nombre }} ({{ $inscripcion->categoria->nombre }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('inscripcion_id')" class="mt-2" />
                    </div>

                    <div class="mt-3">
                        <x-input-label for="concepto_cobro_id" value="Concepto" />
                        <select id="concepto_cobro_id" name="concepto_cobro_id" class="form-select mt-1" required>
                            <option value="">Selecciona un concepto</option>
                            @foreach ($conceptos as $concepto)
                                <option value="{{ $concepto->id }}" data-monto="{{ $concepto->monto }}" {{ (int) old('concepto_cobro_id') === $concepto->id ? 'selected' : '' }}>
                                    {{ $concepto->nombre }} (${{ number_format($concepto->monto, 2) }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('concepto_cobro_id')" class="mt-2" />
                    </div>

                    <div class="mt-3">
                        <x-input-label for="monto" value="Monto" />
                        <x-text-input id="monto" name="monto" type="number" step="0.01" min="0.01" class="mt-1"
                            value="{{ old('monto') }}" required />
                        <x-input-error :messages="$errors->get('monto')" class="mt-2" />
                    </div>

                    <div class="mt-3">
                        <x-input-label for="descripcion" value="Descripción (opcional)" />
                        <x-text-input id="descripcion" name="descripcion" type="text" class="mt-1"
                            value="{{ old('descripcion') }}" />
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                    </div>

                    <div class="mt-3">
                        <x-input-label for="fecha_vencimiento" value="Fecha límite de pago (opcional)" />
                        <x-text-input id="fecha_vencimiento" name="fecha_vencimiento" type="date" class="mt-1"
                            value="{{ old('fecha_vencimiento') }}" />
                        <x-input-error :messages="$errors->get('fecha_vencimiento')" class="mt-2" />
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <x-primary-button type="submit">Guardar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('concepto_cobro_id')?.addEventListener('change', function (e) {
            const montoInput = document.getElementById('monto');
            const monto = e.target.selectedOptions[0]?.dataset.monto;
            if (monto && !montoInput.value) {
                montoInput.value = monto;
            }
        });
    </script>
</x-app-layout>
