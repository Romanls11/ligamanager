<?php

namespace App\Services;

use App\Models\Pago;

class FolioReciboService
{
    /**
     * Folio secuencial legible basado en el id del pago, único por construcción.
     */
    public function generarPara(Pago $pago): string
    {
        return 'REC-'.str_pad((string) $pago->id, 6, '0', STR_PAD_LEFT);
    }
}
