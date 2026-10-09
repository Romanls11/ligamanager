<?php

namespace App\Models;

use App\Enums\EstadoSolicitudAlta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudAlta extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_alta';

    protected $fillable = [
        'inscripcion_id',
        'jugador_id',
        'numero_camiseta',
        'estado',
        'motivo_rechazo',
        'solicitado_por_user_id',
        'revisado_por_user_id',
        'revisado_at',
    ];

    protected function casts(): array
    {
        return [
            'numero_camiseta' => 'integer',
            'estado' => EstadoSolicitudAlta::class,
            'revisado_at' => 'datetime',
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

    public function solicitadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitado_por_user_id');
    }

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por_user_id');
    }
}
