# Guía de despliegue — LigaManager en Render + Aiven

Esta guía es para el combo **Render** (hosting de la app, vía Docker, nivel
gratuito) + **Aiven** (MySQL gratis para siempre). A diferencia de
`DEPLOYMENT.md` (pensada para hosting compartido tipo cPanel), aquí no hay
`.env` en el servidor ni cron tradicional: todo se configura como variables
de entorno desde el dashboard de Render, y el contenedor (`Dockerfile` en la
raíz del repo) arranca, migra y sirve la app solo.

## 0. Qué ya está listo en el repo

- `Dockerfile` — imagen de 3 etapas: compila assets (Vite/Bootstrap) con
  Node, instala dependencias PHP con Composer, y arma la imagen final con
  `php-fpm` + `nginx` corriendo bajo `supervisord`.
- `docker/entrypoint.sh` — en cada arranque del contenedor: corre
  migraciones, siembra roles + el usuario admin (idempotente, no duplica
  nada en reinicios), crea el symlink de storage, y cachea config/rutas/vistas.
- `render.yaml` — define el servicio y la lista de variables de entorno que
  hay que rellenar en el dashboard.
- Ruta `/cron/{token}` — sustituto del cron del sistema (Render free no lo
  tiene). Ver paso 6.

## 1. Crear la base de datos en Aiven

1. Crea una cuenta en [aiven.io](https://aiven.io) (solo correo, sin tarjeta).
2. "Create service" → **MySQL** → plan **Free**.
3. Elige una región (cualquiera cercana a donde esté Render; no es crítico
   para un proyecto de portafolio).
4. Cuando el servicio esté "Running", en su panel de **Overview** copia:
   - Host
   - Port
   - User (normalmente `avnadmin`)
   - Password
   - Default database name (o crea una propia)
5. En esa misma página, descarga el certificado **CA Certificate** (`ca.pem`).
   Lo vas a subir a Render en el paso 4 — no lo pegues en ningún `.env`.

## 2. Subir el código a GitHub

Render despliega conectándose a un repo de GitHub (o GitLab/Bitbucket).

```bash
gh auth login        # si no tienes sesión activa de gh
gh repo create ligamanager --private --source=. --remote=origin --push
```

Si prefieres hacerlo manual: crea el repo vacío en github.com, luego:

```bash
git add .
git commit -m "Despliegue inicial: Render + Aiven"
git remote add origin https://github.com/<tu-usuario>/ligamanager.git
git branch -M main
git push -u origin main
```

## 3. Crear el Web Service en Render

1. Crea una cuenta en [render.com](https://render.com) (solo correo, sin tarjeta).
2. "New +" → **Blueprint** → conecta tu cuenta de GitHub → selecciona el repo.
   Render detecta `render.yaml` automáticamente y propone el servicio
   `ligamanager` (Docker, plan Free). Confírmalo.
   - Si prefieres no usar Blueprint: "New +" → **Web Service** → selecciona
     el repo → Runtime **Docker** → plan **Free** → Render usa el
     `Dockerfile` de la raíz solo.

## 4. Variables de entorno y el certificado de Aiven

En el dashboard del servicio → **Environment**:

1. Sube el `ca.pem` que descargaste de Aiven en **Secret Files**:
   - Filename: `aiven-ca.pem`
   - Contenido: pega el contenido del archivo descargado.
   - Render lo monta en `/etc/secrets/aiven-ca.pem` dentro del contenedor —
     coincide con `MYSQL_ATTR_SSL_CA` en `render.yaml`, no hay que tocar nada más.
2. Rellena las variables marcadas como "pendiente" (`render.yaml` las dejó
   con `sync: false`, así que Render te las pide vacías):

   | Variable | Valor |
   |---|---|
   | `APP_KEY` | genera una local: `php artisan key:generate --show` y pega el resultado completo (incluye `base64:`) |
   | `APP_URL` | la URL que Render te asigna, p. ej. `https://ligamanager.onrender.com` (edítala después del primer deploy si cambia) |
   | `DB_HOST` | host de Aiven |
   | `DB_PORT` | puerto de Aiven |
   | `DB_DATABASE` | nombre de la BD en Aiven |
   | `DB_USERNAME` | usuario de Aiven |
   | `DB_PASSWORD` | password de Aiven |
   | `CRON_TOKEN` | una cadena aleatoria larga, p. ej. `php artisan tinker --execute="echo Str::random(40);"` |

## 5. Primer despliegue

Guarda las variables — Render dispara el build automáticamente. Sigue el log
de build y luego el de runtime. En el arranque del contenedor vas a ver:

```
Migrating: ...
Seeding: Database\Seeders\RolesSeeder
Seeding: Database\Seeders\AdminUserSeeder
```

Si todo corre sin errores, la app queda disponible en la URL que Render
asignó. Entra a `/login` con `admin@liga.test` / `password` y **cambia esa
contraseña de inmediato** desde tu perfil — son credenciales públicas (están
en este mismo repo).

## 6. Cron externo (scheduler + mantener la app despierta)

El plan free de Render no incluye Cron Jobs, y además "duerme" el servicio
tras 15 min sin tráfico (el primer visitante después espera 30-60s). Un
mismo cron externo gratuito resuelve ambas cosas:

1. Crea una cuenta gratis en [cron-job.org](https://cron-job.org).
2. Crea un cron job nuevo:
   - URL: `https://<tu-app>.onrender.com/cron/<el-CRON_TOKEN-que-pusiste>`
   - Intervalo: cada 10-15 minutos.
3. Eso mantiene el contenedor despierto y dispara
   `php artisan schedule:run` lo bastante seguido como para que
   `liga:actualizar-bloqueos` (que corre una vez al día) nunca se salte su
   horario.

## 7. Limitación conocida: almacenamiento no persistente

Los logos de equipo y fotos de jugador se guardan en el disco del
contenedor (`storage/app/public`). El plan free de Render **no** incluye
disco persistente: cada redeploy (push nuevo, reinicio manual) borra esos
archivos subidos — las columnas en la BD siguen apuntando a ellos pero el
archivo físico ya no está.

Para un proyecto de portafolio esto es aceptable (no es un uso productivo
real), pero si te molesta en las demos:

- Opción simple: vuelve a subir un logo/foto de ejemplo después de cada
  redeploy.
- Opción robusta (si algún día quieres resolverlo de verdad): mover
  `FILESYSTEM_DISK` a un bucket S3-compatible gratuito (Cloudflare R2 o
  Backblaze B2 tienen niveles gratuitos) — requeriría agregar el driver S3
  de Laravel y una cuenta más. No está implementado todavía.

## 8. Verificación post-despliegue

- `https://tu-app.onrender.com/` carga el portal público sin pedir login.
- `/login` funciona con el admin, y ya cambiaste su contraseña.
- `/up` responde 200 (healthcheck de Laravel).
- Subir un logo de equipo de prueba funciona (confirma que `storage:link`
  corrió bien) — recuerda la limitación del punto 7.
- El cron externo está dado de alta y, pasado un día, no hay errores de
  scheduler en los logs de Render.

## 9. Actualizaciones futuras

Cada `git push` a la rama conectada dispara un rebuild y redeploy
automático en Render — no hay pasos manuales adicionales. Las migraciones
nuevas corren solas en cada arranque del contenedor (`entrypoint.sh`).
