<?php

namespace App\Models;

use App\Enums\EventoDisparador;
use App\Enums\TipoConceptoCobro;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConceptoCobro extends Model
{
    use HasFactory;

    protected $table = 'conceptos_cobro';

    protected $fillable = [
        'categoria_id',
        'nombre',
        'monto',
        'tipo',
        'automatico',
        'evento_disparador',
        'dias_plazo',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'tipo' => TipoConceptoCobro::class,
            'automatico' => 'boolean',
            'evento_disparador' => EventoDisparador::class,
            'dias_plazo' => 'integer',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class);
    }

    /**
     * Conceptos automáticos aplicables a una categoría para un evento disparador dado,
     * priorizando el concepto específico de la categoría sobre uno global (categoria_id nulo).
     */
    public function scopeAutomaticoPara(Builder $query, int $categoriaId, EventoDisparador $disparador): Builder
    {
        return $query->where('automatico', true)
            ->where('evento_disparador', $disparador)
            ->where(fn ($q) => $q->whereNull('categoria_id')->orWhere('categoria_id', $categoriaId))
            ->orderByRaw('categoria_id IS NULL');
    }
}
