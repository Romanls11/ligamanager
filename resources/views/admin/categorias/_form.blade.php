<div>
    <x-input-label for="torneo_id" value="Torneo" />
    <select id="torneo_id" name="torneo_id" class="form-select mt-1" required>
        <option value="">Selecciona un torneo</option>
        @foreach ($torneos as $torneo)
            <option value="{{ $torneo->id }}" {{ (int) old('torneo_id', $categoria?->torneo_id) === $torneo->id ? 'selected' : '' }}>
                {{ $torneo->nombre }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('torneo_id')" class="mt-2" />
</div>

<div class="mt-3">
    <x-input-label for="nombre" value="Nombre" />
    <x-text-input id="nombre" name="nombre" type="text" class="mt-1"
        value="{{ old('nombre', $categoria?->nombre ?? '') }}" required autofocus />
    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
</div>

<div class="row g-3 mt-1">
    <div class="col-md-6">
        <x-input-label for="min_jugadores" value="Mínimo de jugadores" />
        <x-text-input id="min_jugadores" name="min_jugadores" type="number" min="1" class="mt-1"
            value="{{ old('min_jugadores', $categoria?->min_jugadores ?? 11) }}" required />
        <x-input-error :messages="$errors->get('min_jugadores')" class="mt-2" />
    </div>
    <div class="col-md-6">
        <x-input-label for="max_jugadores" value="Máximo de jugadores" />
        <x-text-input id="max_jugadores" name="max_jugadores" type="number" min="1" class="mt-1"
            value="{{ old('max_jugadores', $categoria?->max_jugadores ?? 18) }}" required />
        <x-input-error :messages="$errors->get('max_jugadores')" class="mt-2" />
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-6">
        <x-input-label for="amarillas_para_suspension" value="Amarillas para suspensión" />
        <x-text-input id="amarillas_para_suspension" name="amarillas_para_suspension" type="number" min="1" class="mt-1"
            value="{{ old('amarillas_para_suspension', $categoria?->amarillas_para_suspension ?? 5) }}" required />
        <x-input-error :messages="$errors->get('amarillas_para_suspension')" class="mt-2" />
    </div>
    <div class="col-md-6">
        <x-input-label for="partidos_castigo_roja" value="Partidos de castigo por roja" />
        <x-text-input id="partidos_castigo_roja" name="partidos_castigo_roja" type="number" min="0" class="mt-1"
            value="{{ old('partidos_castigo_roja', $categoria?->partidos_castigo_roja ?? 1) }}" required />
        <x-input-error :messages="$errors->get('partidos_castigo_roja')" class="mt-2" />
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-6">
        <x-input-label for="jornadas_amnistia" value="Jornadas para amnistía" />
        <x-text-input id="jornadas_amnistia" name="jornadas_amnistia" type="number" min="0" class="mt-1"
            value="{{ old('jornadas_amnistia', $categoria?->jornadas_amnistia ?? 0) }}" required />
        <x-input-error :messages="$errors->get('jornadas_amnistia')" class="mt-2" />
    </div>
    <div class="col-md-6">
        <x-input-label for="fecha_cierre_registros" value="Fecha de cierre de registros" />
        <x-text-input id="fecha_cierre_registros" name="fecha_cierre_registros" type="date" class="mt-1"
            value="{{ old('fecha_cierre_registros', $categoria?->fecha_cierre_registros?->format('Y-m-d')) }}" required />
        <x-input-error :messages="$errors->get('fecha_cierre_registros')" class="mt-2" />
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-6">
        <x-input-label for="vueltas" value="Vueltas" />
        <select id="vueltas" name="vueltas" class="form-select mt-1" required>
            @foreach ([1, 2] as $opcion)
                <option value="{{ $opcion }}" {{ (int) old('vueltas', $categoria?->vueltas ?? 2) === $opcion ? 'selected' : '' }}>{{ $opcion }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('vueltas')" class="mt-2" />
    </div>
    <div class="col-md-6">
        <x-input-label for="equipos_a_liguilla" value="Equipos que avanzan a liguilla" />
        <select id="equipos_a_liguilla" name="equipos_a_liguilla" class="form-select mt-1" required>
            @foreach ([0, 2, 4, 8, 16] as $opcion)
                <option value="{{ $opcion }}" {{ (int) old('equipos_a_liguilla', $categoria?->equipos_a_liguilla ?? 4) === $opcion ? 'selected' : '' }}>
                    {{ $opcion === 0 ? 'Sin liguilla' : $opcion }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('equipos_a_liguilla')" class="mt-2" />
    </div>
</div>
