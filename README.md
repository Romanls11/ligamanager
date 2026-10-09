# LigaManager

Sistema de administración de torneos de fútbol amateur. Un solo admin controla
torneos, categorías, equipos, jugadores, calendario, árbitros y finanzas;
delegados y árbitros trabajan desde su propio panel (pensado para celular);
cualquier visitante puede consultar tablas de posiciones, calendario, goleo y
liguilla sin necesidad de cuenta.

Construido con **Laravel 11 + Blade + Tailwind** (sin Vue/Inertia), pensado
para correr en hosting compartido sin workers de cola persistentes.

## Roles

| Rol | Qué hace |
|---|---|
| **admin** | Control total: torneos, categorías, equipos, jugadores, inscripciones, calendario, árbitros, conceptos de cobro, finanzas, liguilla, revisión de solicitudes de alta. |
| **delegado** | Ve el estado de cuenta y el calendario de su(s) equipo(s), y **propone** altas de jugadores (el admin las aprueba o rechaza). |
| **árbitro** | Ve sus partidos asignados y captura la cédula arbitral (goles, tarjetas, marcador, default) desde el celular. |
| **público** | Sin cuenta: tabla de posiciones, calendario/resultados, goleo y bracket de liguilla, de solo lectura. |

## Qué hace el sistema por sí solo

Buena parte de las reglas de negocio corren automáticamente vía Eloquent
Observers al capturar la cédula arbitral o registrar un pago — no son pasos
manuales del admin:

- **Amarillas y rojas**: generan el cargo (multa) correspondiente; al llegar
  al umbral de amarillas de la categoría, o por roja directa, se crea la
  suspensión automáticamente.
- **Cumplimiento de suspensiones**: cada partido concluido de un equipo
  descuenta un partido de castigo a sus jugadores suspendidos (el partido
  donde ocurrió la expulsión no cuenta, se sirve a partir del siguiente).
- **Arbitraje**: al programar un partido (fecha/hora) se genera el cargo de
  arbitraje para ambos equipos, una sola vez.
- **Default**: asigna el marcador automático configurado y cobra la multa
  solo al equipo responsable.
- **Adeudo vencido**: un cron diario (`liga:actualizar-bloqueos`) suspende a
  los equipos con cargos vencidos y los reactiva en cuanto pagan — mientras
  está suspendido, no se le pueden dar de alta jugadores.
- **Liguilla**: al generarla, siembra el bracket clásico (1 vs 8, 4 vs 5...)
  a ida y vuelta según la tabla de posiciones; cuando las dos piernas de una
  llave concluyen, calcula el ganador; cuando toda la ronda concluye, genera
  la siguiente sola, hasta la final.

## Stack

- **Laravel 11** (PHP ^8.2), **MySQL**
- **Blade + Tailwind** vía Laravel Breeze (sin Vue/Inertia)
- **spatie/laravel-permission** para roles
- **barryvdh/laravel-dompdf** para recibos y estados de cuenta en PDF
- Sin colas: `QUEUE_CONNECTION=sync`; lo único programado es un cron diario

## Instalación local

```bash
composer install
npm install && npm run build   # o `npm run dev` mientras desarrollas

cp .env.example .env
php artisan key:generate
```

Edita `.env` con los datos de tu MySQL local (crea la base de datos antes), luego:

```bash
php artisan migrate --seed
php artisan serve
```

El seed completo crea un torneo con 2 categorías (una de ellas, Libre, con 8
equipos inscritos y 18 jugadores cada uno), árbitros, conceptos de cobro,
calendario generado y un par de jornadas con resultados de demostración.
Inicia sesión con:

```
admin@liga.test / password
```

⚠️ Son credenciales de desarrollo, están en este repositorio — **nunca las
uses en producción** (ver `DEPLOYMENT.md`, que además explica por qué no hay
que correr el seed completo en un servidor real).

## Tests

```bash
php artisan test
```

La suite corre contra SQLite en memoria (configurado en `phpunit.xml`) y no
toca la base de datos de desarrollo. Cubre las reglas de negocio automáticas
descritas arriba, los CRUDs del admin, finanzas, liguilla y el flujo de
solicitudes de alta.

## Estructura relevante

- `app/Services/` — la lógica de negocio con nombre propio (generación de
  calendario y liguilla, tablas de posiciones/goleo, cédula arbitral, altas
  de jugador, pagos, bloqueo por adeudo).
- `app/Observers/` — reacciones automáticas a eventos de partido/cédula
  (`PartidoObserver`, `EventoPartidoObserver`).
- `app/Http/Controllers/{Admin,Delegado,Arbitro,Publico}/` — un namespace por
  rol; cada uno con sus propias rutas (`routes/{admin,delegado,arbitro,publico}.php`).
- `resources/views/layouts/{app,guest,publico}.blade.php` — layout
  autenticado, de login, y público (usado también por las páginas de error).

## Despliegue

Ver [`DEPLOYMENT.md`](DEPLOYMENT.md) para la guía completa de despliegue en
hosting compartido (document root, `.env` de producción, cron, migraciones,
`storage:link`, respaldos).
