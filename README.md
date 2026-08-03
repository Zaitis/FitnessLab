# FitnessLab

[![CI](https://github.com/Zaitis/FitnessLab/actions/workflows/ci.yml/badge.svg)](https://github.com/Zaitis/FitnessLab/actions/workflows/ci.yml)

**Take care of your health and your shape** — a full-stack demo application
built with Laravel and React.

🔗 **Live:** [fitnesslab.zaitis.dev](https://fitnesslab.zaitis.dev)

> ### ⚠️ Demo project — not medical advice
>
> FitnessLab is a **portfolio project**. It is not built or operated by a
> dietitian, nutritionist, or certified personal trainer. BMI results and
> the generated training and meal plans are produced by simple,
> hard-coded rules and exist **only to demonstrate software engineering
> practices** — they are **not medical, nutritional, or fitness advice**.
> Consult a qualified professional before changing your diet or training.
> The application is free, and provided "as is". See [Terms & Disclaimer](docs/legal/TERMS-AND-DISCLAIMER.md).

---

## What it does

The interface is bilingual — Polish and English, switchable at any time,
including for plans generated before the switch
([ADR-005](docs/adr/ADR-005-internationalisation.md)).

**Public landing page**
- Hero section with the project's purpose and a visible demo disclaimer.
- **Try the demo account** — one click into a populated dashboard, no
  registration required.
- **Anonymous BMI calculator** — no account required. Enter weight and
  height; get a BMI value and weight category with full validation.
- After a result is shown, a call to action invites the visitor to create a
  free account to save the measurement and track progress. Registration is
  fully independent — an account can be created without using the calculator.

**Dashboard (authenticated)**
- **Training plan generator** — pick a goal (fat loss / muscle gain /
  maintenance), experience level, and available days per week; a plan is
  assembled from a curated exercise database using deterministic rules.
- **Meal plan generator** — the same approach for a sample calorie and meal
  split, informed by the goal and the latest BMI measurement.
- **Progress tracking** — weight and BMI history over time with a trend chart.
- **Adherence calendar** — a monthly calendar where individual meals and
  exercises are checked off day by day.
- **PDF export** — any generated plan downloads as a PDF carrying a diagonal
  watermark with the demo disclaimer.

**Everywhere**
- A disclaimer layer surfaces the same notice in the page header, on every
  generated plan, in the footer, and as the PDF watermark — from a single
  source of truth.
- Terms of use stating the application is a free demo.

## Engineering focus

The point of this codebase is *how* it is built, not the accuracy of its
fitness recommendations:

- **Testable domain logic.** Plan generation lives in framework-agnostic
  classes driven by the Strategy pattern — no business rules in controllers.
- **A real test pyramid.** Pest unit, feature, and architecture tests on the
  backend; Vitest + React Testing Library with MSW on the frontend;
  Playwright for the critical end-to-end paths. See [Testing](docs/TESTING.md).
- **Enforced boundaries.** Architecture tests fail the build when a
  controller touches the database directly or a domain module reaches into
  infrastructure.
- **Documented decisions.** Non-obvious or expensive-to-reverse choices are
  recorded as ADRs rather than left implicit.

## Tech stack

| Layer | Choice |
|---|---|
| Backend | Laravel 12, PHP 8.4, PostgreSQL 16 |
| Sessions, cache, queue | Redis |
| API auth | Laravel Breeze (API mode) on Sanctum, cookie-based SPA session |
| Frontend | React 19 + TypeScript (strict), Vite |
| Server state | TanStack Query |
| Forms | react-hook-form + zod |
| UI | Tailwind CSS + shadcn/ui |
| i18n | react-i18next, spatie/laravel-translatable |
| Charts / calendar | Recharts, react-day-picker |
| PDF | dompdf |
| Backend tests | Pest (unit, feature, architecture) |
| Frontend tests | Vitest, React Testing Library, MSW |
| E2E | Playwright |
| Quality gates | Larastan, Laravel Pint, oxlint, Prettier, `tsc --noEmit` |
| Infrastructure | Docker Compose, GitHub Actions CI, nginx + Let's Encrypt on a single VPS |

The demo runs on one server across two hosts —
`fitnesslab.zaitis.dev` for the SPA and `fitnesslab-api.zaitis.dev` for the
API. Both sit under one registrable domain, which the cookie-based session
requires ([ADR-004](docs/adr/ADR-004-deployment-topology.md)).

Full rationale for each choice, including the ones deliberately rejected,
is in [Tech Stack](docs/TECH-STACK.md).

## Repository layout

```
FitnessLab/
├── backend/          Laravel API
├── frontend/         React single-page application
├── e2e/              Playwright end-to-end suite
├── docs/             Architecture, patterns, testing, ADRs, legal copy
├── .ai/              Engineering standards applied across the repo
└── docker-compose.yml
```

## Getting started

_Populated during M0 — see [Roadmap](docs/ROADMAP.md)._

```bash
docker compose up -d
```

## Documentation

| Document | Contents |
|---|---|
| [Architecture](docs/ARCHITECTURE.md) | Module boundaries, request flow, data model, auth |
| [Design Patterns](docs/DESIGN-PATTERNS.md) | Patterns applied in the domain layer, and which were rejected |
| [Testing](docs/TESTING.md) | Test pyramid, what is covered at each level, CI gates |
| [Tech Stack](docs/TECH-STACK.md) | Every dependency and the reasoning behind it |
| [Roadmap](docs/ROADMAP.md) | Milestones and definitions of done |
| [ADRs](docs/adr/) | Architecture decision records |
| [Terms & Disclaimer](docs/legal/TERMS-AND-DISCLAIMER.md) | Source copy for the in-app legal pages |

## Status

🚧 In development — current milestone is tracked in the [Roadmap](docs/ROADMAP.md).

**Live now (through M4):** the BMI calculator, account registration and
login, and measurement carry-over from an anonymous session into a new
account. Everything else in "What it does" above — training/meal plans,
progress tracking, adherence calendar, PDF export, the demo account — is
still ahead, arriving milestone by milestone. A live URL with three features
beats a local project with ten.

## License

MIT — see [LICENSE](LICENSE).
