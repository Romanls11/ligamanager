<div>
    <x-input-label for="nombre_completo" value="Nombre completo" />
    <x-text-input id="nombre_completo" name="nombre_completo" type="text" class="mt-1"
        value="{{ old('nombre_completo', $jugador?->nombre_completo ?? '') }}" required autofocus />
    <x-input-error :messages="$errors->get('nombre_completo')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
    <x-text-input id="fecha_nacimiento" name="fecha_nacimiento" type="date" class="mt-1"
        value="{{ old('fecha_nacimiento', $jugador?->fecha_nacimiento?->format('Y-m-d')) }}" required />
    <x-input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="foto" value="Foto" />
    @if ($jugador?->foto)
        <img src="{{ Storage::url($jugador->foto) }}" alt="Foto actual" class="rounded-circle mb-2" style="width: 4rem; height: 4rem; object-fit: cover;">
    @endif
    <input id="foto" name="foto" type="file" accept="image/*" class="mt-1 small text-dark" />
    <x-input-error :messages="$errors->get('foto')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="curp" value="CURP" />
    <x-text-input id="curp" name="curp" type="text" class="mt-1 text-uppercase"
        value="{{ old('curp', $jugador?->curp ?? '') }}" maxlength="18" />
    <x-input-error :messages="$errors->get('curp')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="telefono" value="Teléfono" />
    <x-text-input id="telefono" name="telefono" type="text" class="mt-1"
        value="{{ old('telefono', $jugador?->telefono ?? '') }}" />
    <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
</div>
