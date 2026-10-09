<?php

namespace App\Models;

use App\Enums\EstadoFase;
use App\Enums\TipoFase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fase extends Model
{
    use HasFactory;

    protected $fillable = [
        'categoria_id',
        'tipo',
        'orden',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoFase::class,
            'orden' => 'integer',
            'estado' => EstadoFase::class,
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function partidos(): HasMany
    {
        return $this->hasMany(Partido::class);
    }
}
