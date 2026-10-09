<?php

namespace App\Models;

use App\Enums\EstadoRegistroJugador;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistroJugador extends Model
{
    use HasFactory;

    protected $table = 'registros_jugador';

    protected $fillable = [
        'inscripcion_id',
        'jugador_id',
        'numero_camiseta',
        'amarillas_acumuladas',
        'jornadas_sin_tarjeta',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'numero_camiseta' => 'integer',
            'amarillas_acumuladas' => 'integer',
            'jornadas_sin_tarjeta' => 'integer',
            'estado' => EstadoRegistroJugador::class,
        ];
    }

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class);
    }

    public function jugador(): BelongsTo
    {
        return $this->belongsTo(Jugador::class);
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(EventoPartido::class);
    }

    public function suspensiones(): HasMany
    {
        return $this->hasMany(Suspension::class);
    }
}
