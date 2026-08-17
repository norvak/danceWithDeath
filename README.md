# Baile con la Muerte

Aplicación web para consultar horarios disponibles y reservar una cita de una hora. El proyecto utiliza Laravel 12 para la API, Nuxt 3 y Vue 3 para la interfaz, y PostgreSQL 16 para la persistencia.

Todo el entorno se ejecuta mediante Docker. No es necesario instalar PHP, Composer, Node.js, npm ni PostgreSQL directamente en el equipo.

## Tecnologías

- Laravel 12.
- PHP 8.3.
- Composer 2.
- Nuxt 3.
- Vue 3.
- TypeScript.
- Node.js 20.
- PostgreSQL 16.
- Docker Compose v2.

## Requisitos

Antes de comenzar, instala:

- Docker Desktop.
- Docker Compose v2, incluido con Docker Desktop.
- Git.

Las imágenes utilizadas son compatibles con Apple Silicon y equipos x86-64.

## Estructura

```text
danceWithDeath/
├── backend/
│   ├── app/
│   ├── database/
│   ├── routes/
│   ├── tests/
│   └── Dockerfile
├── frontend/
│   ├── components/
│   ├── composables/
│   ├── types/
│   ├── app.vue
│   └── Dockerfile
├── .env.example
├── docker-compose.yml
└── README.md
```

## Primera instalación

Todos los comandos deben ejecutarse desde la raíz del repositorio, donde se encuentra `docker-compose.yml`.

```bash
git clone https://github.com/norvak/danceWithDeath.git
cd danceWithDeath
```

Crea los archivos locales de configuración:

```bash
cp .env.example .env
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
```

Construye las imágenes y levanta los servicios:

```bash
docker compose up -d --build
```

Genera la clave de Laravel y ejecuta las migraciones:

```bash
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate
```

Comprueba el estado:

```bash
docker compose ps
```

Los tres servicios deben aparecer activos y PostgreSQL debe mostrar el estado `healthy`.

## Accesos locales

- Frontend: [http://localhost:3000](http://localhost:3000)
- Backend: [http://localhost:8000](http://localhost:8000)
- Estado de la API: [http://localhost:8000/api](http://localhost:8000/api)

## Servicios Docker

| Servicio | Contenedor | Puerto local | Función |
| --- | --- | --- | --- |
| `frontend` | `dance_with_death_frontend` | `3000` | Servidor de desarrollo Nuxt |
| `backend` | `dance_with_death_backend` | `8000` | API de Laravel |
| `postgres` | `dance_with_death_postgres` | `5432` | Base de datos PostgreSQL |

PostgreSQL, las dependencias de Composer y las dependencias de npm utilizan volúmenes Docker independientes.

## Reglas de negocio

- Solo se permiten citas de lunes a viernes.
- No se permiten fechas pasadas.
- El horario de atención es de `09:00` a `19:00`.
- Cada cita ocupa un bloque de una hora.
- Los horarios de inicio disponibles son desde `09:00` hasta `18:00`.
- Una fecha y hora no pueden reservarse dos veces.
- Un correo electrónico solo puede tener una cita registrada.
- El nombre y el correo electrónico son obligatorios.
- La interfaz muestra horarios disponibles, ocupados y no disponibles.

PostgreSQL garantiza los duplicados mediante restricciones únicas para el correo y para la combinación de fecha y hora.

## API

### Consultar disponibilidad

```http
GET /api/availability?date=2026-08-18
```

Ejemplo de respuesta:

```json
{
  "date": "2026-08-18",
  "slots": [
    {
      "time": "09:00",
      "available": true,
      "status": "available"
    },
    {
      "time": "10:00",
      "available": false,
      "status": "occupied"
    }
  ]
}
```

### Registrar una cita

```http
POST /api/appointments
```

Ejemplo de solicitud:

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "appointment_date": "2026-08-18",
  "appointment_time": "09:00"
}
```

Ejemplo de respuesta exitosa:

```json
{
  "message": "Appointment booked successfully.",
  "data": {
    "id": 1,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "date": "2026-08-18",
    "time": "09:00"
  }
}
```

La API utiliza `422 Unprocessable Entity` para datos inválidos y `409 Conflict` cuando el correo o el horario ya están registrados.

## Ejecuciones posteriores

Para levantar nuevamente el proyecto:

```bash
docker compose up -d
```

Para detenerlo sin eliminar la información de PostgreSQL:

```bash
docker compose down
```

## Registros

Todos los servicios:

```bash
docker compose logs -f
```

Backend:

```bash
docker compose logs -f backend
```

Frontend:

```bash
docker compose logs -f frontend
```

PostgreSQL:

```bash
docker compose logs -f postgres
```

Para salir de la vista de registros, presiona `Control + C`.

## Pruebas y compilación

Ejecuta las pruebas automatizadas del backend:

```bash
docker compose exec backend php artisan test
```

Comprueba la compilación de producción del frontend:

```bash
docker compose exec frontend npm run build
```

## Migraciones

Ejecutar migraciones pendientes:

```bash
docker compose exec backend php artisan migrate
```

Consultar su estado:

```bash
docker compose exec backend php artisan migrate:status
```

Recrear todas las tablas y eliminar su información:

```bash
docker compose exec backend php artisan migrate:fresh
```

## Acceder a los contenedores

Backend:

```bash
docker compose exec backend bash
```

Frontend:

```bash
docker compose exec frontend sh
```

PostgreSQL:

```bash
docker compose exec postgres psql -U postgres -d dance_with_death
```

Docker Compose ya inicia Laravel y Nuxt automáticamente. No es necesario ejecutar nuevamente los servidores de desarrollo dentro de los contenedores.

## Conexión con DBeaver

Utiliza la siguiente configuración local:

```text
Host: localhost
Port: 5432
Database: dance_with_death
Username: postgres
Password: postgres
```

Laravel no utiliza `localhost` para conectarse a PostgreSQL. Dentro de la red Docker usa `DB_HOST=postgres`.

## Cambiar puertos

Los puertos publicados pueden modificarse en el archivo `.env` de la raíz:

```env
FRONTEND_PORT=3000
BACKEND_PORT=8000
POSTGRES_PORT=5432
```

Después de cambiar un puerto, reinicia los servicios:

```bash
docker compose down
docker compose up -d
```

Si cambia `BACKEND_PORT`, también debe actualizarse `NUXT_PUBLIC_API_BASE_URL` en `frontend/.env`.

## Reiniciar completamente el entorno

El siguiente procedimiento elimina también los datos locales de PostgreSQL:

```bash
docker compose down -v
docker compose up -d --build
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate
```

## Problemas frecuentes

Si un servicio no inicia:

```bash
docker compose ps
docker compose logs --tail=100
```

Si un puerto está ocupado, cambia su valor en el archivo `.env` de la raíz.

Si Laravel informa que falta la clave de la aplicación:

```bash
docker compose exec backend php artisan key:generate
```

Si la base de datos no tiene sus tablas:

```bash
docker compose exec backend php artisan migrate
```

Si se necesita reconstruir una imagen después de modificar un Dockerfile:

```bash
docker compose up -d --build
```
