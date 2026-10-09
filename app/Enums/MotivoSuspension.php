<?php

namespace App\Enums;

enum MotivoSuspension: string
{
    case RojaDirecta = 'roja_directa';
    case AcumulacionAmarillas = 'acumulacion_amarillas';
    case Manual = 'manual';
}
