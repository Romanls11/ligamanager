<?php

namespace App\Enums;

enum EstadoTorneo: string
{
    case Planeacion = 'planeacion';
    case Activo = 'activo';
    case Finalizado = 'finalizado';
}
