# Testing

Testing is a primary deliverable of this project, not a chore attached to
one. The application's domain — BMI thresholds and rule-based plan
generation — is deliberately simple, which is precisely what makes it a
good vehicle for demonstrating a disciplined, layered test suite.

Governing principles from `.ai/testing.md`: test behaviour rather than
implementation, prioritise business logic and integration points over
coverage percentages, keep the suite fast enough that it actually gets run,
and treat a flaky test as a bug.

## The pyramid

| Level | Tool | Scope | Speed target |
|---|---|---|---|
| Unit (backend) | Pest | Value objects, enums, strategies, Actions in isolation | milliseconds |
| Architecture | Pest arch plugin | Structural rules across the whole codebase | milliseconds |
| Feature (backend) | Pest + `RefreshDatabase` on PostgreSQL | Each endpoint: request → database → response | < 30 s total |
| Unit / integration (frontend) | Vitest + React Testing Library + MSW | Components and hooks against a mocked API | < 30 s total |
| End-to-end | Playwright | Four critical journeys through the real stack | minutes, CI-gated |

The shape is intentional: the widest layer is unit tests over the domain
logic, because that is where the interesting rules live and where tests are
cheapest and most stable. E2E is deliberately thin — four journeys, not a
regression net.

## Backend — unit tests

`App\Domain` contains no `Illuminate\*` by architectural rule, enforced by an
architecture test rather than by discipline (see
[Design Patterns](DESIGN-PATTERNS.md)), so these run without the container,
HTTP, or a database.

**BMI calculation and categories.** The category thresholds are the highest-
value target in the codebase because they are pure logic with exact
boundaries. Every threshold gets tested *at* the boundary and on both sides
of it — 18.4 / 18.5 / 18.6, and so on — driven by a Pest dataset rather than
repeated test bodies:

```php
it('assigns the correct category', function (float $bmi, BmiCategory $expected) {
    expect(BmiCategory::forValue($bmi))->toBe($expected);
})->with([
    [18.4, BmiCategory::Underweight],
    [18.5, BmiCategory::Normal],
    // ...
]);
```

**Value object invariants.** `Weight` and `Height` reject implausible input
at construction; tests assert the exception, since that guarantee is what
lets every downstream consumer skip re-validating.

**Plan strategies.** Each strategy is tested through a dataset covering the
matrix of goal × experience level × days per week, asserting structural
properties rather than exact content: the plan has one session per requested
day, no session exceeds the exercise cap for the experience level, every
plan item carries a UUID, and every item carries text in every supported
locale. Asserting structure rather than a fixed exercise list keeps the tests
from breaking every time the catalogue is extended — behaviour, not
implementation.

These tests inject an in-memory `ExerciseCatalogue` holding a small fixed
fixture, so the whole matrix runs without a database. That is the entire
point of the contract described in
[Design Patterns §4](DESIGN-PATTERNS.md): the most-run tests in the suite
stay at unit speed.

**Determinism.** Generators must return identical output for identical
input, so any randomness is injected as a seeded source rather than called
directly. A test asserts that two generations from the same criteria and
seed match exactly.

## Backend — architecture tests

Pest's architecture plugin encodes the boundaries from
[Architecture](ARCHITECTURE.md) as assertions. These are the tests that keep
the design honest once the project grows:

```php
arch('controllers stay thin')
    ->expect('App\Http\Controllers')
    ->not->toUse(['Illuminate\Support\Facades\DB', 'Illuminate\Database\Eloquent\Builder']);

arch('the domain knows nothing of the framework')
    ->expect('App\Domain')
    ->not->toUse('Illuminate');

arch('dependencies point inward')
    ->expect('App\Domain')
    ->not->toUse('App\Infrastructure');

arch('modules stay independent')
    ->expect('App\Domain\Workouts')
    ->not->toUse('App\Domain\Documents');

arch('actions are final and single-purpose')
    ->expect('App\Application')->toBeFinal();

arch('value objects are immutable')
    ->expect('App\Domain\Measurements\ValueObjects')->toBeReadonly();
```

The first two carry the weight. `App\Domain` excluding `Illuminate` entirely
is what makes "the domain is framework-free" a fact rather than an intention
— and it is the rule most likely to be broken by an innocent-looking
`use Illuminate\Support\Collection` during a hurried change.

