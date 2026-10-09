<?php

namespace App\Enums;

enum TipoFase: string
{
    case Jornadas = 'jornadas';
    case Octavos = 'octavos';
    case Cuartos = 'cuartos';
    case Semifinal = 'semifinal';
    case Final = 'final';
}
