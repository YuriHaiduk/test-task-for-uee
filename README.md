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

```sh
docker compose up -d
```

That's it. On first boot the `php` container installs dependencies, creates
`.env`, generates the app key, runs migrations, and seeds a few example
companies — so no manual steps are needed. The first start takes a little longer
while Composer installs; follow the progress with:

```sh
docker compose logs -f php
```

Once bootstrap finishes, the API is available at http://localhost:8080.

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
