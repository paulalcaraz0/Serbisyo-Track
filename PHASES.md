# SerbisyoTrack Delivery Phases

This file is the living implementation roadmap. A phase is marked complete only after its scoped functionality and verification gates pass. Completion of one phase does not mean the full MVP is production-ready.

| Phase | Status | Outcome |
| --- | --- | --- |
| 0. Architecture and planning | Complete | Architecture, data model direction, assumptions, risks, milestones, and acceptance gates defined |
| 1. Application foundation | Complete | Laravel/Inertia/React foundation, bilingual homepage, staff authentication, roles, security headers, quality tooling, SQLite, CI baseline, and documentation |
| 2. Services and public information | Complete | Bilingual service directory/details, privacy/accessibility/help pages, and administrator service management |
| 3. Resident submissions and tracking | Not started | Guided requests, appointments, attachments, secure references, tracking PINs, receipts, and public status tracking |
| 4. Staff operations | Not started | Assignment, workflow transitions, public/private history, internal notes, request timelines, and queued notifications |
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

## Overall MVP definition of done

The complete MVP requires all seven phases, end-to-end resident and staff workflows, server-enforced authorization, public-data redaction, passing automated and static checks, valid deployment configuration, documentation that matches the implementation, and fictional demonstration data only.
