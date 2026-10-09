# Guía de despliegue — LigaManager (hosting compartido)

Esta guía asume un hosting compartido típico (cPanel o similar) sin acceso root,
sin workers de cola persistentes, y con cron disponible. Si tu hosting ofrece
SSH, todos los comandos `php artisan ...` se corren directamente; si no,
casi todos los paneles (cPanel → "Terminal" o "Cron Jobs") permiten ejecutarlos igual.

## 1. Requisitos del servidor

- PHP **8.2 o superior** (el proyecto se probó en 8.4).
- Extensiones: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`,
  `json`, `bcmath`, `fileinfo`, `gd` (dompdf y las fotos de jugador/logo de
  equipo las necesitan). La mayoría de hostings las traen activas por defecto;
  verifícalo en cPanel → "Select PHP Version" → "Extensions".
- MySQL 5.7+ / MariaDB 10.3+.
- Cron (para el único job que necesita el sistema, ver sección 6).

## 2. Preparar el release en tu máquina

No subas el repo tal cual: sube una build ya lista con dependencias y assets compilados.

```bash
composer install --optimize-autoloader --no-dev
npm install && npm run build
```

Esto genera `vendor/` (sin paquetes de desarrollo como Pint, Sail, Faker) y
`public/build/` (CSS/JS ya compilados — el hosting no va a correr `npm`).

## 3. Subir los archivos

Sube **todo el proyecto** (incluyendo `vendor/` y `public/build/` ya generados)
excepto: `.git/`, `node_modules/`, `.env` (lo creas directo en el servidor), y
`storage/logs/*`, `storage/framework/cache|sessions|views/*` (se regeneran solos).

### Dónde apunta el document root

- **Si tu hosting te deja elegir el document root de un dominio/subdominio**
  (la mayoría de cPanel modernos sí): apúntalo directo a la carpeta `public/`
  del proyecto. Es la forma correcta, sin trucos.
- **Si solo tienes una carpeta raíz fija** (`public_html/` sirviendo todo):
  sube el proyecto completo a una carpeta hermana (p. ej. `app-torneo/`, fuera
  de `public_html/` si es posible, o dentro pero sin exponerla) y copia el
  contenido de `app-torneo/public/` dentro de `public_html/`. Luego edita
  `public_html/index.php` para que las dos líneas `require` apunten a la
  ubicación real de `app-torneo/vendor/autoload.php` y
  `app-torneo/bootstrap/app.php`.

En ambos casos, **la carpeta del proyecto que NO es `public/` no debe quedar
accesible por URL** (ni `.env`, ni `app/`, ni `vendor/`).

## 4. Base de datos

1. Crea la base de datos y un usuario MySQL dedicado desde el panel de tu hosting.
2. Anota host, puerto, nombre de BD, usuario y contraseña — los vas a necesitar en el `.env`.

## 5. Archivo `.env` de producción

Crea `.env` directamente en el servidor (nunca subas el `.env` de desarrollo).
Genera uno nuevo a partir de `.env.example` y ajusta al menos:

```env
APP_NAME=LigaManager
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com
APP_LOCALE=es
APP_FALLBACK_LOCALE=es

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<el nombre real>
DB_USERNAME=<el usuario real>
DB_PASSWORD=<la contraseña real>

QUEUE_CONNECTION=sync
SESSION_DRIVER=file
CACHE_STORE=file
FILESYSTEM_DISK=public
```

Genera una `APP_KEY` nueva propia de este entorno (no reutilices la de desarrollo):

```bash
php artisan key:generate
```

`APP_DEBUG=false` es obligatorio: con `true` en producción, cualquier error
expone rutas del servidor, queries y variables de entorno a quien lo vea.

## 6. Cron (lo único que hay que programar)

El sistema no usa workers de cola. Lo único que necesita correr por tiempo es
la revisión diaria de adeudos vencidos (`liga:actualizar-bloqueos`, que
suspende o reactiva equipos según sus cargos vencidos). Se dispara sola a
través del scheduler de Laravel — en cPanel → "Cron Jobs" agrega **una sola
línea**, cada minuto:

```
* * * * * cd /ruta/completa/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

No hace falta programar nada más: `routes/console.php` ya define que
`liga:actualizar-bloqueos` corre una vez al día dentro de ese scheduler.

## 7. Migraciones y datos iniciales

```bash
php artisan migrate --force
```

(`--force` es necesario porque `APP_ENV=production` pide confirmación interactiva).

**No corras `php artisan db:seed` completo.** El seeder por defecto
(`DatabaseSeeder`) crea 8 equipos falsos, un calendario completo y resultados
de demostración — está pensado solo para desarrollo. Para producción corre
únicamente lo necesario para arrancar:

```bash
php artisan db:seed --class=RolesSeeder
php artisan db:seed --class=AdminUserSeeder
```

Esto crea los 3 roles y un usuario administrador con correo
`admin@liga.test` y contraseña `password`.

⚠️ **Cambia esa contraseña de inmediato** — inicia sesión y actualízala desde
tu perfil, o créate tu propio usuario admin desde la sección "Usuarios" y
elimina/inhabilita el genérico. Son credenciales públicas (están en este
mismo repositorio) y cualquiera que lo sepa puede entrar como admin.

## 8. Storage público (logos y fotos)

Los logos de equipo y fotos de jugador se guardan en `storage/app/public`. Crea
el enlace simbólico para que sean accesibles por URL:

```bash
php artisan storage:link
```

Si tu hosting no permite symlinks (algunos compartidos muy restrictivos no
dejan), copia manualmente `storage/app/public/` dentro de `public/storage/`
después de cada actualización, o pide a soporte que active `symlink()`.

## 9. Optimización (recomendado, no obligatorio)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Si vuelves a tocar `.env` o rutas después de esto, corre
`php artisan optimize:clear` y vuelve a cachear — si no, verás configuración vieja.

## 10. Permisos de archivos

`storage/` y `bootstrap/cache/` deben ser escribibles por el proceso PHP
(normalmente el propio usuario del hosting ya es dueño, pero si ves errores
de "Permission denied" en `storage/logs/laravel.log`):

```bash
chmod -R 775 storage bootstrap/cache
```

## 11. Verificación post-despliegue

- `https://tu-dominio.com/` carga el portal público (torneos/categorías) sin pedir login.
- `https://tu-dominio.com/login` funciona y puedes entrar con el admin.
- `https://tu-dominio.com/up` responde 200 (endpoint de salud de Laravel).
- Cambiaste la contraseña del admin genérico.
- El cron está dado de alta (revisa en unos minutos que `storage/logs/laravel.log`
  no muestre errores del scheduler).
- Sube un logo de equipo de prueba y confirma que se ve (valida el `storage:link`).

## 12. Actualizaciones futuras

Cada vez que despliegues cambios de código:

```bash
composer install --optimize-autoloader --no-dev   # si cambiaron dependencias
npm run build                                      # si cambiaron vistas/CSS/JS
php artisan migrate --force                        # si hay migraciones nuevas
php artisan optimize:clear && php artisan optimize # limpia y recachea config/rutas/vistas
```

## 13. Respaldos

El hosting compartido rara vez incluye respaldos automáticos confiables de
MySQL. Configura, aunque sea, un backup diario simple vía cron:

```
0 3 * * * mysqldump -u<usuario> -p'<password>' <basededatos> | gzip > /ruta/backups/liga-$(date +\%F).sql.gz
```

Guarda esos backups fuera del propio hosting cuando puedas (descarga periódica),
para no perderlos si el hosting falla.