Also enforced globally: no `dd()`, `dump()`, `ray()`, or `var_dump()` reaches
the main branch, and everything is strictly typed.

## Backend — feature tests

Per `.ai/testing.md`, every endpoint covers three cases at minimum:

1. **Happy path** with realistic data, asserting the response shape.
2. **Validation** rejects bad input with `422` and names the offending field.
3. **Authorization** — a guest gets `401`, and a different user gets `403`.

The third case matters most here. Plans, measurements, and adherence entries
are all user-owned, and every ownership check gets an explicit
cross-user test. A `403` test that was never written is indistinguishable
from a missing policy.

Endpoint-specific coverage worth calling out:

- **`POST /api/bmi/calculate`** — works unauthenticated, persists nothing
  (asserted by counting rows before and after), and returns `429` once the
  rate limit is exceeded. The rate-limit test runs against an array cache
  store cleared between tests, so the counter never leaks between cases.
- **Authentication** — the full register, login, logout cycle; and
  `Queue::fake()` asserting that password-reset and verification mail is
  *queued* rather than sent inline, since a synchronous SMTP call inside the
  request is the regression this guards against.
- **`POST /api/workout-plans`** — the persisted snapshot matches the
  response body, and every plan item has a UUID.
- **Adherence toggling** — the unique constraint on
  `(user_id, entry_date, plan_item_id)` holds; toggling twice returns to the
  original state rather than creating a duplicate row.
- **PDF export** — returns `application/pdf`, and the extracted text
  contains the disclaimer watermark. A separate test asserts a foreign
  user's plan cannot be exported.
- **Disclaimer presence** — every plan response contains the disclaimer
  string, guarding the single-source-of-truth arrangement against an
  accidental removal.

Factories are used for all test data; no manual inserts.

### Internationalisation

Two locales double the surface for a whole class of quiet failures, so three
checks are automated rather than eyeballed:

- **Key parity.** A test asserts the Polish and English resource files expose
  identical key sets, in both applications. English fallback means a missing
  Polish string renders in English rather than throwing — invisible in manual
  testing and caught only by this test.
- **Snapshot locale coverage.** A generated plan is asserted to contain
  catalogue text for *every* supported locale, guarding the arrangement in
  [ADR-005](adr/ADR-005-internationalisation.md) against a generator change
  that quietly persists only the active language.
- **Locale negotiation.** Feature tests assert that a request's
  `Accept-Language` header selects the validation-message language, and that
  an authenticated user's stored `locale` takes precedence over it.

### The admin log viewer

An endpoint that serves application logs is exactly the kind of thing that
quietly becomes a vulnerability, so its guarantees are asserted rather than
assumed:

- A guest receives `401` and an authenticated non-admin receives `403`;
  an admin receives the list. All three cases, since a middleware that
  rejected everyone would pass a test written only for the first two.
- Redaction is tested at the write boundary: an exception carrying a password
  or token in its context is logged, and the stored row is asserted **not**
  to contain the value. Testing this on write rather than on display is the
  point — the guarantee is that the secret was never persisted.
- The pruning job removes entries past the retention window and leaves newer
  ones untouched.

### The demo account

Its credentials are public, so the constraints protecting it are tested like
any other authorization rule:

- Password change, email change, and account deletion each return `403` for
  an `is_demo` user, and succeed for an ordinary one — the second half
  matters, since a middleware that blocks everyone would pass a
  one-sided test.
- The reset job restores the seeded state, asserted by mutating demo data,
  running the job, and comparing against the seed.
- An E2E journey covers the landing-page *Try the demo account* button
  reaching a populated dashboard, because a broken demo login is the single
  most damaging failure this project can ship.

### Infrastructure in tests

**Feature tests run against PostgreSQL, not SQLite.** This is a deliberate
departure from the usual in-memory-SQLite default, and from the wording in
`.ai/testing.md`, for a specific reason: the schema depends on PostgreSQL
JSONB — plan snapshots ([ADR-003](adr/ADR-003-plan-snapshots.md)) and
catalogue translation columns
([ADR-005](adr/ADR-005-internationalisation.md)). SQLite has no JSONB type
and different JSON operator semantics, so a suite green on SQLite would say
nothing about whether the queries work in production. Testing against a
different database than the one shipped is false confidence, and the failure
mode is silent. CI therefore runs a `postgres` service container.

