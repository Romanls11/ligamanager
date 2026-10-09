<?php

namespace App\Enums;

enum EstadoSolicitudAlta: string
{
    case Pendiente = 'pendiente';
    case Aprobada = 'aprobada';
    case Rechazada = 'rechazada';
}
