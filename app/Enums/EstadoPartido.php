<?php

namespace App\Enums;

enum EstadoPartido: string
{
    case Programado = 'programado';
    case Jugado = 'jugado';
    case DefaultLocal = 'default_local';
    case DefaultVisita = 'default_visita';
    case Suspendido = 'suspendido';
}
