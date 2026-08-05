# E2E suite

Playwright, covering the four journeys in [Testing](../docs/TESTING.md#end-to-end-tests).
Deliberately thin — see that doc for why these four and not more.

## Running against the real stack (what CI does)

This is the one that actually matters: builds the same Dockerfiles
production uses and runs against them, not `npm run dev`/`php artisan serve`.
See [`../docker-compose.e2e.yml`](../docker-compose.e2e.yml) for why.

```bash
docker compose -f ../docker-compose.e2e.yml up -d --build
docker compose -f ../docker-compose.e2e.yml exec backend php artisan migrate --force
docker compose -f ../docker-compose.e2e.yml exec backend php artisan db:seed --force

npm install
npx playwright install --with-deps chromium
BASE_URL=http://localhost:4173 npx playwright test

docker compose -f ../docker-compose.e2e.yml down -v
```

## Running against local dev servers (faster iteration)

Useful while writing a new test, but not a substitute for the above before
trusting a result — the dev servers don't set the same security headers
(no CSP) and skip the FastCGI/nginx hop entirely, so a test can pass here
and still fail against the real image (or, once, the other way around: see
the note below).

```bash
docker compose -f ../docker-compose.yml up -d postgres redis
# in backend/: php artisan serve --port=8000
# in frontend/: npm run dev

BASE_URL=http://localhost:5173 npx playwright test
```

## A rate limit worth knowing about

`register` is limited to 5/hour per IP (`docs/ARCHITECTURE.md`), and every
E2E request comes from the same IP. Three of the four journeys each
register one throwaway user. Iterating locally against a long-lived stack
(not tearing it down between runs) will eventually 429 — that's the rate
limiter working as designed, not a broken test. Restart the stack (or
`redis-cli FLUSHALL` inside its redis container) to reset it. This is why
`playwright.config.ts` sets `retries: 0` even in CI: retrying a genuine
failure would only burn further into the same budget.

## Two real bugs this suite caught before it ever ran in CI

Both were found by actually building and running the Docker stack locally,
not by reading the compose file and trusting it —
`.ai/code-review.md`'s warning about unverified infra-as-code applies to
E2E scaffolding as much as to deploy config.

- **`DatabaseSeeder` called `User::factory()->create()`** (Laravel scaffold
  leftover, never used by anything) — `UserFactory` uses `fake()`, which
  needs `fakerphp/faker`, a dev-only dependency the production Dockerfile's
  `composer install --no-dev` correctly excludes. The one-time production
  seed commands documented in `docs/DEPLOYMENT.md` would have failed the
  first time anyone actually ran them. Removed the dead line.
- **The frontend image's CSP hardcoded the production API origin**
  (`connect-src ... https://fitnesslab-api.zaitis.dev`), silently blocking
  every request when this exact image was built against a different origin
  for E2E. Templated via nginx's own `/etc/nginx/templates/*.template`
  convention (`API_ORIGIN` env var, defaulting to production in the
  Dockerfile) — see `frontend/docker/nginx.conf.template`.
