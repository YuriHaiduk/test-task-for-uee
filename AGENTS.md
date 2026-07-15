# Project Instructions

This repository contains a Docker-based Laravel 12 test task project.

The goal of the project is to build a simple REST API for creating and updating company
information with **data versioning**.

The API exposes a route `POST /api/company` that accepts a JSON company object:

```json
{
    "name": "ТОВ Українська енергетична біржа",
    "edrpou": "37027819",
    "address": "01001, Україна, м. Київ, вул. Хрещатик, 44"
}
```

A companion read route `GET /api/company/{edrpou}/versions` returns a company's version
history (newest first).

Processing rules:

- If a company with the given `edrpou` does not exist — create it and record its first version.
- If it exists and at least one field changed — update the company and store a new version
  (old and new data are preserved).
- If all fields match — do nothing (treated as a duplicate).

The **key requirement** is a reusable versioning module that can work with any future models and
routes, not only `/api/company`.

The main Laravel application is located in the `backend` directory. It is organized as
self-contained modules under `backend/modules/` — `Company` (route, controller, request,
model) and `Versioning` (the reusable versioning module).

Before changing application code, read and follow:

`backend/CLAUDE.md`

## Important

Do not use the Makefile.

The Makefile is intended only for the project owner.

Use Docker Compose commands directly.

Run all commands from the repository root.

## Project structure

- `backend/` — Laravel 12 application (module-based; see `backend/modules/`).
- `docker/` — Docker configuration (php, nginx).
- `docker-compose.yml` — local Docker Compose configuration. Services ship sane
  `${VAR:-default}` defaults, so no separate env file is required to boot.
- `Makefile` — owner-only helper commands. Do not use it.

Tests live inside each module under `backend/modules/*/Tests/` (wired via `phpunit.xml`),
not in `backend/tests/`.

## Services

- `nginx` — web server, proxies PHP requests to `php:9000`.
- `php` — PHP 8.4 FPM with Composer and PostgreSQL extensions.
- `postgres` — PostgreSQL 16 database.

## Expected high-level flow

```text
POST /api/company (JSON)
    ↓
Validation (name, edrpou, address)
    ↓
Company lookup by edrpou
    ↓
Create / Update / Duplicate decision
    ↓
Reusable versioning module records a version on change
    ↓
JSON response: { status, company_id, version }
```

Possible `status` values: `created`, `updated`, `duplicate`.

Follow SOLID principles and Laravel best practices. Prefer clear, maintainable code over
overengineering.