Redis is handled the other way. Sessions and cache use the array driver and
the queue uses `Queue::fake()`, so no Redis service container is needed — the
application's use of Redis is ordinary key–value work with no
Redis-specific semantics to get wrong.

The one exception is deliberate: a single integration test exercises the real
cache-backed rate limiter, because a limiter that silently degrades to a
per-process counter is exactly the failure a faked store would hide.

## Frontend tests

Vitest with React Testing Library, and **MSW** intercepting at the network
layer rather than mocking the API client module. Mocking `fetch` wrappers
tests the mock; intercepting HTTP means the component, the API client, and
the query layer all run for real, and only the server is substituted.

Coverage targets, in priority order:

1. **BMI form** — validation errors render inline for bad input, submit is
   disabled while pending, a result renders on success, and the registration
   call to action appears only after a result exists.
2. **Auth flows** — registration and login render server-side validation
   errors; a protected route redirects an unauthenticated visitor.
3. **Anonymous-result carry-over** — a result held in `sessionStorage` is
   submitted once after registration and then cleared, and is not
   resubmitted on a subsequent reload.
4. **Plan generators** — the three request states (loading, error, data) each
   render, because `.ai/react.md` requires all three handled explicitly.
5. **Adherence calendar** — checking an item issues the mutation, reflects
   optimistically, and rolls back when the request fails.
6. **Progress chart** — renders with an empty history, one point, and many
   points without crashing.

Tests assert what a user perceives — visible text, roles, labels — never
component internals or state variables.

Type checking (`tsc --noEmit`) is part of the test command, not a separate
optional step.

## End-to-end tests

Four journeys through the real stack, chosen because each spans a boundary
that unit and feature tests cannot:

1. **Landing → calculate → register → measurement saved.** The full
   anonymous-to-authenticated carry-over, which crosses `sessionStorage`,
   two origins, and the cookie session.
2. **Generate a plan → export the PDF.** Asserts a real PDF downloads with
   non-trivial content.
3. **Check off a calendar item → reload → still checked.** Verifies
   persistence rather than optimistic UI state.
4. **Try the demo account → populated dashboard.** The path most reviewers
   will actually take, and the one whose failure is most costly.

E2E is kept deliberately thin. Every browser test is slower and more brittle
than the equivalent feature or component test, so the bar for adding one is
that it covers an interaction no cheaper layer can.

## CI gates

GitHub Actions runs on every push and pull request, with a `postgres` service
container. The build fails on any of:

| Gate | Command | Active from |
|---|---|---|
| Code style | `pint --test` | M0 |
| Static analysis | `phpstan analyse` (Larastan, level 6+) | M0 |
| Backend tests | `pest --coverage --min=90` | M0 |
| Frontend lint | `eslint .` | M0 |
| Type check | `tsc --noEmit` | M0 |
| Frontend tests | `vitest run` | M0 |
| E2E | `playwright test` | M11 |

Every gate except E2E is wired up in M0, before feature code exists, so no
milestone can accumulate untested work. E2E is the exception because its
journeys span features that do not exist until late — it is added in M11
rather than sitting green and empty for eleven milestones.

### What the coverage threshold covers

The 90% minimum applies to **`app/Domain` and `app/Application` only**, scoped
through the coverage `<include>` in `phpunit.xml`. Eloquent models, service
providers, migrations, Form Requests, and framework scaffolding are excluded.

This is the difference between a threshold that means something and one that
is gamed. Measured across the whole application, 90% would force tests
asserting that a service provider binds an interface — work that costs time,
adds maintenance, and proves nothing, while a genuine gap in a plan generator
hides inside the same percentage. Scoped to the rings where a missing test
means untested business logic, the number is worth enforcing.

`.ai/testing.md` is explicit that chasing coverage over trivial code is waste.
This scoping is how that principle survives contact with a CI gate.

## Conventions

- Tests are independent and pass in any order; no shared mutable state.
- A bug fix starts with a failing test reproducing the bug.
- Unique-value generators in tests start from randomised values rather than
  fixed low constants, so collisions cannot hide until the full suite runs
  together (`.ai/testing.md`).
- Flaky tests are fixed or deleted, never retried or skipped.
