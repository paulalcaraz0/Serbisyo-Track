# SerbisyoTrack

SerbisyoTrack is a portfolio demonstration of a barangay service-request and appointment portal. It is designed to help residents understand services, submit requests, request appointments, receive a secure reference, and follow public status updates. Authorized staff will process those requests through a protected dashboard.

> **Demonstration only:** SerbisyoTrack is not affiliated with, operated by, or endorsed by a real government office. Barangay Haraya, its records, and all development accounts are fictional.

## Current milestone

Phase 2 adds a tested public service catalog and administrator service management on top of the verified application foundation:

- Laravel 12 with PHP 8.2+
- Inertia 2, React 19, and TypeScript
- Tailwind CSS 4 and Vite
- English and Filipino homepage, service, privacy, accessibility, and help content
- Six complete fictional services with eligibility, requirements, fees, schedules, procedures, appointment guidance, and contact details
- Public service routes that use non-identifying slugs and allow-listed response resources
- Administrator-only service search, filters, sorting, pagination, creation, editing, activation, archival, and restoration
- Archival instead of deletion so future request and audit history can be preserved
- Staff-only session authentication with login throttling
- Administrator/staff role and account-activation foundations
- Secure response headers and encrypted sessions
- SQLite local/test configuration and PostgreSQL-ready environment settings
- PHPUnit/Pest tests, Pint, Larastan, ESLint, Prettier, and TypeScript checks

Resident request submission, tracking, appointments, staff request processing, analytics, and deployment configuration are intentionally scheduled for later verified phases. See [PHASES.md](PHASES.md) for the living delivery checklist.

## Intended users

- Residents browsing services and submitting or tracking requests without an account
- Staff processing assigned requests
- Administrators managing services, staff, analytics, settings, and audit history

## Technology choices

The application is a Laravel monolith. Inertia keeps routing, authentication, validation, and authorization on the Laravel server while React provides a responsive TypeScript interface. SQLite keeps local development and tests simple; PostgreSQL is the production target. Database-backed queues avoid requiring a paid queue service for the demonstration.

See [ARCHITECTURE.md](ARCHITECTURE.md) for boundaries and planned modules.

## Local setup

### Windows PowerShell

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File -Force database/database.sqlite
php artisan migrate --seed
npm.cmd ci
npm.cmd run build
composer run dev
```

### macOS and Linux

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm ci
npm run build
composer run dev
```

Set `APP_DEBUG=true` only in a trusted local environment when detailed debugging is needed. Never enable it in production.

## Demonstration account

The local/testing seeder creates one administrator and six fictional services:

- Email: `admin@serbisyotrack.test`
- Password: `SerbisyoTrack!2026`

The seeder refuses to create demonstration credentials when `APP_ENV=production`. Replace these credentials in any shared non-production environment.

## Quality checks

```powershell
php artisan test
vendor\bin\pint --test
composer analyse
npm.cmd run format:check
npm.cmd run lint:check
npm.cmd run typecheck
npm.cmd run build
npm.cmd audit
composer audit
```

## Security and privacy approach

- Public registration is disabled.
- Inactive users cannot authenticate and existing sessions are rejected.
- Authentication regenerates sessions and uses a generic failure response.
- Shared Inertia user data is explicitly allow-listed.
- Sessions are encrypted and sensitive authenticated responses are not cached.
- Development data is synthetic, and demonstration account credentials are never seeded in production.
- Public service responses omit database IDs and administrative metadata.
- Service administration is enforced by a server-side administrator policy.
- Services can be archived and restored but cannot be deleted through an application route.

Request-specific privacy controls, attachment authorization, audit events, retention cleanup, tracking protection, and CSV sanitization will be implemented alongside their features and tests.

## Public and administrative routes

| Area | Routes | Access |
| --- | --- | --- |
| Public information | `/`, `/services`, `/services/{slug}`, `/privacy`, `/accessibility`, `/help` | Everyone |
| Staff session | `/login`, `/dashboard`, account settings | Active, verified staff or administrators |
| Service management | `/admin/services` and create/edit/archive/restore actions | Administrators only |

No public API exposes a service database ID. The `{slug}` value is a stable public route key.

## Known limitations

- Residents can browse services, but submissions, appointments, receipts, and tracking are not available yet.
- Staff request-processing controls and analytics are not available yet.
- Docker is not installed in the current Windows environment, so image validation is deferred to the deployment milestone and CI.
- Malware scanning is outside the MVP; later attachment handling will use strict private-storage validation and document this limitation.

## Roadmap

1. Completed: application foundation
2. Completed: service directory, public information, and bilingual service administration
3. Next: secure request, appointment, attachment, receipt, and tracking workflows
4. Staff assignment, status, notes, history, and queued notifications
5. Administration, analytics, audit events, exports, and retention cleanup
6. Accessibility and security review, comprehensive tests, Docker, Render configuration, and delivery documentation

## Screenshots

Screenshots will be added after the primary resident and staff workflows are complete.

## License and portfolio use

This repository is a fictional portfolio project. Do not present it as an official government service or use it to collect real resident data.
