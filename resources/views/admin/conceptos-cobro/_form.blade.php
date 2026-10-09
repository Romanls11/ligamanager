<div>
    <x-input-label for="nombre" value="Nombre" />
    <x-text-input id="nombre" name="nombre" type="text" class="mt-1"
        value="{{ old('nombre', $concepto?->nombre ?? '') }}" required autofocus />
    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="categoria_id" value="Categoría (opcional, aplica a todas si se deja vacío)" />
    <select id="categoria_id" name="categoria_id" class="form-select mt-1">
        <option value="">Todas las categorías</option>
        @foreach ($categorias as $categoria)
            <option value="{{ $categoria->id }}" {{ (int) old('categoria_id', $concepto?->categoria_id) === $categoria->id ? 'selected' : '' }}>
                {{ $categoria->nombre }} ({{ $categoria->torneo->nombre }})
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('categoria_id')" class="mt-2" />
</div>

<div class="row g-3 mt-1">
    <div class="col-md-6">
        <x-input-label for="monto" value="Monto" />
        <x-text-input id="monto" name="monto" type="number" step="0.01" min="0" class="mt-1"
            value="{{ old('monto', $concepto?->monto ?? '') }}" required />
        <x-input-error :messages="$errors->get('monto')" class="mt-2" />
    </div>
    <div class="col-md-6">
        <x-input-label for="tipo" value="Tipo" />
        <select id="tipo" name="tipo" class="form-select mt-1" required>
            @foreach ($tipos as $tipo)
                <option value="{{ $tipo->value }}" {{ old('tipo', $concepto?->tipo?->value) === $tipo->value ? 'selected' : '' }}>
                    {{ ucfirst($tipo->value) }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
    </div>
</div>

<div class="mt-3">
    <div class="form-check">
        <input id="automatico" name="automatico" type="checkbox" value="1" class="form-check-input"
            {{ old('automatico', $concepto?->automatico ?? false) ? 'checked' : '' }} />
        <label for="automatico" class="form-check-label">Generar cargo automáticamente</label>
    </div>
    <x-input-error :messages="$errors->get('automatico')" class="mt-2" />
</div>

<div class="row g-3 mt-1">
    <div class="col-md-6">
        <x-input-label for="evento_disparador" value="Evento disparador (si es automático)" />
        <select id="evento_disparador" name="evento_disparador" class="form-select mt-1">
            <option value="">Ninguno</option>
            @foreach ($eventos as $evento)
                <option value="{{ $evento->value }}" {{ old('evento_disparador', $concepto?->evento_disparador?->value) === $evento->value ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_', ' ', $evento->value)) }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('evento_disparador')" class="mt-2" />
    </div>
    <div class="col-md-6">
        <x-input-label for="dias_plazo" value="Días de plazo para pago" />
        <x-text-input id="dias_plazo" name="dias_plazo" type="number" min="0" class="mt-1"
            value="{{ old('dias_plazo', $concepto?->dias_plazo ?? '') }}" />
        <x-input-error :messages="$errors->get('dias_plazo')" class="mt-2" />
    </div>
</div>
