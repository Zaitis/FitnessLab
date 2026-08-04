# Deployment

How FitnessLab gets from a merge to `master` to a running site at
`fitnesslab.zaitis.dev`. Read [ADR-004](adr/ADR-004-deployment-topology.md)
first — the settings below exist to satisfy the decisions made there, not
the other way around.

## Shape of it

- **CI** (`.github/workflows/ci.yml`) builds and tests both apps on every
  push and pull request.
- On a successful push to `master`, the same workflow **builds and pushes**
  `ghcr.io/zaitis/fitnesslab-backend` and `ghcr.io/zaitis/fitnesslab-frontend`
  images to GHCR, then **SSHes into the VPS** and runs
  `docker compose pull && up -d`.
- The VPS never builds anything. It only pulls pre-built images and restarts
  containers.
- The host's own nginx (installed directly on the VPS, not containerized)
  terminates TLS for both public hostnames and reverse-proxies to the
  `frontend` and `backend` containers, which bind only to `127.0.0.1` and are
  never reachable directly from the internet.

Everything below the "One-time VPS setup" heading only needs to happen once.
After that, every merge to `master` deploys itself.

## One-time VPS setup

Run once, by hand, on the server. Nothing here is automated — bootstrapping a
brand-new server is inherently manual once.

### 1. Prerequisites

```bash
sudo apt update
sudo apt install -y docker.io docker-compose-plugin nginx certbot python3-certbot-nginx git
sudo systemctl enable --now docker
```

### 2. Clone the repo

Owned by whichever user `SSH_USER` (step 7) will be — **not** necessarily
whoever you're logged in as right now while running this. A mismatch here
is exactly what it looks like: `git pull` during deploy fails later with
`Permission denied` on `.git/FETCH_HEAD`, because the deploy step
authenticates as `SSH_USER` but the checkout belongs to someone else.

```bash
sudo mkdir -p /var/www/fitnesslab
sudo chown <ssh-user>:<ssh-user> /var/www/fitnesslab
git clone https://github.com/Zaitis/FitnessLab.git /var/www/fitnesslab
cd /var/www/fitnesslab
```

This checkout is what `git pull` refreshes on every deploy — it's how
`docker-compose.prod.yml` and the nginx configs stay in sync with the repo
without a separate copy step.

### 3. Create the env files (never committed)

```bash
cp .env.production.example .env
cp backend/.env.production.example backend/.env
```

Edit `.env` (repo root) and set `POSTGRES_PASSWORD` to a generated secret.

Edit `backend/.env` and fill in:

- `DB_PASSWORD` — **the same value** as `POSTGRES_PASSWORD` in `.env` above.
  Two files, one password — see the comment in `docker-compose.prod.yml` for
  why they're not merged.
- `APP_KEY` — generate one:
  `docker run --rm ghcr.io/zaitis/fitnesslab-backend:latest php artisan key:generate --show`
  (paste the `base64:...` output back into `APP_KEY=`).
- `MAIL_PASSWORD` — the same Zoho SMTP password already used locally (M0).

Every other value in `backend/.env.production.example` is already correct
for production and shouldn't need changing — in particular, don't touch
`SESSION_DOMAIN`, `SESSION_COOKIE`, or `SANCTUM_STATEFUL_DOMAINS` without
re-reading ADR-004 first.

### 4. Make the GHCR images pullable

The deploy step runs `docker compose pull` with no `docker login` — the
simplest option is making both GHCR packages public once the first images
exist (GitHub → your profile → Packages → package → Package settings →
Change visibility). The alternative, if you'd rather keep them private, is
running `docker login ghcr.io` once on the VPS with a PAT that has
`read:packages` scope.

### 5. nginx and TLS

