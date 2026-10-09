<div>
    <x-input-label for="categoria_id" value="Categoría" />
    <select id="categoria_id" name="categoria_id" class="form-select mt-1" required>
        <option value="">Selecciona una categoría</option>
        @foreach ($categorias as $categoria)
            <option value="{{ $categoria->id }}" {{ (int) old('categoria_id', $inscripcion?->categoria_id) === $categoria->id ? 'selected' : '' }}>
                {{ $categoria->nombre }} ({{ $categoria->torneo->nombre }})
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('categoria_id')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="equipo_id" value="Equipo" />
    <select id="equipo_id" name="equipo_id" class="form-select mt-1" required>
        <option value="">Selecciona un equipo</option>
        @foreach ($equipos as $equipo)
            <option value="{{ $equipo->id }}" {{ (int) old('equipo_id', $inscripcion?->equipo_id) === $equipo->id ? 'selected' : '' }}>
                {{ $equipo->nombre }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('equipo_id')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="estado" value="Estado" />
    <select id="estado" name="estado" class="form-select mt-1" required>
        @foreach ($estados as $estado)
            <option value="{{ $estado->value }}" {{ old('estado', $inscripcion?->estado?->value ?? 'activa') === $estado->value ? 'selected' : '' }}>
                {{ ucfirst(str_replace('_', ' ', $estado->value)) }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('estado')" class="mt-2" />
</div>
