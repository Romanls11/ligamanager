<?php

namespace App\Models;

use App\Enums\EstadoPartido;
use App\Observers\PartidoObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([PartidoObserver::class])]
class Partido extends Model
{
    use HasFactory;

    protected $fillable = [
        'fase_id',
        'jornada',
        'local_inscripcion_id',
        'visita_inscripcion_id',
        'fecha_hora',
        'cancha',
        'goles_local',
        'goles_visita',
        'estado',
        'llave',
        'es_vuelta',
    ];

    protected function casts(): array
    {
        return [
            'jornada' => 'integer',
            'fecha_hora' => 'datetime',
            'goles_local' => 'integer',
            'goles_visita' => 'integer',
            'estado' => EstadoPartido::class,
            'llave' => 'integer',
            'es_vuelta' => 'boolean',
        ];
    }

    public function fase(): BelongsTo
    {
        return $this->belongsTo(Fase::class);
    }

    public function local(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'local_inscripcion_id');
    }

    public function visita(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'visita_inscripcion_id');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(EventoPartido::class);
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class);
    }

    public function asignacionesArbitro(): HasMany
    {
        return $this->hasMany(AsignacionArbitro::class);
    }

    public function suspensionesOrigen(): HasMany
    {
        return $this->hasMany(Suspension::class, 'partido_origen_id');
    }
}
