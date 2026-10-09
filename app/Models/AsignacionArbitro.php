<?php

namespace App\Models;

use App\Enums\RolAsignacionArbitro;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsignacionArbitro extends Model
{
    use HasFactory;

    protected $table = 'asignaciones_arbitro';

    protected $fillable = [
        'partido_id',
        'arbitro_id',
        'rol',
        'tarifa',
    ];

    protected function casts(): array
    {
        return [
            'rol' => RolAsignacionArbitro::class,
            'tarifa' => 'decimal:2',
        ];
    }

    public function partido(): BelongsTo
    {
        return $this->belongsTo(Partido::class);
    }

    public function arbitro(): BelongsTo
    {
        return $this->belongsTo(Arbitro::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoArbitro::class, 'asignacion_arbitro_id');
    }
}
