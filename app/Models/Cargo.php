<?php

namespace App\Models;

use App\Enums\EstadoCargo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cargo extends Model
{
    use HasFactory;

    protected $fillable = [
        'inscripcion_id',
        'concepto_cobro_id',
        'partido_id',
        'evento_partido_id',
        'monto',
        'descripcion',
        'estado',
        'fecha_vencimiento',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'estado' => EstadoCargo::class,
            'fecha_vencimiento' => 'date',
        ];
    }

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class);
    }

    public function conceptoCobro(): BelongsTo
    {
        return $this->belongsTo(ConceptoCobro::class);
    }

    public function partido(): BelongsTo
    {
        return $this->belongsTo(Partido::class);
    }

    public function eventoPartido(): BelongsTo
    {
        return $this->belongsTo(EventoPartido::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento !== null
            && $this->fecha_vencimiento->isPast()
            && in_array($this->estado, [EstadoCargo::Pendiente, EstadoCargo::Parcial], true);
    }

    public function totalPagado(): float
    {
        return (float) $this->pagos()->sum('monto');
    }

    public function saldoPendiente(): float
    {
        return max(0, (float) $this->monto - $this->totalPagado());
    }

    /**
     * Recalcula el estado (pendiente/parcial/pagado) según lo pagado hasta ahora.
     * No toca cargos condonados: la condonación es una decisión manual del admin.
     */
    public function recalcularEstado(): void
    {
        if ($this->estado === EstadoCargo::Condonado) {
            return;
        }

        $totalPagado = $this->totalPagado();

        $this->estado = match (true) {
            $totalPagado >= (float) $this->monto => EstadoCargo::Pagado,
            $totalPagado > 0 => EstadoCargo::Parcial,
            default => EstadoCargo::Pendiente,
        };

        $this->save();
    }
}
