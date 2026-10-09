<?php

namespace App\Models;

use App\Enums\TipoEventoPartido;
use App\Observers\EventoPartidoObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy([EventoPartidoObserver::class])]
class EventoPartido extends Model
{
    use HasFactory;

    protected $table = 'eventos_partido';

    protected $fillable = [
        'partido_id',
        'registro_jugador_id',
        'tipo',
        'minuto',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoEventoPartido::class,
        ];
    }

    public function partido(): BelongsTo
    {
        return $this->belongsTo(Partido::class);
    }

    public function registroJugador(): BelongsTo
    {
        return $this->belongsTo(RegistroJugador::class, 'registro_jugador_id');
    }

    public function cargo(): HasOne
    {
        return $this->hasOne(Cargo::class);
    }

    public function suspension(): HasOne
    {
        return $this->hasOne(Suspension::class);
    }
}
