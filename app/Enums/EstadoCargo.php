<?php

namespace App\Enums;

enum EstadoCargo: string
{
    case Pendiente = 'pendiente';
    case Pagado = 'pagado';
    case Parcial = 'parcial';
    case Condonado = 'condonado';
}
