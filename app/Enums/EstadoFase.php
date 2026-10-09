<?php

namespace App\Enums;

enum EstadoFase: string
{
    case Pendiente = 'pendiente';
    case EnCurso = 'en_curso';
    case Finalizada = 'finalizada';
}
