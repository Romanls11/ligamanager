<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoArbitro extends Model
{
    use HasFactory;

    protected $table = 'pagos_arbitro';

    protected $fillable = [
        'asignacion_arbitro_id',
        'monto',
        'fecha_pago',
        'registrado_por_user_id',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_pago' => 'date',
        ];
    }

    public function asignacionArbitro(): BelongsTo
    {
        return $this->belongsTo(AsignacionArbitro::class, 'asignacion_arbitro_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_user_id');
    }
}
