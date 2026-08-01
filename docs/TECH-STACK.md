# Tech Stack

Every dependency in FitnessLab, why it is there, and what was considered
instead. Decisions expensive to reverse are promoted to [ADRs](adr/);
everything else is recorded here.

The selection bias throughout: prefer boring, widely-used tools that a
reviewer recognises immediately, and add a dependency only when it removes
work that would otherwise be written by hand.

---

## Backend

### Laravel 12 · PHP 8.4 · PostgreSQL 16

PostgreSQL over MySQL for one concrete reason: generated plans are stored as
JSONB snapshots ([ADR-003](adr/ADR-003-plan-snapshots.md)), and PostgreSQL's
JSONB support — indexing, containment operators, path queries — is
materially better than MySQL's JSON type. The rest of the schema would run
equally well on either.

PHP 8.4 enables the language features the domain layer leans on: `readonly`
classes for value objects, backed enums for categories and goals,
constructor property promotion, and property hooks where they simplify
accessors.

### Laravel Sanctum + Breeze — authentication

Laravel splits authentication across complementary packages, so this is two
choices rather than one:

- **Sanctum** is the mechanism that establishes and verifies the session.
  Used in SPA mode — a cookie session rather than bearer tokens.
- **Breeze (API mode)** is the scaffolding on top: registration, login,
  logout, password reset, and email verification as JSON endpoints, with no
  Blade views.

Passport, Fortify, and Jetstream were each considered and rejected. Full
reasoning, including a table of what each package actually does, is in
[ADR-002](adr/ADR-002-spa-cookie-auth.md).

Rejected alternative: hand-rolling these endpoints. Writing custom
authentication is the standard way to introduce a security bug into an
otherwise sound application, and nothing about FitnessLab's requirements
differs from the framework default.

### dompdf (`barryvdh/laravel-dompdf`) — PDF export

Chosen because it is pure PHP with no external binary. The alternatives all
carry an operational cost that outweighs their advantages at this scale:

| Option | Assessment |
|---|---|
| **dompdf** | Pure PHP, no binary, renders HTML/CSS. Limited CSS support, but the watermark is a rotated absolutely-positioned element with opacity — comfortably within its capabilities. **Chosen.** |
| Browsershot | Renders through headless Chromium, so CSS fidelity is essentially perfect. Requires Chromium in the Docker image and on the VPS — hundreds of megabytes and a recurring source of deployment breakage. Rejected: fidelity is not the constraint here. |
| wkhtmltopdf / Snappy | Another external binary, and upstream is archived. Rejected. |
| Client-side (`react-pdf`, `jsPDF`) | Moves generation off the server entirely. Rejected because export must be authorization-checked server-side — a client-generated PDF cannot enforce that only a plan's owner may download it. |

Behind `PdfExporterInterface`, so this choice is reversible without touching
domain code.

### Pest 3 — testing

Over PHPUnit, primarily for two features this project uses heavily:
**datasets**, which turn the BMI-threshold and plan-matrix tests into tables
rather than repeated test bodies, and the **architecture plugin**, which
converts the module boundaries in [Architecture](ARCHITECTURE.md) into build
failures. Pest runs on PHPUnit underneath, so nothing is given up.

### Larastan · Laravel Pint

Static analysis at level 6 minimum per `.ai/laravel.md`, and PSR-12
formatting. Both run in CI, so style is never a review topic.

### spatie/laravel-translatable — catalogue translations

Stores translated catalogue fields as JSONB maps of locale to string, so a
new language is a data change rather than a migration across every
translatable table. Chosen over parallel `name_pl` / `name_en` columns and
over a normalised translations table; the comparison is in
[ADR-005](adr/ADR-005-internationalisation.md).

Laravel's own `lang/` directory covers validation and framework messages,
with Polish translations taken from **`laravel-lang/common`** rather than
hand-written — translating a framework's validation strings by hand is
avoidable work with an avoidable error rate.

### Mail — existing SMTP on `zaitis.dev`

Laravel's standard SMTP driver against the mail service already running for
the domain, sending as `fitnesslab@zaitis.dev`. No new provider is
introduced, since the requirement is a handful of password-reset and
verification messages.

Two operational caveats, both recorded as M0 items rather than left to
discovery at launch: **SPF, DKIM, and DMARC** records must be present on
`zaitis.dev` or reset mail lands in spam, and shared mail services often
impose per-hour sending limits that a queue worker retrying a failed batch
can trip.

### Scramble (`dedoc/scramble`) — API documentation

