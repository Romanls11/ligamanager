<div>
    <x-input-label for="nombre" value="Nombre" />
    <x-text-input id="nombre" name="nombre" type="text" class="mt-1"
        value="{{ old('nombre', $equipo?->nombre ?? '') }}" required autofocus />
    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="logo" value="Logo" />
    @if ($equipo?->logo)
        <img src="{{ Storage::url($equipo->logo) }}" alt="Logo actual" class="rounded-circle mb-2" style="width: 4rem; height: 4rem; object-fit: cover;">
    @endif
    <input id="logo" name="logo" type="file" accept="image/*" class="mt-1 small text-dark" />
    <x-input-error :messages="$errors->get('logo')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="color_uniforme" value="Color de uniforme" />
    <x-text-input id="color_uniforme" name="color_uniforme" type="text" class="mt-1"
        value="{{ old('color_uniforme', $equipo?->color_uniforme ?? '') }}" />
    <x-input-error :messages="$errors->get('color_uniforme')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="delegado_user_id" value="Delegado" />
    <select id="delegado_user_id" name="delegado_user_id" class="form-select mt-1">
        <option value="">Sin delegado asignado</option>
        @foreach ($delegados as $delegado)
            <option value="{{ $delegado->id }}" {{ (int) old('delegado_user_id', $equipo?->delegado_user_id) === $delegado->id ? 'selected' : '' }}>
                {{ $delegado->name }} ({{ $delegado->email }})
            </option>
        @endforeach
    </select>
    <p class="small text-secondary mt-1">Los delegados se crean desde la sección de Usuarios con rol "delegado".</p>
    <x-input-error :messages="$errors->get('delegado_user_id')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="telefono_contacto" value="Teléfono de contacto" />
    <x-text-input id="telefono_contacto" name="telefono_contacto" type="text" class="mt-1"
        value="{{ old('telefono_contacto', $equipo?->telefono_contacto ?? '') }}" />
    <x-input-error :messages="$errors->get('telefono_contacto')" class="mt-2" />
</div>
