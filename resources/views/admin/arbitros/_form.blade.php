<div>
    <x-input-label for="nombre" value="Nombre" />
    <x-text-input id="nombre" name="nombre" type="text" class="mt-1"
        value="{{ old('nombre', $arbitro?->nombre ?? '') }}" required autofocus />
    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="telefono" value="Teléfono" />
    <x-text-input id="telefono" name="telefono" type="text" class="mt-1"
        value="{{ old('telefono', $arbitro?->telefono ?? '') }}" />
    <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="tarifa_default" value="Tarifa por partido" />
    <x-text-input id="tarifa_default" name="tarifa_default" type="number" step="0.01" min="0" class="mt-1"
        value="{{ old('tarifa_default', $arbitro?->tarifa_default ?? '') }}" required />
    <x-input-error :messages="$errors->get('tarifa_default')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="user_id" value="Cuenta de usuario (para acceso al panel)" />
    <select id="user_id" name="user_id" class="form-select mt-1">
        <option value="">Sin cuenta asociada</option>
        @foreach ($usuarios as $usuario)
            <option value="{{ $usuario->id }}" {{ (int) old('user_id', $arbitro?->user_id) === $usuario->id ? 'selected' : '' }}>
                {{ $usuario->name }} ({{ $usuario->email }})
            </option>
        @endforeach
    </select>
    <p class="small text-secondary mt-1">Solo se listan usuarios con rol "árbitro" que no tengan ya un árbitro vinculado.</p>
    <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
</div>

<div class="mt-3">
    <div class="form-check">
        <input id="activo" name="activo" type="checkbox" value="1" class="form-check-input"
            {{ old('activo', $arbitro?->activo ?? true) ? 'checked' : '' }} />
        <label for="activo" class="form-check-label">Activo</label>
    </div>
    <x-input-error :messages="$errors->get('activo')" class="mt-2" />
</div>