```bash
sudo cp deploy/nginx/fitnesslab.zaitis.dev.conf /etc/nginx/sites-available/
sudo cp deploy/nginx/fitnesslab-api.zaitis.dev.conf /etc/nginx/sites-available/
sudo ln -s /etc/nginx/sites-available/fitnesslab.zaitis.dev.conf /etc/nginx/sites-enabled/
sudo ln -s /etc/nginx/sites-available/fitnesslab-api.zaitis.dev.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

Both DNS records (`fitnesslab.zaitis.dev`, `fitnesslab-api.zaitis.dev`) need
to already point at this server before the next step.

```bash
sudo certbot --nginx -d fitnesslab.zaitis.dev
sudo certbot --nginx -d fitnesslab-api.zaitis.dev
```

Certbot rewrites the two config files in place to add the HTTPS server
blocks and redirect HTTP → HTTPS. Verify renewal actually works rather than
assuming it — the roadmap calls this out explicitly:

```bash
sudo certbot renew --dry-run
```

#### Security headers

These are split across two layers deliberately, and the split is easy to get
wrong when editing either file.

**HSTS is set by the host configs** in `deploy/nginx/`, on *both* hostnames.
Certbot's `--nginx` installer adds the TLS block and the HTTP→HTTPS redirect
but never adds HSTS, so a config that looks finished after certbot still
isn't. Both hosts need it because the session cookie is scoped to
`.zaitis.dev` ([ADR-004](adr/ADR-004-deployment-topology.md)) — a protocol
downgrade on either subdomain exposes the same cookie. `includeSubDomains`
is deliberately omitted: it would commit every unrelated project under
`zaitis.dev` to HTTPS-only, which is not this project's call to make.

**CSP and the rest are set by the frontend container** in
`frontend/docker/nginx.conf`, because that is the layer that knows what the
SPA actually loads. `connect-src` names the API origin explicitly, since the
SPA and API are different subdomains.

One nginx trap worth stating plainly: `add_header` does **not** merge with
enclosing blocks. Any `location` that sets a single header silently drops
every header inherited from the `server` block. That is why
`location /assets/` repeats `X-Content-Type-Options` — without the repeat,
the hashed JS and CSS bundles would be the only responses served without it.

After a config change, verify against the running site rather than the file:

```bash
curl -sI https://fitnesslab.zaitis.dev | grep -iE 'strict-transport|content-security|x-content-type|x-frame'
curl -sI https://fitnesslab.zaitis.dev/assets/ | grep -i x-content-type
curl -sI https://fitnesslab-api.zaitis.dev/api/health | grep -iE 'strict-transport|x-content-type'
```

### 6. First bring-up

```bash
docker compose -f docker-compose.prod.yml pull
docker compose -f docker-compose.prod.yml run --rm backend php artisan migrate --force
docker compose -f docker-compose.prod.yml up -d
```

Seed the exercise catalogue once the `workout_plans`/`exercises` tables exist
(from M6 onward), and the meal catalogue once `nutrition_plans`/
`meal_templates` exist (from M7 onward):

```bash
docker compose -f docker-compose.prod.yml run --rm backend php artisan db:seed --class=ExerciseSeeder --force
docker compose -f docker-compose.prod.yml run --rm backend php artisan db:seed --class=MealTemplateSeeder --force
```

Deliberately one-time manual steps, not part of the automated deploy
script: neither seeder is idempotent (`Exercise::create()` /
`MealTemplate::create()`, not `updateOrCreate()`), so running either twice
duplicates every row. Re-run one only after clearing its table, or once the
seeder is rewritten to upsert — not needed yet for catalogues that don't
change per deploy.

Once both catalogues exist (from M9 onward), seed the demo account — it
draws from both, so it must run after them, not before:

```bash
docker compose -f docker-compose.prod.yml run --rm backend php artisan db:seed --class=DemoAccountSeeder --force
```

Unlike the two above, this one is a wrapper around `ResetDemoAccountAction`
and is safe to re-run any number of times — it's the same action the
nightly `demo:reset` schedule calls, so running it manually just triggers
an extra reset a beat early. Only needed once; the schedule takes over from
there.

### 7. GitHub Actions secrets

Repo → Settings → Secrets and variables → Actions:

| Secret | Value |
|---|---|
| `SSH_HOST` | the VPS's address |
| `SSH_USER` | the deploy user (the one who owns `/var/www/fitnesslab`) |
| `SSH_PRIVATE_KEY` | a private key whose public half is in that user's `~/.ssh/authorized_keys` on the VPS |

Generate a dedicated deploy key rather than reusing a personal one:

```bash
ssh-keygen -t ed25519 -C "fitnesslab-deploy" -f fitnesslab_deploy_key -N ""
```

Add `fitnesslab_deploy_key.pub` to the VPS user's `authorized_keys`, and the
contents of `fitnesslab_deploy_key` (the private half) as the `SSH_PRIVATE_KEY`
secret. Delete both local files once they're in place.

`GITHUB_TOKEN` (used to push to GHCR) is provided automatically — nothing to
add for that one.

## Verifying a deploy

```bash
docker compose -f docker-compose.prod.yml ps        # everything should be "Up"
docker compose -f docker-compose.prod.yml logs backend --tail 50
curl -s https://fitnesslab-api.zaitis.dev/api/health
```

## Rolling back

Pull and restart with an explicit older tag rather than `latest`:

```bash
docker compose -f docker-compose.prod.yml pull backend frontend  # skip if the tag is already local
docker run -d --name fitnesslab-backend-rollback ghcr.io/zaitis/fitnesslab-backend:<previous-sha>
```

In practice, re-deploying the previous commit (revert + push) is simpler and
goes through the same tested pipeline rather than a manual container swap.
