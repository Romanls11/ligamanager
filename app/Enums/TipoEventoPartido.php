<?php

namespace App\Enums;

enum TipoEventoPartido: string
{
    case Gol = 'gol';
    case Autogol = 'autogol';
    case Amarilla = 'amarilla';
    case Roja = 'roja';
}