Generates an OpenAPI specification from existing type hints, Form Requests,
and API Resources without annotation comments. Included because a documented
API is part of what [ADR-001](adr/ADR-001-api-spa-split.md) claims to
demonstrate, and the marginal cost here is a config file.

Rejected: Swagger-PHP with docblock annotations, which duplicates in comments
what the code already states and drifts as soon as someone forgets to update
it.

### Redis — sessions, cache, rate limiting, queue

The default queue and cache driver in `.ai/architecture.md`. Four distinct
concerns in FitnessLab need a shared, out-of-process store, and Redis serves
all four rather than introducing a different backend for each:

**1. Session storage.** [ADR-002](adr/ADR-002-spa-cookie-auth.md) chooses a
cookie session, which means session *state* lives on the server. The default
`file` driver writes it to local disk, which ties sessions to one container's
filesystem — they vanish on redeploy, logging everyone out. Redis makes
sessions survive container replacement and stay consistent across PHP-FPM
workers.

**2. Rate limiting.** `POST /api/bmi/calculate` is public and throttled per
IP. Laravel's rate limiter counts through the cache store, so with a
per-process driver each PHP-FPM worker keeps its own counter and the
effective limit multiplies by the worker count. A shared store is what makes
the limit mean what it says.

**3. Cache.** `GET /api/disclaimer` and the exercise and meal catalogues are
read constantly and change almost never — exactly what a cache is for.

**4. Queue.** Outbound mail from Breeze — password reset and email
verification — involves an SMTP round trip that must not sit inside the
user's request. Queued mail is the textbook case for a worker, and it keeps
registration responsive regardless of how the mail provider is behaving.

A `redis` container and one `queue:work` worker under supervision join the
Compose stack. Keys are namespaced with a FitnessLab-specific prefix, which
also matters for the shared-domain arrangement in
[ADR-004](adr/ADR-004-deployment-topology.md).

**PDF export stays synchronous.** `.ai/laravel.md` puts work slower than
~500 ms on a queue; a single-page plan PDF renders well inside that, and
queuing it would replace a direct download with job dispatch, polling, and
temporary file storage — real complexity for no user-visible gain. The
trigger to revisit is explicit and measured: if generation crosses ~500 ms,
export moves to a job. Redis being available does not by itself justify
routing work through it.

---

## Frontend

### React 19 · TypeScript (strict) · Vite

`strict: true` with no `any`, per `.ai/react.md`. API response types are
declared explicitly rather than inferred from `fetch`.

### React Router v7 — routing

Chosen over TanStack Router. TanStack Router has stronger type-safe route
parameters, but React Router is what the overwhelming majority of React
codebases use, and a portfolio project benefits from a reviewer recognising
the routing layer without preamble. FitnessLab's route tree is shallow —
landing, auth, four dashboard sections — so the type-safety advantage has
little to act on.

Used in declarative mode. The framework/data-loader mode overlaps with
TanStack Query and would leave two systems responsible for server state.

### TanStack Query — server state

All server data lives here: measurements, plans, adherence entries, the
disclaimer. Caching, retries, and invalidation are its responsibility, not
hand-written `useEffect` chains (`.ai/react.md`).

### No Zustand — authentication state included

`.ai/react.md` permits a global client store for genuinely global concerns
such as auth. FitnessLab does not add one, because on inspection auth is not
client state at all: the authenticated user is a server resource behind
`GET /api/user`, and the session lives in an `HttpOnly` cookie the frontend
cannot read anyway.

Modelling it as a TanStack Query query means one cache, invalidated on login
and logout, with no risk of a store and the server disagreeing about who is
signed in. Copying server data into a client store is the exact anti-pattern
`.ai/react.md` warns against; auth is not an exception to it.

If a genuinely client-only global concern appears — a theme toggle, say —
that is the point to reconsider.

### react-i18next — interface translations

Polish and English UI copy from JSON resource files, with English as the
fallback for any missing key. Chosen over rolling a translation hook by hand,
which looks trivial until plurals, interpolation, and locale-aware number and
date formatting arrive — all of which the library already handles and all of
which this application needs for weights, dates, and calorie counts.

The alternative of shipping a single language was rejected for the reason set
out in [ADR-005](adr/ADR-005-internationalisation.md): the Polish audience and
the international reviewer audience are both real.

### react-hook-form + zod — forms

Zod schemas are the single definition of client-side validation and are
reused as the source of the corresponding TypeScript types. Backend
validation remains authoritative and independent; the client schema is a
UX affordance, not a security boundary.

