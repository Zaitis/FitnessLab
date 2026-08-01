# ADR-001: Separate Laravel API and React SPA in one monorepo

Date: 2026-07-31
Status: Accepted

## Context

`.ai/architecture.md` defaults to "a Laravel monolith plus React where
interactivity demands it", which in practice means Inertia.js or Blade with
React islands. FitnessLab needs a decision on how the two frameworks meet.

The project's purpose shapes the answer. FitnessLab is a public portfolio
codebase whose value is in demonstrating engineering practice — API design,
typed frontend, layered testing — not in shipping the smallest possible
amount of code. That reweights the usual trade-off toward the option that
exercises more distinct, separately assessable skills.

## Options considered

1. **Inertia.js** — one deployable unit, React as the view layer, routing
   and auth handled by Laravel.
   *Pros:* least boilerplate, no CORS, no separate auth wiring, fastest to a
   working screen.
   *Cons:* there is no API to design; request/response contracts, status
   codes, and resource serialisation never become explicit. The frontend is
   not independently runnable or testable.

2. **Blade with React islands** — server-rendered pages, React only for
   interactive fragments such as the progress chart and adherence calendar.
   *Pros:* simplest possible setup, good SEO on the landing page.
   *Cons:* a mixed rendering model, split UI conventions, and very little
   contiguous React to show. Poor fit for a dashboard-heavy application.

3. **Laravel API plus a separate React SPA (Vite), one monorepo** — REST/JSON
   between them, Sanctum cookie session for auth.
   *Pros:* forces an explicit API contract (Form Requests, API Resources,
   status codes, authorization); frontend and backend are independently
   testable and independently reviewable; mirrors the split most teams
   actually run.
   *Cons:* cross-origin auth setup (stateful domains, CSRF, CORS) is a real
   source of friction; two dev processes; the landing page loses
   server-side rendering.

## Decision

Option 3, with both applications in a single repository under `backend/`
and `frontend/`.

The separated split is chosen because the API boundary is itself a
deliverable of this project: it is where validation, authorization, and
serialisation become visible rather than implicit. The extra configuration
cost is one-time and well documented upstream.

The monorepo qualifier is chosen against splitting into two repositories.
The applications share a release cadence, a CI pipeline, an end-to-end
suite that spans both, and a single `docker compose up`. Two repositories
would buy independent versioning that nothing here needs, at the cost of
requiring two clones before anything runs.

## Consequences

- **Easier:** the API contract is explicit and reviewable; Playwright can
  drive the real stack; frontend and backend test suites stay independent;
  a single clone and one command starts everything.
- **Harder:** Sanctum stateful-domain, CSRF, and CORS configuration must be
  correct in local, CI, and production environments — three places to get
  wrong. Local development runs two processes.
- **Given up:** server-side rendering. The landing page is client-rendered,
  so its content is not visible to non-JavaScript crawlers. Acceptable for a
  demo whose audience arrives from a direct link, and revisitable later if
  SEO becomes a goal.
- **Committed to:** API Resources rather than raw models in responses
  (`.ai/laravel.md`), and a single API client module on the frontend rather
  than scattered `fetch` calls (`.ai/react.md`).
