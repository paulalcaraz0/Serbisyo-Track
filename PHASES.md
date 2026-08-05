# SerbisyoTrack Delivery Phases

This file is the living implementation roadmap. A phase is marked complete only after its scoped functionality and verification gates pass. Completion of one phase does not mean the full MVP is production-ready.

| Phase | Status | Outcome |
| --- | --- | --- |
| 0. Architecture and planning | Complete | Architecture, data model direction, assumptions, risks, milestones, and acceptance gates defined |
| 1. Application foundation | Complete | Laravel/Inertia/React foundation, bilingual homepage, staff authentication, roles, security headers, quality tooling, SQLite, CI baseline, and documentation |
| 2. Services and public information | Complete | Bilingual service directory/details, privacy/accessibility/help pages, and administrator service management |
| 3. Resident submissions and tracking | Complete | Guided requests, appointments, attachments, secure references, tracking PINs, receipts, and public status tracking |
| 4. Staff operations | Complete | Assignment, workflow transitions, public/private history, internal notes, request timelines, due targets, appointment controls, and queued notifications |
| 5. Administration and reporting | Not started | Staff management, office/retention settings, analytics, sanitized exports, audit events, and retention cleanup |
| 6. Hardening and comprehensive QA | Not started | Error pages, accessibility audit, privacy review, security hardening, and full workflow test coverage |
| 7. Delivery | Not started | Docker, Render deployment configuration, final GitHub Actions validation, final documentation, and clean-install verification |

## Phase 2 acceptance checklist

- [x] Six realistic fictional services seed successfully
- [x] Residents can browse only active, non-archived services
- [x] Service details show eligibility, requirements, fees, processing time, office hours, procedure, appointment requirement, and contact information
- [x] English and Filipino content is available without changing URLs
- [x] Privacy, accessibility, and help pages are functional and bilingual
- [x] Administrators can search, filter, sort, paginate, create, update, activate, deactivate, archive, and restore services
- [x] Staff users cannot access service administration
- [x] Services cannot be deleted through the application
- [x] Clean migrations and seed data pass
- [x] Automated tests, Pint, Larastan, Prettier, ESLint, TypeScript, dependency audits, and production build pass

Phase 2 verification result: 45 automated tests with 338 assertions passed. The production asset build, clean migrations/seeding, formatting, static analysis, TypeScript, frontend linting, and dependency audits also passed. Live HTTP smoke checks passed; interactive browser visual QA remains unavailable in the current environment and is not represented as completed.

## Phase 3 acceptance checklist

- [x] Residents can open a bilingual guided request form from any active, non-archived service
- [x] Contact, location, request, appointment-note, and attachment-name fields are encrypted at rest
- [x] Submissions receive a high-entropy non-sequential reference and a six-digit PIN that is stored only as a secure hash
- [x] Required and optional appointment preferences are validated and stored separately from the request
- [x] Up to five PDF, JPG, or PNG attachments of at most 5 MB each are validated by content and stored privately
- [x] The receipt displays the tracking PIN once, supports printing, and does not expose submitted personal details
- [x] Reference-and-PIN tracking is rate limited, uses a short-lived session grant, and returns only allow-listed public status data
- [x] Attachment downloads require the matching tracking grant and request ownership
- [x] Inactive and archived services reject new requests
- [x] Request, receipt, tracking, and download responses opt out of caching and search indexing
- [x] Clean migrations and seed data pass
- [x] Automated tests, Pint, Larastan, Prettier, ESLint, TypeScript, dependency audits, and production build pass

Phase 3 verification result: 57 automated tests with 468 assertions passed. Clean migrations/seeding, formatting, static analysis, TypeScript, frontend linting, dependency audits, production build, and live HTTP smoke checks passed. The in-app browser reported no available browser instance, so interactive visual QA remains explicitly deferred rather than represented as completed.

## Phase 4 acceptance checklist

- [x] Active, verified staff can browse a protected request queue and open an authorized request workspace
- [x] Queue summaries and filters cover assignment, status, service, search, and overdue work without leaking resident details in list data
- [x] Staff can claim unassigned work and release their own work; administrators can assign any active, verified staff member
- [x] Only an assigned staff member or administrator can transition an open request, add an internal note, or manage its appointment
- [x] A single transactionally locked workflow service enforces the allowed status graph and prevents terminal-state changes
- [x] Needs-information and rejection transitions require complete English and Filipino resident guidance
- [x] A request cannot enter Scheduled until its appointment is confirmed
- [x] Status, assignment, note, and appointment actions append encrypted immutable activity records
- [x] Public tracking exposes only allow-listed localized history without actors, private notes, personal data, or storage metadata
- [x] Service-specific business-day targets populate due dates, overdue indicators, and terminal closure times
- [x] Email-preference updates are queued after commit without including the tracking PIN
- [x] Staff attachment downloads are authorized and scoped through the owning request
- [x] Fictional seed data covers unassigned, assigned, overdue, and appointment-preference staff workflows and is disabled in production
- [x] Clean migrations and seed data pass
- [x] Automated tests, Pint, Larastan, Prettier, ESLint, TypeScript, dependency audits, and production build pass

Phase 4 verification result: 76 automated tests with 640 assertions passed. Clean migrations/seeding, PHP and frontend formatting, static analysis, TypeScript, frontend linting, production dependency audits, the production build, and live HTTP smoke checks passed. The in-app browser reported no available browser instance, so interactive visual QA remains explicitly deferred rather than represented as completed.

## Overall MVP definition of done

The complete MVP requires all seven phases, end-to-end resident and staff workflows, server-enforced authorization, public-data redaction, passing automated and static checks, valid deployment configuration, documentation that matches the implementation, and fictional demonstration data only.
