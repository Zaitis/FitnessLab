# Security Review — 2026-08-04

A full-project security audit carried out after M8, against the checklist in
`.ai/security.md`. It covers the application code, the deployment topology,
the CI pipeline, and the repository itself.

Findings are ordered by severity, using the labels from `.ai/code-review.md`:
**[blocker]** for bugs, security issues and data loss; **[should]** for
maintainability problems and missing coverage of core logic.

Every finding below except **S-1** was fixed in the same pass. S-1 was a
missing feature rather than a defect in existing code, and is now built —
see [Resolution](#resolution).

Scope note: this was a code and configuration audit. It did not include
penetration testing, dependency supply-chain review beyond advisory
databases, or a review of the VPS host itself (SSH configuration, fail2ban,
rootkit scanning — see the VPS section of `.ai/security.md`, which is
tracked separately).

---

## Summary

| ID | Severity | Finding | Status |
|---|---|---|---|
| S-1 | blocker | Password reset unusable — emailed link had no route | Fixed |
| S-2 | blocker | No rate limiting on register / forgot-password / reset-password | Fixed |
| S-3 | blocker | User enumeration via `POST /api/forgot-password` | Fixed |
| S-4 | blocker | No security headers on either hostname | Fixed |
| S-5 | should | No dependency audit in CI; a real high-severity advisory was live | Fixed |
| S-6 | should | Log redaction missed compound keys, incl. `password_confirmation` | Fixed |
| S-7 | should | Ownership checks duplicated across three controllers, no policies | Fixed |
| S-8 | should | Repository root `.gitignore` did not ignore `.env` | Fixed |

---

## S-1 — Password reset was unusable end to end **[blocker]**

**What was wrong.** The backend was complete and covered by feature tests:
`/api/forgot-password` and `/api/reset-password` both worked, the
notification was queued, and `ResetPassword::createUrlUsing` built a link to
`{frontend_url}/password-reset/{token}?email=…`.

The SPA had no `/password-reset/:token` route. The emailed link fell through
to the catch-all `*` route and rendered the 404 page. There was also no
"forgot your password?" link anywhere in the interface, so the flow could not
be started from the UI either.

**Impact.** A user of the live site who forgot their password could not
recover their account by any route. The roadmap's M4 definition of done —
"a password-reset email arrives in an inbox" — was satisfied literally while
the feature it belonged to was non-functional.

**Why the test suite did not catch it.** The gap sat exactly on the seam
between the two applications. Backend feature tests assert the endpoints and
the queued notification, and they all passed. Frontend component tests mount
components directly and never exercise the router's route table. Nothing in
either suite asserts that a URL the backend *generates* resolves to a screen
the frontend *serves*. This is the class of defect the M11 end-to-end suite
exists to catch, and it is the strongest argument in the project for not
deferring E2E further.

**Fix.** Built `ForgotPasswordPage` and `ResetPasswordPage`, registered
`/forgot-password` and `/password-reset/:token`, and added the entry link to
`LoginPage`. The reset page reads the token from the path and the email from
the query string, matching the URL the backend builds.

Two details worth recording:

- An expired or already-used token comes back from Laravel as a validation
  error on the `email` field — a field this form deliberately does not show,
  because it comes from the link rather than the user. Applied naively it
  would render nowhere. It is surfaced as a banner with a link to request a
  fresh email.
- A link missing its `email` parameter renders an explanation instead of a
  form that could only ever fail on submit.

**Verified.** Against the real backend, not mocks: generated a genuine reset
token, opened the exact URL the email builds, set a new password, was
redirected to login, and signed in with the new password. Replaying the same
consumed link then produced the expected invalid-token banner.

---

## S-2 — No rate limiting on the unauthenticated write surface **[blocker]**

**What was wrong.** Only `POST /api/bmi/calculate` (10/min per IP) and login
(Breeze's per-email+IP lockout inside `LoginRequest`) were throttled.
`register`, `forgot-password` and `reset-password` had no limit at all.

**Impact.** In order of severity:

- **`forgot-password` was an open mail relay.** Anyone could drive unlimited
  outbound email to arbitrary addresses through `fitnesslab@zaitis.dev`. The
  damage is not to this application — it is to the sending reputation of
  `zaitis.dev`, the asset M0 deliberately built by setting up SPF, DKIM and
  DMARC, and which every other project sending mail from that domain shares.
- **`reset-password` allowed unlimited token guessing.**
- **`register` allowed unlimited automated account creation.**

**Fix.** Two named limiters in `AppServiceProvider`:

| Limiter | Applied to | Limit |
|---|---|---|
| `register` | `POST /api/register` | 5/hour per IP |
| `auth` | `login`, `forgot-password`, `reset-password` | 10/min per IP **and** 5/hour per email |

The `auth` limiter is deliberately two-dimensional. A per-IP limit alone is
defeated by a rotating IP pool; a per-email limit alone is defeated by a
credential-stuffing run that tries each address once. Login keeps Breeze's
own lockout as well, because that one answers a different question —
protecting a *single account* from a guessing run, rather than protecting the
*service* from bulk abuse.

**Verified.** `AuthRateLimitTest` asserts 429 past the limit on both
`forgot-password` and `register`.

---

## S-3 — User enumeration on `forgot-password` **[blocker]**

**What was wrong.** Laravel Breeze ships this endpoint returning `422` with a
validation error when the address has no account, and `200` when it does.

**Impact.** A free oracle for "does this person have a FitnessLab account?",
one address at a time — and, combined with S-2, at unlimited speed. Account
existence is personal information in a health-adjacent product.

**Fix.** The endpoint now returns an identical status and body in both cases.
Nothing in the interface needed the distinction: the user-facing copy is
"if that address has an account, a link is on its way" either way.

**Verified.** A feature test asserts the two responses are byte-identical. A
component test asserts the *UI* wording is identical too — the endpoint being
non-committal is worth nothing if the screen in front of it announces which
branch was taken.

---

## S-4 — No security headers on either hostname **[blocker]**

**What was wrong.** Neither the host nginx configs in `deploy/nginx/` nor the
frontend container's own nginx set any of: Content-Security-Policy,
Strict-Transport-Security, X-Content-Type-Options, X-Frame-Options,
Referrer-Policy. `.ai/security.md` lists CSP, X-Content-Type-Options,
X-Frame-Options and HSTS as baseline requirements.

**A specific trap worth recording:** `certbot --nginx` adds the TLS server
block and the HTTP→HTTPS redirect but **never adds HSTS**. A configuration
that looks finished after running certbot still has no HSTS. This is easy to
assume away, because the padlock appears and the redirect works.

**Fix.** Split across two layers, deliberately:

- **HSTS on both hostnames**, in the host configs. Both, because the session
  cookie is scoped to `.zaitis.dev` per
  [ADR-004](adr/ADR-004-deployment-topology.md) — a protocol downgrade on
  either subdomain exposes the same cookie. `includeSubDomains` is
  deliberately omitted: it would commit every unrelated project under
  `zaitis.dev` to HTTPS-only, which is not this project's decision to make.
- **CSP and the rest in the frontend container**, because that is the only
  layer that knows what the SPA actually loads. The policy needs no
  `unsafe-eval` and no wildcard host; `connect-src` names the API origin
  explicitly, since the SPA and API are separate subdomains.

**A second nginx trap, found while fixing the first:** `add_header` does not
merge with enclosing blocks. Any `location` that sets one header silently
drops every header inherited from the `server` block. `location /assets/`
set `Cache-Control`, which would have made the hashed JS and CSS bundles the
only responses served without `nosniff`. The header is repeated there.

**Verified.** `nginx -t` on all three configs, then the built SPA served
through the real config in a container: every expected header present on `/`,
`nosniff` confirmed present on `/assets/`, and the application renders fully
under the CSP — React mounts, fonts load, zero violations reported.

---

## S-5 — No dependency audit in CI, and a live high-severity advisory **[should]**

**What was wrong.** `.ai/security.md` requires `composer audit` / `npm audit`
in CI. Neither was present.

Adding `npm audit` immediately surfaced two high-severity findings:
[GHSA-qwww-vcr4-c8h2](https://github.com/advisories/GHSA-qwww-vcr4-c8h2)
against `react-router` — every release from 7.12 to 8.2, and the project was
on 7.18.2.

**Assessment.** The advisory is a CSRF bypass in **RSC mode**. This
application is a client-rendered SPA that never runs React Server
Components, so it was not exploitable here. Recording that assessment matters
as much as the fix: an audit gate is only sustainable if findings get
triaged rather than reflexively silenced.

**Fix.** Both gates added, and the project migrated to React Router v8 (which
merges `react-router-dom` back into `react-router`). The upgrade was taken
despite the advisory not being exploitable, because carrying a permanent
audit exception costs more than a migration that turned out to be one import
specifier per file.

`npm audit` runs at `--audit-level=high` rather than the default `low`. A
gate that fails on an unfixable transitive advisory in a dev-only toolchain
package gets switched off, which is worse than a narrower gate that stays on.

**Verified.** `composer audit` and `npm audit` both report zero
vulnerabilities. The router migration was checked in a browser as well as by
the test suite — a routing major can pass component tests that use
`MemoryRouter` and still break `BrowserRouter` at runtime. Client-side
navigation, deep links into nested protected routes, and the 404 catch-all
were all confirmed working.

---

## S-6 — Log redaction missed compound keys **[should]**

**What was wrong.** `DatabaseLogHandler` matched sensitive context keys by
**exact equality** against `['password', 'token', 'authorization', 'cookie']`.

**Impact.** `password` was redacted while `password_confirmation` — the same
secret, and this application's own registration payload key — was written to
the `error_logs` table in plaintext, where the admin log viewer would display
it. The same applied to `access_token`, `api_key` and `X-XSRF-TOKEN`.

This partially defeated the guarantee
[ARCHITECTURE.md](ARCHITECTURE.md) makes about the log viewer: that secrets
are redacted on write, so a later change to the viewer cannot leak what was
never stored.

**Fix.** Substring matching against a fragment list, extended with `secret`,
`api_key` and `apikey`. Recursion into nested arrays was already correct and
is unchanged.

**Verified.** A regression test covers exactly the keys that leaked.

---

## S-7 — Ownership checks duplicated, no policies **[should]**

**What was wrong.** `WorkoutPlanController`, `NutritionPlanController` and
`AdherenceController` each hand-rolled the same check and the same literal
response:

```php
if ($plan->user_id !== $request->user()->id) {
    return response()->json(['message' => 'Forbidden.'], 403);
}
```

`.ai/laravel.md` requires authorizing every non-public action with policies.
Three copies of an authorization rule is three places for it to drift, and
the fourth controller to be written is the one that forgets it.

**A related gap in the same area:** `AdherenceController` verified that the
*plan* belonged to the caller, but never that the submitted `plan_item_id`
belonged to that plan. A user could record adherence against an arbitrary
UUID. Low impact — it only corrupts the caller's own data — but it is an
integrity hole that the database's unique constraint cannot catch.

**Fix.** `WorkoutPlanPolicy` and `NutritionPlanPolicy`, invoked through
`Gate::authorize`. `ToggleAdherenceAction` now validates the item against the
plan's snapshot and returns `422` if it does not belong.

`firstOrCreate` in the same action was also replaced with `updateOrCreate`:
under a concurrent double-submit the former lost the race to the unique
constraint and surfaced a `500` instead of an idempotent success.

**Verified.** Cross-user `403` tests still pass for both plan types; a new
test covers the foreign-item-id `422`.

---

## S-8 — Repository root `.gitignore` did not ignore `.env` **[should]**

**What was wrong.** The root `.gitignore` listed only `.ai`, `.claude`,
`.cursor` and `CLAUDE.md`. `backend/.gitignore` and `frontend/.gitignore`
each ignored their own `.env`, but the repository root did not.

**Impact.** The production VPS keeps the PostgreSQL superuser password in a
root-level `.env` beside `docker-compose.prod.yml`, in the same working tree
where the deploy runs `git pull`. The file sat untracked but not ignored —
one `git add -A` away from being committed. Committing secrets is listed as a
red line in `.ai/security.md`.

**Fix.** Root `.gitignore` now ignores `.env` and `.env.*`, with explicit
negations for the two committed `*.example` templates.

---

## Resolution

All eight findings are fixed. Verification totals after the pass:

| Gate | Result |
|---|---|
| Backend tests (Pest) | 228 passed, 2551 assertions |
| Static analysis (Larastan, level 6) | clean |
| Code style (Pint) | clean |
| Frontend tests (Vitest) | 33 passed |
| Type check (`tsc -b`) | clean |
| Lint (oxlint) / format (Prettier) | clean |
| `composer audit` / `npm audit` | 0 vulnerabilities |
| `nginx -t`, all three configs | valid |
| CSP against the built SPA | renders, 0 violations |

---

## Accepted risks

Unchanged by this review, and deliberate:

- **Session cookie scoped to `.zaitis.dev`.** Every other project on the
  parent domain receives it. Reviewed and accepted in
  [ADR-004](adr/ADR-004-deployment-topology.md); the distinct
  `SESSION_COOKIE` name and exact-match `SANCTUM_STATEFUL_DOMAINS` are the
  mitigations.
- **No database backups.** The terms promise no retention and the only data
  at risk is demo data ([ROADMAP.md](ROADMAP.md)). This becomes wrong the
  moment anyone treats the site as somewhere to keep something.
- **No external error monitoring.** One server, one maintainer, no paying
  users; the trade is documented in [ARCHITECTURE.md](ARCHITECTURE.md). There
  are no alerts, because nobody is on call.
- **Email verification is not enforced.** Deliberate — a demo whose dashboard
  is unreachable until an email arrives is a demo held hostage by a spam
  filter.

## Open items

- **End-to-end tests (M11).** S-1 is the concrete argument for them: it was a
  cross-application defect that neither suite could see, live on production,
  while every gate was green.
- **VPS host hardening** — SSH key-only auth, `fail2ban`, per-project runtime
  isolation, periodic rootkit scanning. Required by `.ai/security.md` and out
  of scope for a code audit; needs a separate pass on the server itself.
- **No Subresource Integrity or CSP nonces.** Not applicable while all
  scripts are same-origin and no third-party CDN is used. Revisit if that
  changes.
