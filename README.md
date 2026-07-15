# UEE Company Versioning API

A small Laravel-based REST API for creating and updating company information with **data
versioning**. Every change to a company's data is stored as a separate version, and the versioning
mechanism is designed as a reusable module that can be attached to any future model or route.

## API Overview

### `POST /api/company`

Accepts a JSON company object:

```json
{
    "name": "ТОВ Українська енергетична біржа",
    "edrpou": "37027819",
    "address": "01001, Україна, м. Київ, вул. Хрещатик, 44"
}
```

Behaviour:

- Company with this `edrpou` does not exist → create it and record version 1 → `status: created`.
- Company exists and at least one field changed → update it and store a new version →
  `status: updated`.
- Company exists and all fields match → nothing changes → `status: duplicate`.

Example response:

```json
{
    "status": "updated",
    "company_id": 5,
    "version": 3
}
```

Possible `status` values: `created`, `updated`, `duplicate`.

## Tech Stack

- Laravel 12
- PostgreSQL
- Docker Compose
- PHPUnit

## Quick Start

Clone the repository and open the project directory:

```sh
git clone <repository-url>
cd test-task-for-uee
```

Copy environment files:

```sh
cp .env.docker.example .env.docker
cp backend/.env.example backend/.env
```

Install Composer dependencies using a temporary PHP container:

```sh
docker compose -f docker-compose.local.yml --env-file .env.docker run --rm php composer install
```

Start containers:

```sh
docker compose -f docker-compose.local.yml --env-file .env.docker up -d --build
```

Check that all containers are running:

```sh
docker compose -f docker-compose.local.yml --env-file .env.docker ps
```

Generate application key:

```sh
docker compose -f docker-compose.local.yml --env-file .env.docker exec -T php php artisan key:generate
```

Run migrations:

```sh
docker compose -f docker-compose.local.yml --env-file .env.docker exec -T php php artisan migrate
```

## Usage

The API is available at:

```text
http://localhost:8080
```

Create or update a company:

```sh
curl -X POST http://localhost:8080/api/company \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "ТОВ Українська енергетична біржа",
    "edrpou": "37027819",
    "address": "01001, Україна, м. Київ, вул. Хрещатик, 44"
  }'
```

## Services

- `nginx` — http://localhost:8080
- `php` — PHP 8.4 FPM
- `postgres` — PostgreSQL 16 on port 5432

> Note: the Laravel application in `backend/` is installed in a later step.
