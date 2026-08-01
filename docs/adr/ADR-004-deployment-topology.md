# ADR-004: Single-server deployment across two subdomains

Date: 2026-07-31
Status: Accepted

## Context

[ADR-001](ADR-001-api-spa-split.md) separates the React SPA from the Laravel
API, and [ADR-002](ADR-002-spa-cookie-auth.md) authenticates them with a
session cookie. A cookie is only sent to an origin the browser considers
related, so the hosting layout is not a free choice — it is constrained by
the auth decision.

The available infrastructure is a single VPS, and the parent domain
`zaitis.dev` already hosts other personal projects.

## Options considered

1. **Two subdomains: `fitnesslab.zaitis.dev` (SPA) and
   `fitnesslab-api.zaitis.dev` (API).**
   *Pros:* the API is visibly a separate, addressable service, which makes
   the architecture legible to anyone inspecting the deployment; the two can
   later move to different hosts without changing URLs.
   *Cons:* cross-origin. Requires CORS with credentials, and the session
   cookie must be scoped to `.zaitis.dev` so both subdomains receive it —
   which means every other application under `zaitis.dev` receives it too.

2. **One origin, API behind a path: `fitnesslab.zaitis.dev/api`.**
   *Pros:* no CORS, no cross-origin CSRF choreography, and the session cookie
   scopes to a single host — it never reaches any other project. Strictly the
   safer and simpler arrangement.
   *Cons:* the API stops being independently addressable, and the split that
   ADR-001 exists to demonstrate becomes invisible from the outside.

## Decision

Option 1 — `fitnesslab.zaitis.dev` and `fitnesslab-api.zaitis.dev`, both
served by one nginx instance on one VPS, with Let's Encrypt certificates for
each host.

Both are subdomains of the same registrable domain, so the session cookie
works as ADR-002 requires. The visible service split is accepted as worth
the cross-origin configuration cost, given that demonstrating the
API/SPA boundary is a stated purpose of the project.

## Consequences

### Cookie scope crosses project boundaries

This is the significant cost, and it is a security consideration rather than
an inconvenience.

For one cookie to reach both hosts, `SESSION_DOMAIN` must be `.zaitis.dev`.
The browser will then attach FitnessLab's session cookie to requests for
**every** subdomain of `zaitis.dev`, including unrelated projects. The cookie
stays `HttpOnly`, so scripts on those subdomains cannot read it, but their
servers do receive it in request headers. The converse also holds: any
subdomain under `zaitis.dev` can set cookies scoped to the parent domain,
which is the precondition for session-fixation style attacks against
FitnessLab.

Mitigations adopted:

- `SESSION_SECURE_COOKIE=true` and `SESSION_SAME_SITE=lax`.
- `SESSION_COOKIE` named distinctly (`fitnesslab_session`) so it cannot
  collide with another project's cookie under the shared domain.
- `SANCTUM_STATEFUL_DOMAINS` and the CORS allow-list name
  `fitnesslab.zaitis.dev` exactly — never a wildcard over `*.zaitis.dev`.
- Sessions stored in Redis under a FitnessLab-specific key prefix, so no
  session data is shared even if another application on the box uses the
  same Redis instance.

The residual risk was reviewed and **explicitly accepted** by the domain
owner, on the basis that everything under `zaitis.dev` is operated by one
person and no third party can obtain a subdomain there. It is recorded here
rather than left implicit so that the acceptance is visibly a decision and
not an oversight.

**This arrangement would not be acceptable on a domain with untrusted
subdomains** — for instance one offering user-controlled subdomains. Anyone
reusing this layout should re-evaluate rather than inherit the acceptance.

Reverting to option 2 later costs one nginx location block, one frontend base
URL, and a narrowed `SESSION_DOMAIN`; no application code changes.

### Other consequences

- **Easier:** the API is independently addressable and documented at its own
  host; nginx terminates TLS once for both; a single Compose stack holds the
  whole system.
- **Harder:** CORS, stateful domains, and cookie attributes must be correct
  in local, CI, and production — and a mistake presents as a `419` or `401`
  rather than a descriptive error. E2E tests run against both origins.
- **Single point of failure:** one VPS hosting nginx, PHP-FPM, PostgreSQL,
  and Redis. Appropriate for a demonstration project; explicitly not a
  production-grade topology.
