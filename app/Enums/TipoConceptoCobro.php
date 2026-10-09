<?php

namespace App\Enums;

enum TipoConceptoCobro: string
{
    case Inscripcion = 'inscripcion';
    case Arbitraje = 'arbitraje';
    case Multa = 'multa';
    case Otro = 'otro';
}
