# Roadmap

Milestones in build order. Each one ends with a working, tested, merged
increment — no milestone is complete while its tests are outstanding, since
the test suite is a deliverable of this project rather than a follow-up to it
(see [Testing](TESTING.md)).

Two principles drive the ordering:

**Deploy early, then grow in public.** The application goes live at M4, with
only the BMI calculator and accounts working. Deployment is the step most
likely to be deferred indefinitely if left until the end, and everything
after it — every later milestone — becomes visible the moment it merges. A
live URL with three features beats a local project with ten.

**From M4 onward, every milestone ends deployed.** Merging to `main` builds
and ships. A milestone that passes CI but never reaches the server is not
done.

Progress is tracked by checking items off in place.

---

## M0 — Foundation and CI

Monorepo skeleton with the quality gates running before any feature code
exists, so no milestone can accumulate untested work.

- [x] `backend/` — Laravel 12, PHP 8.4, PostgreSQL connection configured.
- [x] `frontend/` — Vite, React 19, TypeScript in strict mode.
- [x] `docker-compose.yml` — `postgres`, `redis`, `php-fpm`, `queue`,
      `scheduler`, `nginx`, `node`.
- [x] Redis wired as the session, cache, rate-limiter, and queue store, with
      a FitnessLab-specific key prefix.
- [x] `config/supported_locales.php` (`pl`, `en`); `react-i18next` and
      Laravel `lang/` wired with `laravel-lang/common` for Polish; key-parity
      tests in both applications.
- [x] SMTP configured against the existing `zaitis.dev` mail service, sending
      as `fitnesslab@zaitis.dev`; mail queued, verified with `Queue::fake()`.
- [x] **SPF, DKIM, and DMARC records verified on `zaitis.dev`** — a real test
      message must land in an inbox rather than a spam folder.
- [x] Sanctum installed; `SANCTUM_STATEFUL_DOMAINS`, `SESSION_DOMAIN`, and
      CORS with `supports_credentials` configured for local development.
- [x] Pest, Larastan (level 6), Pint on the backend.
- [x] Vitest, React Testing Library, MSW, oxlint, Prettier on the frontend.
- [x] Tailwind v4 and shadcn/ui initialised.
- [x] GitHub Actions workflow with a `postgres` service container, running
      every gate listed in [Testing](TESTING.md) except E2E; README badges
      wired up.
- [x] `App\Domain` / `App\Application` / `App\Infrastructure` / `App\Http`
      namespaces created, with the architecture tests that enforce them
      failing on a deliberate violation before anything else is built.
- [x] `GET /api/health` endpoint with a feature test.

**Definition of done:** `docker compose up` serves both applications; the
frontend reaches `/api/health` cross-origin without CORS errors; a session
written by one request is readable by the next through Redis; CI passes on a
pull request.

**Version check.** The versions named across this documentation — Laravel 12,
PHP 8.4, React 19, Tailwind 4, Pest 3, PostgreSQL 16 — were current when it
was written. Confirm the current stable major of each at kickoff and update
[Tech Stack](TECH-STACK.md) if any has moved; a documented stack that
disagrees with `composer.json` is worse than no documented stack.

## M1 — BMI domain and public calculator API

The domain core, built framework-free so it can be unit-tested in isolation.

- [x] `Weight` and `Height` value objects rejecting implausible input.
- [x] `BmiCategory` backed enum owning its thresholds.
- [x] `Bmi` readonly value object with `fromMeasurements()`.
- [x] `CalculateBmiAction` — no HTTP or database dependencies.
- [x] `CalculateBmiRequest` Form Request.
- [x] `POST /api/bmi/calculate` — public, stateless, rate-limited per IP.
- [x] Unit tests: every category boundary tested at the threshold and either
      side of it, via a Pest dataset; value-object invariants.
- [x] Feature tests: happy path, `422` on invalid input, `429` past the rate
      limit, and an assertion that no rows are written.
- [x] First architecture tests: `App\Domain` free of `Illuminate`, controllers
      free of database access.

**Definition of done:** the endpoint returns correct categories across the
full BMI range with boundary coverage, and writes nothing.

## M2 — Landing page, disclaimer layer, legal pages

The first demonstrable surface, and the cross-cutting disclaimer machinery
that every later milestone depends on.

- [x] `config/disclaimer.php` and the `DisclaimerText` value object as the
      single source of truth ([Design Patterns §5](DESIGN-PATTERNS.md)).
- [x] `GET /api/disclaimer` — public and cached.
- [x] Landing page: hero, purpose, and a prominent demo disclaimer.
- [x] BMI form — react-hook-form with a zod schema, inline validation errors,
      submit disabled while pending, all three request states handled.
- [x] Result view with weight category, followed by a registration call to
      action.
- [x] Terms and Disclaimer page in both locales, rendered from
      [the source copy](legal/TERMS-AND-DISCLAIMER.md); footer disclaimer and
      link present on every page.
- [x] Language switcher in the layout; anonymous choice persisted to
      `localStorage`.
- [x] Component tests: validation rendering, pending state, result rendering,
      call to action appearing only once a result exists, and the switcher
      changing rendered copy.

