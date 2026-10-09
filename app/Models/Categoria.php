<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'torneo_id',
        'nombre',
        'min_jugadores',
        'max_jugadores',
        'amarillas_para_suspension',
        'partidos_castigo_roja',
        'jornadas_amnistia',
        'fecha_cierre_registros',
        'vueltas',
        'equipos_a_liguilla',
    ];

    protected function casts(): array
    {
        return [
            'min_jugadores' => 'integer',
            'max_jugadores' => 'integer',
            'amarillas_para_suspension' => 'integer',
            'partidos_castigo_roja' => 'integer',
            'jornadas_amnistia' => 'integer',
            'fecha_cierre_registros' => 'date',
            'vueltas' => 'integer',
            'equipos_a_liguilla' => 'integer',
        ];
    }

    public function torneo(): BelongsTo
    {
        return $this->belongsTo(Torneo::class);
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }

    public function fases(): HasMany
    {
        return $this->hasMany(Fase::class);
    }

    public function conceptosCobro(): HasMany
    {
        return $this->hasMany(ConceptoCobro::class);
    }
}
