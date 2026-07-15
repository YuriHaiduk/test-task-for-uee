# UEE Company Versioning API

REST API for creating and updating companies with **data versioning**. Every
change is stored as a separate version, and the versioning mechanism is a
reusable module that can be attached to any future model.

Endpoints:

- `POST /api/company` — create or update a company. Returns `status`
  (`created` / `updated` / `duplicate`), `company_id`, and `version`.
- `GET /api/company/{edrpou}/versions` — full version history, newest first.

## Tech Stack

- Laravel 12 / PHP 8.4
- PostgreSQL 16
- Docker Compose
- PHPUnit

## Getting Started

Copy environment files:

```sh
cp .env.docker.example .env.docker
cp backend/.env.example backend/.env
```

Install dependencies, start the containers, and set up the app:

```sh
docker compose -f docker-compose.local.yml --env-file .env.docker run --rm php composer install
docker compose -f docker-compose.local.yml --env-file .env.docker up -d --build
docker compose -f docker-compose.local.yml --env-file .env.docker exec -T php php artisan key:generate
docker compose -f docker-compose.local.yml --env-file .env.docker exec -T php php artisan migrate --seed
```

`--seed` loads a few example companies (each with an initial version). The API
is now available at http://localhost:8080.

## Usage

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

Get the version history of a company (`37027819` is created by the seeder):

```sh
curl http://localhost:8080/api/company/37027819/versions \
  -H "Accept: application/json"
```