**Definition of done:** an anonymous visitor calculates BMI end to end; the
disclaimer text is defined in exactly one place and appears in header,
footer, and result.

## M3 — Authentication and anonymous result carry-over

- [x] Laravel Breeze in API mode: register, login, logout, password reset,
      email verification, `GET /api/user`.
- [x] Outbound mail queued rather than sent inline; `queue:work` worker
      running under Compose.
- [x] `auth:sanctum` applied to protected routes.
- [x] `POST /api/measurements` persisting a measurement for the authenticated
      user.
- [x] Frontend auth modelled as a TanStack Query query — no client store
      ([Tech Stack](TECH-STACK.md)); protected routes redirect guests.
- [x] Anonymous result held in `sessionStorage` and submitted once after
      registration, then cleared.
- [x] `locale` column on `users`, taking precedence over `Accept-Language`.
- [x] Email verification implemented but **not** enforced as a gate on using
      the application.
- [x] Feature tests: guests receive `401` on protected routes; a registered
      user can complete the login and logout cycle; password-reset mail is
      queued rather than sent synchronously, asserted with `Queue::fake()`.
- [x] Component tests: server validation errors render; the carry-over
      submits exactly once and does not repeat on reload.

**Definition of done:** an account can be created either from a calculator
result — which is saved as the first measurement — or independently from the
navigation, and the session survives a page refresh.

## M4 — First deployment: go live

Deliberately placed here rather than at the end. Everything from this point
ships to a public URL as it merges, which is what makes the remaining
milestones worth doing in order.

- [x] Production Dockerfiles for backend and frontend; images built and
      pushed to GHCR by CI.
- [x] DNS: `fitnesslab.zaitis.dev` and `fitnesslab-api.zaitis.dev` pointed at
      the VPS.
- [x] nginx serving both hosts; Certbot certificates for each, with renewal
      verified via `certbot renew --dry-run` rather than assumed.
- [x] Production configuration per
      [ADR-004](adr/ADR-004-deployment-topology.md): `SESSION_DOMAIN`,
      a distinctly named `SESSION_COOKIE`, `SESSION_SECURE_COOKIE=true`,
      exact-match `SANCTUM_STATEFUL_DOMAINS` and CORS allow-list — no
      wildcard over `*.zaitis.dev`.
- [x] Redis and the queue worker running under supervision, restarting with
      the stack. The `scheduler` container is in place but gains its first
      job in M9.
- [x] Deploy on merge to `master`: CI pulls the built image and restarts the
      stack. Verified end-to-end on a real push to `master`.
- [x] Error capture: a `stack` log channel writing `error` and above to both
      the daily file and an `error_logs` table, so a production failure after
      go-live leaves a durable record instead of scrolling off a container's
      stdout. Capture only — the viewer arrives in M5.
- [ ] README updated with the live link and screenshots. **Live link is in;
      screenshots still to come.**

No database backups at this stage, deliberately. The terms promise nothing
about retention, the only data at risk is demo data, and a backup pipeline is
work that does not move the project forward. This becomes wrong the moment
anyone treats the site as somewhere to keep something.

**Definition of done:** the site is publicly reachable over HTTPS in both
locales, a real account can be registered and used on it, a password-reset
email arrives in an inbox, and merging to `master` deploys automatically.

## M5 — Dashboard shell, measurement history, admin log viewer

- [ ] Dashboard layout with navigation across its four user-facing sections,
      plus an admin section rendered only for admins.
- [ ] `GET /api/measurements` — paginated, scoped to the authenticated user.
- [ ] Weight and BMI trend chart (Recharts) with BMI category reference bands.
- [ ] Manual measurement entry from the dashboard.
- [ ] `is_admin` flag on `users`; an admin-only dashboard section listing
      recent entries from `error_logs`, paginated and filterable by level.
- [ ] `GET /api/admin/logs` behind an admin policy, with the context payload
      redacted as described in [Architecture](ARCHITECTURE.md).
- [ ] Scheduled pruning of `error_logs` beyond the retention window.
- [ ] Feature tests: measurement pagination; a cross-user request returns
      `403`; a non-admin requesting the log endpoint returns `403` while an
      admin succeeds; the redaction actually removes what it claims to.
- [ ] Component tests: the chart renders with zero, one, and many points.

**Definition of done:** measurement history is listed and plotted over time,
no user can read another user's measurements, production errors are visible
without SSH access to the server, and it is live.

## M6 — Training plan generator

- [ ] `exercises` catalogue table with a seeder — rules as data, not code;
      user-visible fields as JSONB translation columns via
      `spatie/laravel-translatable`.
- [ ] `WorkoutPlanCriteria` typed input object.
- [ ] `ExerciseCatalogue` contract in `App\Domain`, with an Eloquent
      implementation in `App\Infrastructure` and an in-memory one for tests
      ([Design Patterns §4](DESIGN-PATTERNS.md)).
- [ ] `WorkoutPlanStrategy` interface with implementations for fat loss,
      muscle gain, and maintenance ([Design Patterns §1](DESIGN-PATTERNS.md)).
