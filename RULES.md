# Project Rules

The product source of truth is `doxs/2026_Women_Entrepreneurs_Platform.docx`. Markdown files in `doxs/` provide the agreed technical interpretation and must not contradict that source.

## Stack
- Use `Laravel` + `Vue 3` + `Inertia.js` as the base stack.
- Keep the app as a modular monolith for the first funded release.
- Use `MySQL 8`, `Redis`, `Meilisearch`, and S3-compatible storage.

## Backend
- Keep controllers thin.
- Put validation in `Form Request` classes.
- Move growing business logic into actions or services.
- Use migrations, factories, and seeders for data changes.

## API
- Use the `/api/v1` prefix for REST endpoints.
- Return JSON in UTF-8.
- Validate all input server-side with localized errors.
- Use cursor pagination for large lists.
- Make webhook and critical POST handlers idempotent.

## Data and Privacy
- Use UTC and ISO 8601 for stored dates.
- Enforce RBAC and model-level access policies.
- Hide personal data by default.
- Treat moderation and visibility rules as first-class requirements.

## UI
- Build mobile-first screens.
- Support `RU`, `RO`, and `EN`.
- Keep accessibility at `WCAG 2.1 AA` level.

## Quality
- Put background work into queues.
- Test critical flows: auth, profile, moderation, events, mentoring, notifications.
- Keep AI behind an optional adapter with a rule-based fallback.

## First Release Priority
- Build the foundation first: auth, roles, localization, CMS, admin.
- Then add community, directory, learning, events, opportunities, mentoring, and notifications.
- Include basic AI-supported search and recommendations; delay advanced AI features until enough quality profiles and content exist.
