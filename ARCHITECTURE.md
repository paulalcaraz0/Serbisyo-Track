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
- Tracking requires a non-sequential reference plus a hashed private PIN.
- Public Inertia/JSON data is built from allow-listed resources rather than serialized models.
- Staff routes require an authenticated, active, verified account.
- Request and administrator operations are enforced with policies, assignment-aware domain services, and transaction-coupled audit events.
- Attachments remain private and download through authorized controller actions.

## Domain modules

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

## Implemented resident request module

Phase 3 implements resident intake and public tracking without resident accounts:

- `ServiceRequest` stores a high-entropy public reference, a one-way tracking PIN hash, workflow state, locale, consent time, and encrypted resident-submitted text fields.
- `RequestAppointment` stores a resident preference separately so staff can confirm, request rescheduling, or cancel without rewriting the original request.
- `RequestAttachment` uses a random public UUID, encrypted original filename, strict type/size/count validation, and the private local filesystem disk.
- Submission creation and attachment metadata writes occur in one database transaction; any private files written before a failure are removed.
- The receipt reveals the PIN only on its initial response. Reloaded receipts remain redacted and accessible only through the same short-lived session grant.
- Tracking checks a reference and PIN behind layered rate limits, then issues a 15-minute encrypted-session grant. Public tracking props exclude resident name, contact, location, request description, and future private notes.
- Attachment downloads query through the owning request and require the same tracking grant; no storage path or database identifier is exposed.
- Sensitive request, receipt, tracking, and attachment responses send `no-store` and `noindex` headers.

## Implemented staff operations module

Phase 4 implements the protected processing boundary:

- `ServiceRequestPolicy` allows active, verified users to view the queue while restricting assignment, transitions, notes, appointments, and downloads by role, assignment, open state, and request ownership.
- `RequestWorkflow` is the single status-transition service. It reloads the request with a row lock, re-authorizes the actor, validates the transition graph and appointment prerequisite, updates status/due/closure fields, appends an activity, and schedules the notification inside one transaction.
- `RequestOperations` uses the same lock-and-re-authorize pattern for assignments, encrypted internal notes, and appointment changes. Staff can claim unassigned work and release only their own; administrators can select any active, verified staff member.
- `RequestActivity` is an append-only timeline record with an actor, optional subject, typed event, status edge, encrypted bilingual public messages, and encrypted private details. No application route mutates or deletes an activity.
- Staff list resources deliberately omit resident PII. The protected detail resource includes only the data needed for the authorized workspace and never returns PIN hashes or storage paths.
- Public tracking derives history from activities that contain both allow-listed public messages. It selects the request locale and omits actors, private details, raw bilingual fields, and internal identifiers.
- Each service has a 1–60 business-day internal target. Submission calculates `due_at` while skipping weekends; open overdue work is surfaced in queue summaries and filters.
- `ResidentRequestUpdated` is an after-commit queued email notification. The notifier sends only when email is the selected contact channel and never contains the tracking PIN.
- Fictional development seed data exercises unassigned, assigned, overdue, and appointment-preference states and is guarded from production.

## Status workflow

Request states are defined by a PHP backed enum. The controlled forward graph is:

```text
Submitted -> Acknowledged | Rejected | Cancelled
Acknowledged -> Needs information | Scheduled | In progress | Rejected | Cancelled
Needs information -> Acknowledged | In progress | Rejected | Cancelled
Scheduled -> Needs information | In progress | Cancelled
In progress -> Needs information | Ready for release | Completed | Rejected | Cancelled
Ready for release -> In progress | Completed | Cancelled
Completed | Rejected | Cancelled -> terminal
```

`Scheduled` additionally requires a confirmed appointment. Terminal transitions set `closed_at` and disable assignment, notes, appointment changes, and further transitions. Request activities preserve the detailed operational timeline while cross-module audit events record sanitized security-relevant actions.

## Implemented administration and reporting module

Phase 5 implements the administrator and data-governance boundary:

- `UserPolicy`, `OfficeSettingPolicy`, and `AuditEventPolicy`, plus the report ability, restrict every Phase 5 route to active administrators on the server.
- `StaffAccountManager` creates verified internal accounts and updates them under row locks. It prevents self-deactivation/self-demotion and removal of the last active administrator. Deactivation releases only open assignments and appends operational history.
- `OfficeSetting` is a singleton containing bilingual office identity, public contact details, and the validated retention duration. The resident footer receives only locale-selected allow-listed values.
- `AdminReportService` explicitly selects request reference, workflow/due/closure dates, service, status, and assignee. Encrypted resident fields and activity contents never enter the reporting collection.
- Reports calculate aggregate status, workload, overdue, completion, resolution-time, and daily trend data for a maximum 366-day filter window.
- CSV export uses the same safe report collection. `CsvCellSanitizer` removes line controls and neutralizes formula-leading characters before writing fixed operational columns.
- `AuditLogger` accepts a typed event, a constrained subject type, and event-specific allow-listed scalar metadata. It discards unrecognized keys and never records resident details or internal note content.
- Service and request-operation audits are written inside their domain transaction. Staff, settings, exports, and retention actions also create typed audit events. No application update or delete route exists for audit rows.
- `requests:purge-expired` supports dry runs, locks and rechecks each candidate, deletes private files, and cascades expired closed requests. The scheduler runs it daily at 02:30 without overlap; recent and open requests remain untouched.

## Data strategy

- PostgreSQL is the production database; SQLite is used locally and in tests.
- Portable string-backed enums avoid database-specific enum drift.
- Services and accounts are archived/deactivated rather than deleted.
- Expired closed requests and private attachments are purged by a scheduled command after the configured retention period.
- Audit metadata is centrally allow-listed and excludes resident contact details, descriptions, and internal note contents.

## Frontend strategy

- Mobile-first layouts support widths from 320px upward.
- Server-provided translations establish English and Filipino without duplicating domain rules in JavaScript.
- Native HTML semantics are preferred; ARIA is used only for state or announcements native HTML cannot express.
- Every phase must include keyboard, focus, contrast, reduced-motion, loading, empty, success, and failure behavior relevant to that phase.

## Verification gates

Meaningful milestones must pass database reset/migrations, automated tests, Pint, Larastan, Prettier, ESLint, TypeScript, dependency audits, and the production Vite build. Docker and Render checks are added in the delivery phase.