- [ ] `GenerateWorkoutPlanAction`; snapshot persisted to
      `workout_plans.generated_plan`, with a UUID on every plan item and
      catalogue text embedded for every supported locale
      ([ADR-005](adr/ADR-005-internationalisation.md)).
- [ ] `POST /api/workout-plans`, `GET /api/workout-plans`,
      `GET /api/workout-plans/{id}` with ownership authorization.
- [ ] `WorkoutPlanResource` attaching the disclaimer.
- [ ] Dashboard form and plan view.
- [ ] Unit tests: the goal × level × days matrix as a Pest dataset against an
      in-memory catalogue — no database; determinism for identical input;
      every plan item carries text in every supported locale.
- [ ] Feature tests: happy path, validation, `401`, cross-user `403`,
      snapshot matches the response, disclaimer present.
- [ ] Architecture test: `Workouts` does not reference other modules.

**Definition of done:** every valid input combination produces a coherent
plan, covered by the dataset matrix, with generation logic reachable without
booting HTTP.

## M7 — Meal plan generator

- [ ] `meal_templates` catalogue table with a seeder, translatable like
      `exercises`; `MealTemplateCatalogue` contract and its two
      implementations.
- [ ] `NutritionPlanStrategy` interface and per-goal implementations —
      a parallel hierarchy, not shared with `Workouts`.
- [ ] `GenerateNutritionPlanAction` taking the goal and the latest BMI
      measurement as input; daily calorie target stored in its own column.
- [ ] Endpoints, Resource with disclaimer, and dashboard view mirroring M6.
- [ ] Test coverage mirroring M6.

**Definition of done:** as M6, for meal plans, including the case where a
user has no recorded measurement yet.

## M8 — Adherence calendar

- [ ] `adherence_entries` table with a unique constraint on
      `(user_id, entry_date, plan_item_id)`.
- [ ] `ToggleAdherenceAction`; `POST`/`DELETE /api/adherence` endpoints.
- [ ] `GET /api/adherence?month=` returning a month of entries in one request.
- [ ] Calendar view (shadcn/ui Calendar) with per-day meal and exercise
      check-offs, using optimistic updates.
- [ ] Feature tests: the unique constraint holds; toggling twice returns to
      the original state; cross-user `403`.
- [ ] Component tests: the mutation fires, the optimistic update renders, and
      a failed request rolls back.

**Definition of done:** meals and exercises are checked off per day, the
state survives a reload, and duplicate entries are impossible at the database
level.

## M9 — Demo account

Placed after the features it showcases exist, so the button leads somewhere
worth arriving at.

- [ ] `is_demo` flag on `users`; seeder creating the account with measurement
      history, plans of both kinds, and a partially filled adherence calendar.
- [ ] Middleware rejecting password change, email change, and account
      deletion for `is_demo` users.
- [ ] *Try the demo account* button on the landing page.
- [ ] Nightly scheduled job resetting the demo account to its seeded state;
      the `scheduler` container gets its first real work here.
- [ ] Feature tests: destructive account actions return `403` for the demo
      user and succeed for an ordinary one; the reset job restores seeded
      state after the data is mutated.
- [ ] README updated with the demo credentials.

**Definition of done:** one click from the landing page reaches a populated
dashboard on the live site, and nothing a visitor does there can lock out the
next visitor.

## M10 — PDF export with watermark

- [ ] `PdfExporterInterface` and `PlanExportData` in the domain layer.
- [ ] `DompdfPlanExporter` rendering a diagonal, semi-transparent disclaimer
      watermark across the page, in the requested locale.
- [ ] `ExportPlanToPdfAction`; export endpoints for both plan types with
      ownership authorization.
- [ ] Download action in the plan views.
- [ ] Unit tests: the Action passes the correct `PlanExportData` to a fake
      exporter.
- [ ] Feature tests: `application/pdf` returned, extracted text contains the
      disclaimer, cross-user export returns `403`.
- [ ] Generation time measured on the production host; if it exceeds ~500 ms,
      moved to a queued job per `.ai/laravel.md`.

**Definition of done:** both plan types export as PDFs carrying a visible
disclaimer watermark, downloadable only by their owner.

## M11 — End-to-end suite

Last because its journeys span features that do not exist until now. Adding
them earlier would mean writing browser tests against screens still being
designed.

- [ ] Playwright covering the four journeys in [Testing](TESTING.md).
- [ ] E2E added as a CI gate, running against the built images.
- [ ] Final pass over the README: setup instructions, architecture summary,
      screenshots current.

**Definition of done:** the four end-to-end journeys pass in CI against a
production-equivalent stack.

---

## Deliberately out of scope

- Repository pattern over Eloquent for writes
  ([Design Patterns §4](DESIGN-PATTERNS.md)).
- Queued PDF export, until generation is measured above ~500 ms
  ([Tech Stack](TECH-STACK.md)).
- Server-side rendering ([ADR-001](adr/ADR-001-api-spa-split.md)).
- Machine learning in the generators — the rules are deterministic by design.
- Paid tiers, payments, and subscriptions. The application is free, and the
  disclaimers throughout depend on it staying a demonstration rather than a
  commercial health product.
