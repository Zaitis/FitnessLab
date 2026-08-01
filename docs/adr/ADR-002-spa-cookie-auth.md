# ADR-002: Cookie-based SPA session via Sanctum, not API tokens

Date: 2026-07-31
Status: Accepted

## Context

[ADR-001](ADR-001-api-spa-split.md) puts the React SPA on a different origin
from the Laravel API, which means authentication has to cross origins.
Laravel Sanctum supports two distinct modes for this, and they are not
interchangeable: a stateful cookie session for first-party SPAs, and
bearer tokens for third-party or non-browser clients.

FitnessLab has exactly one client: its own frontend. There is no mobile
app, no third-party integration, and no machine-to-machine consumer either
now or in the stated roadmap.

## Options considered

1. **Sanctum SPA mode — session cookie + CSRF token.**
   *Pros:* credentials live in an `HttpOnly` cookie that JavaScript cannot
   read, so a successful XSS cannot exfiltrate the session; CSRF protection
   and session invalidation are Laravel's existing, well-tested machinery;
   no token lifecycle code to write.
   *Cons:* requires `SANCTUM_STATEFUL_DOMAINS`, CORS with
   `supports_credentials: true`, an initial `/sanctum/csrf-cookie` call, and
   `credentials: 'include'` on every request. Same-site cookie rules mean
   the two origins must share a registrable domain in production.

2. **Sanctum API tokens — bearer token in `Authorization` header.**
   *Pros:* origin-agnostic, no CSRF or cookie configuration, works
   unchanged for a future mobile client.
   *Cons:* the token has to be stored somewhere the SPA can read it.
   `localStorage` is readable by any injected script, turning any XSS into
   full account takeover; keeping it in memory instead means the session
   dies on every refresh unless a refresh-token mechanism is built. Token
   issuing, rotation, and revocation all become application code.

3. **Laravel Passport — a full OAuth2 authorization server.**
   *Pros:* the correct answer when third parties need delegated access to
   user data, with authorization codes, scopes, and refresh tokens.
   *Cons:* FitnessLab has no third-party clients to delegate to. Passport
   brings an OAuth2 server, its key management, and its token tables to
   solve a problem that does not exist here. Rejected as overengineering.

### A note on package roles

These are frequently confused, because Laravel splits authentication across
complementary packages rather than offering one:

| Package | What it provides | Used here |
|---|---|---|
| **Sanctum** | The *mechanism* that establishes and verifies who is authenticated | Yes — in SPA session mode |
| **Breeze** | *Scaffolding*: ready-made register, login, logout, password-reset, and email-verification endpoints, built on Sanctum | Yes — in API mode, which emits JSON endpoints and no Blade views |
| **Fortify** | Breeze's backend logic without any routes or UI, for hand-built flows | No — Breeze's API mode already fits |
| **Jetstream** | Fortify plus a prescribed Blade or Vue UI, teams, and 2FA | No — it imposes a frontend that would conflict with the React SPA |
| **Passport** | OAuth2 authorization server | No — see option 3 |

Breeze and Sanctum are therefore not alternatives to each other: Breeze
supplies the endpoints, Sanctum supplies the session behind them.

## Decision

Sanctum SPA mode with a session cookie, with Laravel Breeze in API mode
supplying the authentication endpoints on top of it.

The deciding factor is the threat model, not convenience. Token storage in
a browser is a known-hard problem whose safe solutions converge on
"put it in an `HttpOnly` cookie" — at which point the cookie session is
simply the direct route. The cost is configuration, which is one-time and
verified by tests; the cost of the alternative is custom security-sensitive
code, which is permanent.

The mobile-client argument for tokens is speculative. `.ai/architecture.md`
forbids building for requirements that do not exist, and Sanctum can issue
tokens alongside the cookie session later without discarding this work.

## Consequences

- **Easier:** no credential storage decisions on the frontend; logout and
  session expiry are handled server-side; XSS cannot read the session; the
  register, login, logout, password-reset, and email-verification endpoints
  come from Breeze rather than being written by hand — and hand-written
  authentication is a reliable way to introduce a security bug.
- **Harder:** three environments (local, CI, production) each need correct
  stateful-domain and CORS configuration, and misconfiguration surfaces as
  a confusing `419` or `401` rather than a clear error.
- **Constrains deployment:** the API and SPA must share a registrable
  domain. This is what dictates the hosting layout in
  [ADR-004](ADR-004-deployment-topology.md), including the cookie-scope
  trade-off documented there.
- **Requires server-side session storage:** unlike stateless tokens, a
  cookie session keeps state on the server. Sessions are stored in Redis
  (see [Tech Stack](../TECH-STACK.md)) rather than files, so they survive
  container replacement and stay consistent across PHP-FPM workers.
- **Committed to:** CSRF-token handling in the API client module; feature
  tests asserting that unauthenticated requests to protected endpoints
  return `401` and that cross-user access returns `403`.
