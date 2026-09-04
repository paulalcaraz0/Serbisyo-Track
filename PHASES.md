# SerbisyoTrack Delivery Phases

This file is the living implementation roadmap. A phase is marked complete only after its scoped functionality and verification gates pass. Completion of one phase does not mean the full MVP is production-ready.

| Phase | Status | Outcome |
| --- | --- | --- |
| 0. Architecture and planning | Complete | Architecture, data model direction, assumptions, risks, milestones, and acceptance gates defined |
| 1. Application foundation | Complete | Laravel/Inertia/React foundation, bilingual homepage, staff authentication, roles, security headers, quality tooling, SQLite, CI baseline, and documentation |
| 2. Services and public information | Complete | Bilingual service directory/details, privacy/accessibility/help pages, and administrator service management |
| 3. Resident submissions and tracking | Complete | Guided requests, appointments, attachments, secure follow-up responses, tracking PINs, receipts, and public status tracking |
| 4. Staff operations | Complete | Assignment, workflow transitions, public/private history, internal notes, request timelines, due targets, appointment controls, and queued notifications |
| 5. Administration and reporting | Complete | Staff management, office/retention settings, analytics, sanitized exports, audit events, and retention cleanup |
| 6. Hardening and comprehensive QA | Complete | Safe bilingual error states, accessibility and privacy review, layered security controls, abuse limits, and full workflow regression coverage |
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
- [x] Residents can securely provide encrypted follow-up information and private files only while staff is awaiting information
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
- [x] Resident follow-up responses and their linked files appear in the authorized staff timeline without automatically changing request status
- [x] Service-specific business-day targets populate due dates, overdue indicators, and terminal closure times
- [x] Email-preference updates are queued after commit without including the tracking PIN
- [x] Staff attachment downloads are authorized and scoped through the owning request
- [x] Fictional seed data covers unassigned, assigned, overdue, and appointment-preference staff workflows and is disabled in production
- [x] Clean migrations and seed data pass
- [x] Automated tests, Pint, Larastan, Prettier, ESLint, TypeScript, dependency audits, and production build pass

Phase 4 verification result: 76 automated tests with 640 assertions passed. Clean migrations/seeding, PHP and frontend formatting, static analysis, TypeScript, frontend linting, production dependency audits, the production build, and live HTTP smoke checks passed. The in-app browser reported no available browser instance, so interactive visual QA remains explicitly deferred rather than represented as completed.

## Phase 5 acceptance checklist

- [x] Only administrators can access staff management, office settings, reports, exports, and audit history
- [x] Administrators can search, filter, paginate, create, edit, activate, deactivate, promote, demote, and reset passwords for internal accounts
- [x] An administrator cannot deactivate or demote their own account, and at least one active administrator must remain
- [x] Deactivating staff transactionally releases their open assignments while preserving closed-request and activity history
- [x] Bilingual office name/address and public contact details are editable and reflected in the resident-facing footer
- [x] Retention is validated from 30 to 3,650 days and drives a non-overlapping daily cleanup command
- [x] Reports aggregate totals, open, overdue, completed, completion rate, resolution time, status, service workload, and daily volume
- [x] Report queries explicitly omit resident-submitted PII and internal notes
- [x] CSV exports use fixed operational columns, neutralize spreadsheet formulas, remove line controls, and return private no-store responses
- [x] Audit actions and metadata keys are centrally allow-listed and omit resident PII and note contents
- [x] Service, request-operation, staff, settings, export, and retention audit events are append-only through application routes
- [x] Request and service audits commit in the same transaction as the action they describe
- [x] Retention dry runs preserve data; real cleanup deletes only expired closed requests and their private files while preserving recent/open requests and audit history
- [x] Fictional inactive-staff and sanitized-audit seed data is available locally and disabled in production
- [x] Clean migrations and seed data pass
- [x] Automated tests, Pint, Larastan, Prettier, ESLint, TypeScript, dependency audits, and production build pass

Phase 5 verification result: 85 automated tests with 753 assertions passed. Clean migrations/seeding, the daily scheduler definition, PHP and frontend formatting, static analysis, TypeScript, frontend linting, production dependency audits, the production build, and live HTTP smoke checks passed. The in-app browser reported no available browser instance, so interactive visual QA remains explicitly deferred rather than represented as completed.

## Phase 6 acceptance checklist

- [x] Browser-safe 403, 404, 419, 429, 500, and 503 pages are bilingual, accessible, non-indexable, non-cacheable, and omit exception details
- [x] JSON clients retain machine-readable framework errors without receiving the Inertia error UI
- [x] Error rendering is independent of office/database shared data so it remains viable during backend failures
- [x] Security headers apply globally, including the health endpoint, with tighter permissions, resource isolation, referrer, framing, and content controls
- [x] Sensitive resident and tracking inputs are excluded from Laravel's server-side validation flash bag
- [x] Public submission/tracking/download limits are supplemented by per-account staff and administrator mutation limits
- [x] Public, authentication, and staff shells provide skip links, focusable main landmarks, and route-title announcements
- [x] Resident validation summaries receive focus and form controls expose invalid/error/help relationships to assistive technology
- [x] Reduced-motion and forced-colors preferences are supported, touch behavior is improved, and report tables/charts have non-visual equivalents
- [x] Privacy and accessibility copy reflects the implemented retention, audit, verification, and known-testing boundaries
- [x] End-to-end regression coverage verifies resident submission, staff processing, and redacted public tracking in one workflow
- [x] Error handling, PII flashing, authenticated abuse limits, security headers, and production error redaction have dedicated regression tests
- [x] Automated tests, Pint, Larastan, Prettier, ESLint, TypeScript, dependency audits, production build, and live HTTP smoke checks pass

Phase 6 verification result: 95 automated tests with 1,043 assertions passed. PHP and frontend formatting, static analysis, TypeScript, frontend linting, production dependency audits, the production build, and live HTTP smoke checks passed. The in-app browser again reported no available browser instance, so interactive keyboard, assistive-technology, and cross-browser visual QA remains explicitly deferred rather than represented as completed.

## Overall MVP definition of done

The complete MVP requires all seven phases, end-to-end resident and staff workflows, server-enforced authorization, public-data redaction, passing automated and static checks, valid deployment configuration, documentation that matches the implementation, and fictional demonstration data only.
