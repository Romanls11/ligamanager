<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Marcador por default
    |--------------------------------------------------------------------------
    |
    | Marcador que se asigna automáticamente al equipo que NO dio el default
    | cuando un partido se marca como default_local o default_visita.
    |
    */
    'marcador_default' => [
        'goles_favor' => 3,
        'goles_contra' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Bloqueo por adeudo
    |--------------------------------------------------------------------------
    |
    | Si está activo, las inscripciones con cargos vencidos (fecha_vencimiento
    | pasada y estado pendiente/parcial) se marcan como suspendida_por_adeudo.
    |
    */
    'bloqueo_por_adeudo' => true,

    /*
    |--------------------------------------------------------------------------
    | Peso de fair play
    |--------------------------------------------------------------------------
    |
    | Usado como criterio de desempate en la tabla general: menor puntaje
    | de tarjetas es mejor.
    |
    */
    'peso_fair_play' => [
        'amarilla' => 1,
        'roja' => 2,
    ],

];
