# SerbisyoTrack Architecture

## System shape

SerbisyoTrack is a Laravel monolith with an Inertia/React interface. Laravel owns routing, authentication, authorization, validation, transactions, queues, notifications, scheduled cleanup, and all public-data redaction. React never connects directly to the database or private file storage.

```text
Browser (Inertia React + TypeScript)
              |
      Laravel web routes
              |
 Controllers + Form Requests + Policies
              |
       Domain actions/services
        /          |          \
 Database     Private files    Queue
PostgreSQL       Storage     Notifications
(SQLite dev/test)
```

## Trust boundaries

- Public resident routes never accept or expose internal database IDs.
- Tracking will require a non-sequential reference plus a hashed private PIN.
- Public Inertia/JSON data will be built from allow-listed resources rather than serialized models.
- Staff routes require an authenticated, active, verified account.
- Administrator operations will be enforced with policies and recorded as sanitized audit events.
- Attachments will remain private and download through authorized controller actions.

## Planned domain modules

| Module | Responsibility |
| --- | --- |
| Services | Bilingual service details, requirements, fees, hours, processing targets, archival |
| Requests | Guided submission, secure references, consent, workflow status and public tracking |
| Appointments | Preferences, confirmed schedules, rescheduling history |
| Operations | Assignment, status transitions, internal notes, timelines and overdue calculations |
| Administration | Staff lifecycle, office settings, retention, services and audit review |
| Reporting | Authorized aggregate analytics and sanitized filtered CSV exports |
| Delivery | Queued email behind a channel interface designed for later SMS support |

## Implemented service module

Phase 2 implements the first complete domain boundary:

- `Service` owns bilingual resident guidance, fee-in-centavos, scheduling information, publication state, and a public slug.
- `ServiceRequirement` stores ordered bilingual requirements below a service.
- Public controllers query only active, non-archived records and serialize them through `PublicServiceResource`.
- Administrator controllers validate through dedicated form requests, authorize through `ServicePolicy`, and serialize through a separate administrative resource.
- Requirements are replaced with their service content inside a database transaction; a service itself is archived rather than deleted.
- English and Filipino share routes and domain records. The session locale selects allow-listed translated fields on the server.

## Status workflow

The request workflow will use a PHP backed enum and a single transition service. The service will validate the transition, authorize the actor, lock the request, update it, append immutable public/private history, create an audit event, and queue any notification inside one transaction.

Planned states: Submitted, Acknowledged, Needs information, Scheduled, In progress, Ready for release, Completed, Rejected, and Cancelled.

## Data strategy

- PostgreSQL is the production database; SQLite is used locally and in tests.
- Portable string-backed enums avoid database-specific enum drift.
- Services and accounts are archived/deactivated rather than deleted.
- Expired demonstration requests and private attachments will be purged by a scheduled command after the configured retention period.
- Audit metadata will be allow-listed and must not contain resident contact details.

## Frontend strategy

- Mobile-first layouts support widths from 320px upward.
- Server-provided translations establish English and Filipino without duplicating domain rules in JavaScript.
- Native HTML semantics are preferred; ARIA is used only for state or announcements native HTML cannot express.
- Every phase must include keyboard, focus, contrast, reduced-motion, loading, empty, success, and failure behavior relevant to that phase.

## Verification gates

Meaningful milestones must pass database reset/migrations, automated tests, Pint, Larastan, Prettier, ESLint, TypeScript, dependency audits, and the production Vite build. Docker and Render checks are added in the delivery phase.
