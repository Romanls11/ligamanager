<div>
    <x-input-label for="name" value="Nombre" />
    <x-text-input id="name" name="name" type="text" class="mt-1"
        value="{{ old('name', $usuario?->name ?? '') }}" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="email" value="Correo electrónico" />
    <x-text-input id="email" name="email" type="email" class="mt-1"
        value="{{ old('email', $usuario?->email ?? '') }}" required />
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="password" :value="$usuario ? 'Nueva contraseña (dejar en blanco para no cambiar)' : 'Contraseña'" />
    <x-text-input id="password" name="password" type="password" class="mt-1" {{ $usuario ? '' : 'required' }} />
    <x-input-error :messages="$errors->get('password')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="rol" value="Rol" />
    <select id="rol" name="rol" class="form-select mt-1" required>
        <option value="">Selecciona un rol</option>
        @foreach ($roles as $rol)
            <option value="{{ $rol }}" {{ old('rol', $rolActual) === $rol ? 'selected' : '' }}>
                {{ ucfirst($rol) }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('rol')" class="mt-2" />
</div>
