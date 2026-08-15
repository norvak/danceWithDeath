# Dance with Death

A Docker-based development environment for an appointment booking application built with Laravel 12, Nuxt 3, Vue 3, TypeScript, and PostgreSQL 16.

## Requirements

- Docker Desktop
- Docker Compose v2
- Git

PHP, Composer, Node.js, npm, and PostgreSQL do not need to be installed on the host machine. The images support Apple Silicon and x86-64 hosts.

## Quick start

```bash
cp .env.example .env
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
docker compose up -d --build
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate
```

Open:

- Frontend: http://localhost:3000
- Backend: http://localhost:8000
- API status: http://localhost:8000/api

## DBeaver connection

Use these development credentials:

```text
Host: localhost
Port: 5432
Database: dance_with_death
Username: postgres
Password: postgres
```

The port and credentials can be changed in the root `.env` file if they conflict with another local project. Laravel connects to PostgreSQL through the internal `postgres` service name, not through `localhost`.

## Useful commands

```bash
docker compose ps
docker compose logs -f
docker compose logs -f backend
docker compose logs -f frontend
docker compose logs -f postgres
docker compose exec backend php artisan migrate
docker compose exec backend php artisan test
docker compose exec backend composer install
docker compose exec frontend npm install
```

Stop the environment while preserving database data:

```bash
docker compose down
```

Delete the environment and its database data:

```bash
docker compose down -v
```

## Services

| Service | Container | Host port | Purpose |
| --- | --- | --- | --- |
| `frontend` | `dance_with_death_frontend` | `3000` | Nuxt development server |
| `backend` | `dance_with_death_backend` | `8000` | Laravel API |
| `postgres` | `dance_with_death_postgres` | `5432` | PostgreSQL database |

PostgreSQL data, backend Composer dependencies, and frontend npm dependencies are stored in dedicated Docker volumes to avoid host architecture conflicts.
