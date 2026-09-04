<p align="center">
  <img src="public/branding/serbisyo-track-icon.png" width="190" alt="SerbisyoTrack location pin, document, and checkmark logo">
</p>

<h1 align="center">SerbisyoTrack</h1>

<p align="center">
  A bilingual, privacy-conscious service-request and appointment tracking portal for a fictional barangay.
</p>

<p align="center">
  <img alt="Laravel 12" src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white">
  <img alt="React 19" src="https://img.shields.io/badge/React-19-149ECA?logo=react&logoColor=white">
  <img alt="TypeScript" src="https://img.shields.io/badge/TypeScript-strict-3178C6?logo=typescript&logoColor=white">
  <img alt="Phase 6 complete" src="https://img.shields.io/badge/Phase%206-complete-14594F">
</p>

<p align="center">
  <a href="#features">Features</a> ·
  <a href="#local-setup">Local setup</a> ·
  <a href="#demonstration-accounts-and-requests">Demo accounts</a> ·
  <a href="#quality-checks">Quality checks</a> ·
  <a href="PHASES.md">Delivery phases</a>
</p>

> **Portfolio demonstration only:** SerbisyoTrack is not affiliated with, operated by, or endorsed by a real government office. Barangay Haraya, its records, contact details, accounts, and requests are fictional. Do not submit real personal or sensitive information.

## Project status

Phases 1–6 are complete. The application includes the resident, staff, and administration workflows plus accessibility, privacy, security, error-handling, and comprehensive regression hardening. Phase 7 covers deployment configuration, final CI validation, clean-install verification, and delivery documentation.

See [PHASES.md](PHASES.md) for the acceptance checklist and verified results for every completed phase.

## Features

| Area                 | Highlights                                                                                                                                                                                                             |
| -------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Resident experience  | Bilingual service directory, detailed eligibility and requirements, guided requests, secure follow-up responses, appointment preferences, private attachments, printable receipts, and reference-and-PIN tracking without an account |
| Privacy and security | Encrypted resident fields and sessions, hashed tracking PINs, private file storage, short-lived tracking grants, layered rate limits, allow-listed public responses, secure headers, and non-cacheable sensitive pages |
| Staff workspace      | Searchable request queue, safe claim and release controls, assignment policies, controlled status transitions, appointment management, encrypted notes, immutable history, due dates, and overdue indicators           |
| Administration       | Service and staff management, bilingual office settings, aggregate reporting, formula-safe CSV exports, append-only audit events, and configurable scheduled retention cleanup                                         |
| Accessibility        | Semantic landmarks, skip links, keyboard-visible focus, announced validation errors and page titles, responsive layouts, reduced-motion support, forced-colors support, and non-visual chart data                      |
| Quality assurance    | End-to-end resident-to-staff-to-tracking coverage, authorization and abuse regression tests, PHP and TypeScript static analysis, formatting, linting, dependency audits, and production builds                         |

### Technology snapshot

- Laravel 12 and PHP 8.2+
- Inertia 2, React 19, and strict TypeScript
- Tailwind CSS 4 and Vite
- SQLite for local development and tests; PostgreSQL-ready configuration for production
- Database-backed sessions, queues, cache, and scheduled cleanup

## Intended users

- Residents browsing services and submitting or tracking requests without an account
- Staff processing assigned requests
- Administrators managing services, staff, analytics, settings, and audit history

## Technology choices

The application is a Laravel monolith. Inertia keeps routing, authentication, validation, and authorization on the Laravel server while React provides a responsive TypeScript interface. SQLite keeps local development and tests simple; PostgreSQL is the production target. Database-backed queues avoid requiring a paid queue service for the demonstration.

See [ARCHITECTURE.md](ARCHITECTURE.md) for boundaries and planned modules.

### Inertia page convention

React page components live in `resources/js/pages` using lowercase directory and file names. Server-side component names map directly to `.tsx` files beneath that directory:

```php
return Inertia::render('requests/create');
```

```text
resources/js/pages/requests/create.tsx
```

The browser resolver in `resources/js/app.tsx`, the SSR resolver, and `config/inertia.php` all use this same lowercase convention. Preserve the capitalization when adding or renaming pages: GitHub Actions runs on Linux, where `pages` and `Pages` are different paths even though Windows treats them as equivalent.

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

`composer run dev` starts the Laravel application, queue worker, log viewer, and Vite development server together. Open **http://localhost:8000** in your browser; the Vite address is only the frontend asset server and will display Vite's Laravel integration message when opened directly.

## Demonstration accounts and requests

The local/testing seeder creates an administrator, a staff member, six fictional services, and four fictional requests:

- Administrator: `admin@serbisyotrack.test`
- Staff: `staff@serbisyotrack.test`
- Password: `SerbisyoTrack!2026`

The sample request references are `ST-DEMA-RQST-AAAA` through `ST-DEMD-RQST-DDDD`; all use tracking PIN `246824`. They cover unassigned, assigned, overdue, and appointment-preference states. No sample represents a real person or government record.

An inactive fictional account and three sanitized audit events are also seeded so the staff and audit filters have immediate demonstration data. The inactive account cannot authenticate.

The seeder refuses to create demonstration credentials when `APP_ENV=production`. Replace these credentials in any shared non-production environment.

## Quality checks

