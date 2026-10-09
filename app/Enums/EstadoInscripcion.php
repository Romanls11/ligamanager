<?php

namespace App\Enums;

enum EstadoInscripcion: string
{
    case Activa = 'activa';
    case SuspendidaPorAdeudo = 'suspendida_por_adeudo';
    case Retirada = 'retirada';
}
