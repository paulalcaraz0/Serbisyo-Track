# SerbisyoTrack

SerbisyoTrack is a portfolio demonstration of a barangay service-request and appointment portal. It is designed to help residents understand services, submit requests, request appointments, receive a secure reference, and follow public status updates. Authorized staff will process those requests through a protected dashboard.

> **Demonstration only:** SerbisyoTrack is not affiliated with, operated by, or endorsed by a real government office. Barangay Haraya, its records, and all development accounts are fictional.

## Current milestone

Phase 4 adds a protected staff operations workspace and controlled request workflow on top of the resident submission, public tracking, service catalog, and application foundation:

- Laravel 12 with PHP 8.2+
- Inertia 2, React 19, and TypeScript
- Tailwind CSS 4 and Vite
- English and Filipino homepage, service, privacy, accessibility, and help content
- Six complete fictional services with eligibility, requirements, fees, schedules, procedures, appointment guidance, and contact details
- Guided bilingual resident request forms that do not require an account
- Required or optional appointment preferences with clear confirmation guidance
- Private PDF, JPG, and PNG attachments with strict count and size limits
- Encrypted resident-submitted fields and attachment names at rest
- High-entropy non-sequential references and one-way hashed six-digit tracking PINs
- Printable receipts that reveal the tracking PIN only once
- Rate-limited public tracking with 15-minute session grants and allow-listed status responses
- Authorized private attachment downloads that verify both the request and attachment ownership
- Staff request queues with search, status, assignment, and overdue filters
- Administrator assignment plus safe staff self-claim and release controls
- Server-enforced workflow transitions, terminal-state protection, and appointment scheduling rules
- Encrypted internal notes and immutable request activity timelines
- Bilingual public-safe status and appointment history without staff or private metadata
- Per-service business-day targets, due dates, overdue indicators, and closure timestamps
- Queued after-commit resident email updates for requests that prefer email contact
- Protected staff attachment downloads scoped to the owning request
- Public service routes that use non-identifying slugs and allow-listed response resources
- Administrator-only service search, filters, sorting, pagination, creation, editing, activation, archival, and restoration
- Archival instead of deletion so future request and audit history can be preserved
- Staff-only session authentication with login throttling
- Administrator/staff role and account-activation foundations
- Secure response headers and encrypted sessions
- SQLite local/test configuration and PostgreSQL-ready environment settings
- PHPUnit/Pest tests, Pint, Larastan, ESLint, Prettier, and TypeScript checks

Staff administration, analytics, formal audit events, exports, retention cleanup, and deployment configuration are intentionally scheduled for later verified phases. See [PHASES.md](PHASES.md) for the living delivery checklist.

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

## Demonstration accounts and requests

The local/testing seeder creates an administrator, a staff member, six fictional services, and four fictional requests:

- Administrator: `admin@serbisyotrack.test`
- Staff: `staff@serbisyotrack.test`
- Password: `SerbisyoTrack!2026`

The sample request references are `ST-DEMA-RQST-AAAA` through `ST-DEMD-RQST-DDDD`; all use tracking PIN `246824`. They cover unassigned, assigned, overdue, and appointment-preference states. No sample represents a real person or government record.

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
- Resident contact, location, request-detail, appointment-note, and original attachment-name fields are encrypted at rest.
- Tracking PINs are stored only as password hashes and checked behind layered per-IP and per-reference rate limits.
- Request references use 60 bits of cryptographic randomness and omit visually ambiguous characters.
- Tracking and receipt access uses short-lived encrypted-session grants; public response objects are explicitly allow-listed.
- Attachments are content-validated, stored on the private disk, and downloaded only after request ownership and tracking access checks.
- Staff request visibility and each operation are protected by server-side policy checks; assignment, transitions, notes, and appointment changes are transactionally locked.
- Only an assigned staff member or an administrator can change an open request, while verified active staff may safely claim unassigned work or release their own assignment.
- Public history is built from explicitly allow-listed bilingual messages and never exposes an actor, internal note, resident details, storage paths, or a tracking PIN.
- Public messages, internal notes, and resident-submitted text remain encrypted at rest; activity rows have no application update or delete route.
- Email notifications are queued only after a successful commit and only when the resident selected email as the preferred channel.
- Sensitive public workflow responses use `no-store`, `noindex`, and `nosniff` controls.

Formal administrator audit events, retention cleanup, and CSV sanitization will be implemented alongside their later features and tests.

## Public and administrative routes

| Area | Routes | Access |
| --- | --- | --- |
| Public information | `/`, `/services`, `/services/{slug}`, `/privacy`, `/accessibility`, `/help` | Everyone |
| Resident requests | `/services/{slug}/request`, request receipt | Everyone; receipt is session-grant protected |
| Public tracking | `/track`, `/track/{reference}`, authorized attachment downloads | Reference and PIN; 15-minute session grant |
| Staff session | `/login`, `/dashboard`, account settings | Active, verified staff or administrators |
| Request operations | `/staff/requests` and assignment/status/note/appointment/download actions | Active, verified staff; mutations additionally require assignment or administrator authority |
| Service management | `/admin/services` and create/edit/archive/restore actions | Administrators only |

No public API exposes a service database ID. The `{slug}` value is a stable public route key.

## Known limitations

- Staff lifecycle management, analytics, CSV exports, formal audit review, and automated retention cleanup are not available yet.
- Business-day targets currently skip weekends but do not yet use a configurable holiday calendar.
- Email delivery requires a running queue worker; SMS is represented as a future channel rather than silently simulated.
- Docker is not installed in the current Windows environment, so image validation is deferred to the deployment milestone and CI.
- Malware scanning is outside the MVP; attachment handling relies on strict content-type, size, count, private-storage, and authorized-download controls.
- Automated and live HTTP checks pass, but interactive browser visual QA could not run because the current environment exposed no browser instance.

## Roadmap

1. Completed: application foundation
2. Completed: service directory, public information, and bilingual service administration
3. Completed: secure request, appointment, attachment, receipt, and tracking workflows
4. Completed: staff assignment, controlled status and appointment operations, notes, timelines, overdue targets, and queued email updates
5. Next: administration, analytics, audit events, exports, and retention cleanup
6. Accessibility and security review, comprehensive tests, Docker, Render configuration, and delivery documentation

## Screenshots

Screenshots will be added after the primary resident and staff workflows are complete.

## License and portfolio use

This repository is a fictional portfolio project. Do not present it as an official government service or use it to collect real resident data.