### Tailwind CSS v4 + shadcn/ui

shadcn/ui is copy-in source rather than an installed dependency, so
components are readable and modifiable in-repo — which suits a project whose
purpose is to be read. It also supplies the two non-trivial UI pieces
directly:

- **Calendar** (wrapping `react-day-picker`) for the adherence view.
- **Chart** (wrapping **Recharts**) for the weight and BMI trend.

Recharts over Chart.js: it is React-native and composable rather than an
imperative canvas API wrapped in an effect, and the trend chart is a single
line series with reference bands for BMI categories — well within its range.
Rejected: D3 directly, which is the correct tool for bespoke visualisation
and considerable overkill for one line chart.

**date-fns** for calendar arithmetic — tree-shakeable, immutable, and
sufficient. Rejected: Moment.js, which is in maintenance mode and ships a
large mutable-date API.

### MSW — API mocking in tests

Intercepts at the network layer, so component tests exercise the real API
client and query layer with only the server substituted. Mocking the API
client module instead would leave the most failure-prone code — request
construction, error handling, cache invalidation — untested. Reasoning
expanded in [Testing](TESTING.md).

### Vitest + React Testing Library · Playwright

Vitest shares Vite's transform pipeline, so there is no second build
configuration to maintain. Playwright covers four end-to-end journeys;
scope and rationale in [Testing](TESTING.md).

---

## Infrastructure

### Docker Compose

Containers: `postgres`, `redis`, `php-fpm`, `queue` (a `queue:work` worker),
`scheduler` (`schedule:work`, which resets the demo account nightly),
`nginx`, and a `node` container for the frontend dev server. One
`docker compose up` produces a running application, which matters for a
public repository — a reviewer who cannot start the project in one command
will not start it at all.

### GitHub Actions

Runs the full gate list from [Testing](TESTING.md) on every push and pull
request, with status badges in the README. The badges are not decoration:
they are the fastest available evidence that the test suite described in the
documentation actually exists and passes.

### VPS deployment — nginx + Let's Encrypt

Self-hosted on a single VPS rather than a PaaS, serving two hosts:

| Host | Serves |
|---|---|
| `fitnesslab.zaitis.dev` | React SPA — static build |
| `fitnesslab-api.zaitis.dev` | Laravel API via PHP-FPM |

Both are subdomains of one registrable domain, which is what
[ADR-002](adr/ADR-002-spa-cookie-auth.md) requires for the session cookie to
reach both. nginx terminates TLS for both hosts and Certbot handles
certificates. The cookie-scope consequence of this layout, and the mitigations
applied, are documented in
[ADR-004](adr/ADR-004-deployment-topology.md) — worth reading before
configuring `SESSION_DOMAIN`.

Deployment flow: GitHub Actions builds images and pushes them to GHCR; the
VPS pulls and restarts via Compose. Pulling a built, tested image is
reproducible in a way that building from a `git pull` on the server is not.

---

## Considered and rejected

| Option | Why not |
|---|---|
| Inertia.js | Removes the API contract that is a stated deliverable. [ADR-001](adr/ADR-001-api-spa-split.md) |
| Next.js | Would restore SSR for the landing page, but adds a Node server to operate alongside PHP and shifts the project's centre of gravity away from Laravel. |
| Repository pattern | Eloquent is already the abstraction; no second data store exists. [Design Patterns §4](DESIGN-PATTERNS.md) |
| GraphQL | Six or seven endpoints with fixed shapes. Nothing to solve. |
| Passport / Fortify / Jetstream | No third-party OAuth clients; Jetstream imposes a conflicting frontend. [ADR-002](adr/ADR-002-spa-cookie-auth.md) |
| Queued PDF export | Renders well under the 500 ms threshold; queuing would add polling and temporary storage for no user-visible gain. Revisit trigger documented above. |
| Storybook | Genuinely useful for a component library; FitnessLab's UI is mostly composed shadcn primitives, so it would document other people's components. |
| Sentry / hosted error tracking | One server, one maintainer, no paging. A database log channel and an admin-only viewer cover the actual need; reasoning in [Architecture](ARCHITECTURE.md). |
| Database backups | Nothing here is data anyone should keep, and the terms say so. Revisit the moment that stops being true. |
| A real ML model for plan generation | The generators are rule-based on purpose — the demonstration is deterministic, testable domain logic, and a model would make the output unverifiable and the disclaimer harder to honour. |
