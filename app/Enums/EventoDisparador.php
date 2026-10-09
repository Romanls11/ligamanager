<?php

namespace App\Enums;

enum EventoDisparador: string
{
    case TarjetaAmarilla = 'tarjeta_amarilla';
    case TarjetaRoja = 'tarjeta_roja';
    case DefaultPartido = 'default_partido';
    case PartidoProgramado = 'partido_programado';
}