GitHub Actions runs these checks on Ubuntu for pushes and pull requests targeting `main` or `develop`. This also verifies case-sensitive file resolution that may not be detectable on Windows.

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
- Resident contact, location, request-detail, appointment-note, and original attachment-name fields are encrypted at rest.
- Tracking PINs are stored only as password hashes and checked behind layered per-IP and per-reference rate limits.
- Request references use 60 bits of cryptographic randomness and omit visually ambiguous characters.
- Tracking and receipt access uses short-lived encrypted-session grants; public response objects are explicitly allow-listed.
- Attachments are content-validated, stored on the private disk, and downloaded only after request ownership and tracking access checks.
- While a request needs information, the same tracking grant lets the resident send an encrypted response and additional private files; the response is appended to the staff timeline without changing the status on staff's behalf.
- Staff request visibility and each operation are protected by server-side policy checks; assignment, transitions, notes, and appointment changes are transactionally locked.
- Only an assigned staff member or an administrator can change an open request, while verified active staff may safely claim unassigned work or release their own assignment.
- Public history is built from explicitly allow-listed bilingual messages and never exposes an actor, internal note, resident details, storage paths, or a tracking PIN.
- Public messages, internal notes, and resident-submitted text remain encrypted at rest; activity rows have no application update or delete route.
- Email notifications are queued only after a successful commit and only when the resident selected email as the preferred channel.
- Staff administration prevents self-deactivation, self-demotion, and removal of the last active administrator. Deactivation releases open assignments without deleting history.
- Report queries explicitly select operational fields and omit encrypted resident names, contact details, locations, request descriptions, and internal notes.
- CSV cells neutralize spreadsheet formula prefixes, remove embedded line controls, use fixed filenames, and are returned with private `no-store` headers.
- Audit event types and metadata keys are centrally allow-listed; request and service audits commit inside the same transaction as their underlying action.
- Closed requests and their private files are purged daily only after the configurable retention period. Audit events are retained separately.
- Sensitive public workflow responses use `no-store`, `noindex`, and `nosniff` controls.
- Framework error pages cover authorization, missing routes, expired sessions, throttling, server errors, and maintenance without returning exception data to browser users.
- Resident PII, request details, attachment input, and tracking credentials are excluded from validation old-input flashing.
- Authenticated staff and administrator mutations are rate limited per account; public limits remain scoped by IP and tracking reference where appropriate.
- Global headers restrict framing, cross-origin resource use, unnecessary browser capabilities, referrers on sensitive responses, and legacy cross-domain policy files.

## Public and administrative routes

| Area               | Routes                                                                      | Access                                                                                       |
| ------------------ | --------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------- |
| Public information | `/`, `/services`, `/services/{slug}`, `/privacy`, `/accessibility`, `/help` | Everyone                                                                                     |
| Resident requests  | `/services/{slug}/request`, request receipt                                 | Everyone; receipt is session-grant protected                                                 |
| Public tracking    | `/track`, `/track/{reference}`, secure follow-up responses, authorized attachment downloads | Reference and PIN; 15-minute session grant                                          |
| Staff session      | `/login`, `/dashboard`, account settings                                    | Active, verified staff or administrators                                                     |
| Request operations | `/staff/requests` and assignment/status/note/appointment/download actions   | Active, verified staff; mutations additionally require assignment or administrator authority |
| Service management | `/admin/services` and create/edit/archive/restore actions                   | Administrators only                                                                          |
| Administration     | `/admin/staff`, `/admin/settings`, `/admin/audit-events`                    | Administrators only                                                                          |
| Reporting          | `/admin/reports`, sanitized CSV download                                    | Administrators only                                                                          |

No public API exposes a service database ID. The `{slug}` value is a stable public route key.

## Known limitations

- Business-day targets currently skip weekends but do not yet use a configurable holiday calendar.
- Email delivery requires a running queue worker; SMS is represented as a future channel rather than silently simulated.
- Reporting is operational and aggregate; predictive analytics and external business-intelligence integrations are outside this MVP.
- Docker is not installed in the current Windows environment, so image validation is deferred to the deployment milestone and CI.
- Malware scanning is outside the MVP; attachment handling relies on strict content-type, size, count, private-storage, and authorized-download controls.
- Automated and live HTTP checks pass, but interactive keyboard, assistive-technology, and cross-browser visual QA could not run because the current environment exposed no browser instance.

## Roadmap

1. Completed: application foundation
2. Completed: service directory, public information, and bilingual service administration
3. Completed: secure request, appointment, attachment, receipt, and tracking workflows
4. Completed: staff assignment, controlled status and appointment operations, notes, timelines, overdue targets, and queued email updates
5. Completed: staff administration, office/retention settings, analytics, sanitized exports, audit events, and scheduled cleanup
6. Completed: accessibility, privacy, and security hardening with comprehensive workflow QA
7. Next: Docker, Render configuration, final CI validation, delivery documentation, and clean-install verification

## Quick tour

After starting the development environment, explore these entry points:

- `/` — resident landing page and demonstration notice
- `/services` — bilingual public service directory
- `/track` — secure reference-and-PIN request tracking
- `/login` — fictional staff and administrator sign-in
- `/staff/requests` — protected operational request queue
- `/admin/reports` — administrator-only aggregate reporting

## License and portfolio use

This repository is a fictional portfolio project. Do not present it as an official government service or use it to collect real resident data.
