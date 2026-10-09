<?php

namespace App\Models;

use App\Enums\MotivoSuspension;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suspension extends Model
{
    use HasFactory;

    protected $table = 'suspensiones';

    protected $fillable = [
        'registro_jugador_id',
        'motivo',
        'partidos_castigo',
        'partidos_cumplidos',
        'cumplida',
        'partido_origen_id',
        'evento_partido_id',
    ];

    protected function casts(): array
    {
        return [
            'motivo' => MotivoSuspension::class,
            'partidos_castigo' => 'integer',
            'partidos_cumplidos' => 'integer',
            'cumplida' => 'boolean',
        ];
    }

    public function registroJugador(): BelongsTo
    {
        return $this->belongsTo(RegistroJugador::class, 'registro_jugador_id');
    }

    public function partidoOrigen(): BelongsTo
    {
        return $this->belongsTo(Partido::class, 'partido_origen_id');
    }

    public function eventoPartido(): BelongsTo
    {
        return $this->belongsTo(EventoPartido::class);
    }
}
