# ADR-003: Store generated plans as JSONB snapshots

Date: 2026-07-31
Status: Accepted

## Context

Training and meal plans are assembled by rule-based generators from two
seeded catalogues: `exercises` and `meal_templates`. Those catalogues are
expected to grow and be corrected over the life of the project.

Each generated plan then has a long life of its own: it is displayed on the
dashboard, exported to PDF, and — through the adherence calendar — checked
off day by day for weeks. This raises the question of what is actually
persisted when a user generates a plan.

## Options considered

1. **Store only the inputs, re-generate on read.** Persist `goal`,
   `experience_level`, `days_per_week` and run the generator whenever the
   plan is displayed.
   *Pros:* smallest schema; a fixed generator bug retroactively improves
   every past plan.
   *Cons:* a plan can silently change under a user who is midway through
   following it, and a PDF exported today may not match the same plan
   exported tomorrow. Worse, adherence entries reference plan items that a
   re-generation can make disappear, leaving orphaned check-offs. Every
   read also pays generation cost.

2. **Fully normalise the generated plan** into `workout_plan_days`,
   `workout_plan_exercises`, and equivalents for nutrition.
   *Pros:* relationally queryable; foreign keys to catalogue rows.
   *Cons:* four to six additional tables and their migrations, factories,
   and models, to represent a structure that is only ever read back whole.
   Foreign keys to the catalogue reintroduce the mutation problem from
   option 1 — a corrected exercise row changes historical plans.

3. **Store the generated plan as a JSONB snapshot** on the plan row,
   alongside the inputs that produced it.
   *Pros:* what the user was shown is what is stored, permanently; export
   and history are trivially consistent; no read-time computation; the
   catalogue stays free to evolve. PostgreSQL JSONB remains indexable and
   queryable if a reporting need appears.
   *Cons:* the snapshot is not relationally joinable to the catalogue;
   aggregate queries across plans are more awkward; the snapshot's shape is
   application-enforced rather than schema-enforced.

## Decision

Option 3 — a JSONB `generated_plan` column, with the generator inputs kept
in dedicated typed columns beside it.

The governing principle is that a generated plan is a *record of what was
given to the user*, not a live view over current reference data. Once the
adherence calendar references individual plan items, immutability stops
being a nicety and becomes a correctness requirement: check-offs must not
be able to point at items that no longer exist.

Keeping the inputs in real columns rather than inside the JSON preserves
the ability to query and index the things actually worth filtering on —
which goal, which experience level, how many plans of each kind.

## Consequences

- **Easier:** history and PDF export are consistent by construction; the
  exercise and meal catalogues can be edited freely without rewriting the
  past; no join-heavy read path for what is always a whole-document read.
- **Harder:** cross-plan analytics need JSONB operators rather than plain
  joins; the snapshot structure has no database-level schema, so it must be
  guarded by a typed data object on write and covered by tests.
- **Committed to:** assigning a stable UUID to every meal and exercise
  inside a snapshot at generation time, so `adherence_entries.plan_item_id`
  has something durable to reference; and versioning the snapshot shape if
  it ever changes incompatibly.
