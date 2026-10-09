<div>
    <x-input-label for="nombre" value="Nombre" />
    <x-text-input id="nombre" name="nombre" type="text" class="mt-1"
        value="{{ old('nombre', $torneo?->nombre ?? '') }}" required autofocus />
    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="fecha_inicio" value="Fecha de inicio" />
    <x-text-input id="fecha_inicio" name="fecha_inicio" type="date" class="mt-1"
        value="{{ old('fecha_inicio', $torneo?->fecha_inicio?->format('Y-m-d') ?? '') }}" required />
    <x-input-error :messages="$errors->get('fecha_inicio')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="estado" value="Estado" />
    <select id="estado" name="estado" class="form-select mt-1" required>
        @foreach ($estados as $estado)
            <option value="{{ $estado->value }}" {{ old('estado', $torneo?->estado?->value ?? '') === $estado->value ? 'selected' : '' }}>
                {{ ucfirst($estado->value) }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('estado')" class="mt-2" />
</div>
