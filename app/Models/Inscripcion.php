<?php

namespace App\Models;

use App\Enums\EstadoInscripcion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inscripcion extends Model
{
    use HasFactory;

    protected $table = 'inscripciones';

    protected $fillable = [
        'categoria_id',
        'equipo_id',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoInscripcion::class,
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function registrosJugador(): HasMany
    {
        return $this->hasMany(RegistroJugador::class);
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class);
    }

    public function partidosLocal(): HasMany
    {
        return $this->hasMany(Partido::class, 'local_inscripcion_id');
    }

    public function partidosVisita(): HasMany
    {
        return $this->hasMany(Partido::class, 'visita_inscripcion_id');
    }
}
