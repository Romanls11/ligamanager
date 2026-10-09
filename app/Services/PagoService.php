<?php

namespace App\Services;

use App\Enums\EstadoCargo;
use App\Enums\MetodoPago;
use App\Models\AsignacionArbitro;
use App\Models\Cargo;
use App\Models\Pago;
use App\Models\PagoArbitro;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;

class PagoService
{
    public function __construct(
        private readonly FolioReciboService $folios,
        private readonly BloqueoAdeudoService $bloqueos,
    ) {}

    /**
     * Registra un pago (total o parcial) contra un cargo y recalcula su estado.
     */
    public function registrarPago(Cargo $cargo, float $monto, MetodoPago $metodo, ?Carbon $fecha, User $registradoPor): Pago
    {
        if ($cargo->estado === EstadoCargo::Condonado) {
            throw new RuntimeException('Este cargo fue condonado, no admite pagos.');
        }

        if ($cargo->estado === EstadoCargo::Pagado) {
            throw new RuntimeException('Este cargo ya está pagado por completo.');
        }

        if ($monto <= 0) {
            throw new RuntimeException('El monto del pago debe ser mayor a cero.');
        }

        if ($monto > $cargo->saldoPendiente() + 0.01) {
            throw new RuntimeException('El monto excede el saldo pendiente del cargo ($'.number_format($cargo->saldoPendiente(), 2).').');
        }

        $pago = Pago::create([
            'cargo_id' => $cargo->id,
            'monto' => $monto,
            'fecha' => $fecha ?? now(),
            'metodo' => $metodo,
            'folio_recibo' => 'TEMP-'.Str::random(16),
            'registrado_por_user_id' => $registradoPor->id,
        ]);

        $pago->update(['folio_recibo' => $this->folios->generarPara($pago)]);

        $cargo->recalcularEstado();
        $this->bloqueos->evaluar($cargo->inscripcion);

        return $pago->fresh();
    }

    /**
     * Condona (perdona) un cargo pendiente o parcial.
     */
    public function condonarCargo(Cargo $cargo): void
    {
        if ($cargo->estado === EstadoCargo::Pagado) {
            throw new RuntimeException('No se puede condonar un cargo ya pagado.');
        }

        $cargo->update(['estado' => EstadoCargo::Condonado]);
        $this->bloqueos->evaluar($cargo->inscripcion);
    }

    /**
     * Registra el pago (total o parcial) de una asignación de árbitro.
     */
    public function registrarPagoArbitro(AsignacionArbitro $asignacion, float $monto, ?Carbon $fecha, User $registradoPor): PagoArbitro
    {
        $totalPagado = (float) $asignacion->pagos()->sum('monto');
        $saldo = max(0, (float) $asignacion->tarifa - $totalPagado);

        if ($monto <= 0) {
            throw new RuntimeException('El monto del pago debe ser mayor a cero.');
        }

        if ($monto > $saldo + 0.01) {
            throw new RuntimeException('El monto excede el saldo pendiente de esta asignación ($'.number_format($saldo, 2).').');
        }

        return PagoArbitro::create([
            'asignacion_arbitro_id' => $asignacion->id,
            'monto' => $monto,
            'fecha_pago' => $fecha ?? now(),
            'registrado_por_user_id' => $registradoPor->id,
        ]);
    }
}
