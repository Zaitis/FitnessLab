# Architecture Decision Records

Decisions that are expensive to reverse, or that deviate from the defaults
in `.ai/architecture.md`, are recorded here. Each ADR states the context, the
options weighed, the decision, and what it costs — including what the choice
gives up.

Cheaper, reversible choices are documented in
[Tech Stack](../TECH-STACK.md) instead.

| ADR | Title | Status |
|---|---|---|
| [001](ADR-001-api-spa-split.md) | Separate Laravel API and React SPA in one monorepo | Accepted |
| [002](ADR-002-spa-cookie-auth.md) | Cookie-based SPA session via Sanctum, not API tokens | Accepted |
| [003](ADR-003-plan-snapshots.md) | Store generated plans as JSONB snapshots | Accepted |
| [004](ADR-004-deployment-topology.md) | Single-server deployment across two subdomains | Accepted |
| [005](ADR-005-internationalisation.md) | Bilingual interface, with translations embedded in plan snapshots | Accepted |

New records follow `.ai/templates/` conventions: sequential numbering, a
status of Proposed / Accepted / Superseded, and a superseding link rather
than an edit when a decision is reversed.
