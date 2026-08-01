# Design Patterns

Patterns applied in FitnessLab's domain layer, the problem each one solves
here, and — equally important — the patterns deliberately **not** used.
A pattern earns its place by removing a concrete problem in this codebase,
not by appearing in a catalogue.

Guiding constraints come from `.ai/coding-principles.md`: SOLID, DRY, KISS,
YAGNI, in that order of application, with simplicity outranking cleverness.

---

## 1. Strategy — plan generation

**Problem.** Training and meal plans are assembled differently depending on
the goal (fat loss, muscle gain, maintenance) and experience level. Expressed
directly, this becomes a single generator class holding a growing `match`
over every combination — one class with several unrelated reasons to change,
awkward to test because every case runs through the same entry point, and
requiring modification of working code to add a goal.

**Solution.** A strategy interface with one implementation per goal, and a
resolver that picks the applicable one.

```php
interface WorkoutPlanStrategy
{
    public function supports(WorkoutPlanCriteria $criteria): bool;

    public function generate(WorkoutPlanCriteria $criteria): GeneratedWorkoutPlan;
}
```

`GenerateWorkoutPlanAction` receives the strategies as a collection — bound
in a service provider — finds the first whose `supports()` returns true, and
delegates. Adding a goal means adding a class and a binding; no existing
strategy is touched. That is Open/Closed with an actual payoff rather than a
recital.

**Applied twice, shared once.** `Nutrition` mirrors the structure with its
own `NutritionPlanStrategy` interface and its own implementations. The two
hierarchies are *not* merged behind a shared generic interface despite
having the same shape. Training and nutrition are different domains that
coincidentally rhyme; unifying them would couple two things that change for
unrelated reasons and force both to move together.

**Testing leverage.** Each strategy is a plain PHP object living in
`App\Domain`, which by architectural rule contains no `Illuminate\*` at all.
It reaches the exercise catalogue through the `ExerciseCatalogue` contract
(see §7), so unit tests inject an in-memory implementation and the generation
rules run with no HTTP, no database, and no container. This is the single
biggest reason the pattern is here.

---

## 2. Action classes — one use case per class

Every application operation is a class with one public method:

`CalculateBmiAction` · `RecordMeasurementAction` · `GenerateWorkoutPlanAction`
· `GenerateNutritionPlanAction` · `ToggleAdherenceAction` ·
`ExportPlanToPdfAction`

Controllers reduce to validate → call → serialise, per `.ai/laravel.md`.

**Why not a service class per module.** A `WorkoutService` starts with three
methods and accumulates loosely related ones, because there is never a
natural point at which adding one more method feels wrong. Its constructor
dependencies become the union of everything any method needs, so every test
of any method drags in all of them. One class per use case has an obvious
boundary and a constructor that lists exactly what that operation requires.

**Why not fat models.** Plan generation reads the exercise catalogue, applies
goal-specific rules, and produces a document. That is not the responsibility
of an Eloquent model representing a persisted row.

---

## 3. Value objects and typed criteria

**Problem.** Passing `float $bmi` through the system loses everything that
makes BMI meaningful: its category thresholds, its valid range, and its
formatting. That logic then reappears — slightly differently each time — in
controllers, resources, and the frontend. Classic primitive obsession.

**Solution.**

```php
final readonly class Bmi
{
    private function __construct(
        public float $value,
        public BmiCategory $category,
    ) {}

    public static function fromMeasurements(Weight $weight, Height $height): self { /* ... */ }
}
```

`BmiCategory` is a backed enum owning its thresholds, so category boundaries
exist in exactly one place and are covered by boundary tests. `Weight` and
`Height` are value objects that reject physically implausible input at
construction, which means anything holding one is guaranteed valid — the
validity check happens once, not at every use site.

The same applies on the way in: generators accept `WorkoutPlanCriteria`
rather than a loose array, so the contract is typed, static analysis can
verify it, and a missing field is a compile-time-ish error rather than a
runtime `undefined array key`.

---

## 4. Repository pattern — rejected for persistence, one exception for reads

The reflex on a portfolio project is to add `WorkoutPlanRepositoryInterface`
with an `EloquentWorkoutPlanRepository` behind it, on the theory that this
demonstrates layering. Applied wholesale, it would demonstrate the opposite.

Eloquent is already an abstraction over SQL. Wrapping *writes* achieves
something only if there is a second store to swap in — and there is one
store. What it costs is real: an interface, an implementation, and a binding
per aggregate, plus a leaky abstraction the moment anything needs eager
loading or pagination, both of which are Eloquent concepts a storage-agnostic
interface cannot express without becoming a worse Eloquent.

So plans, measurements, and adherence entries are persisted through Eloquent
directly inside Actions, with nothing in between.

**The one exception is the catalogue read path**, and it is worth being
precise about why it is not the same thing. Plan generation is the logic this
project most wants under fast, exhaustive unit tests — a matrix of goal ×
level × days, run hundreds of times. Those strategies need exercise data.
Without an interface, every one of those tests needs a database, which drags
the most-run tests in the suite down to the speed of the slowest layer.

`ExerciseCatalogue` and `MealTemplateCatalogue` are therefore declared in
`App\Domain\<Module>\Contracts` and implemented in `App\Infrastructure`. The
distinction from a general repository:

| Rejected repository | Accepted catalogue contract |
|---|---|
| One per aggregate, across the whole app | Two, only for seeded reference data |
| Full CRUD surface | Read-only, one query method |
| Justified by a hypothetical second data store | Justified by a test-speed need that exists today |
| Wraps user data that Actions own anyway | Wraps reference data the domain merely consumes |

`.ai/architecture.md` prohibits speculative flexibility, not all abstraction.
This one is paid for by a concrete, present benefit; a write-side repository
would not be.

---

## 5. Single source of truth for disclaimer copy

Not a Gang-of-Four pattern, but the highest-consequence structural decision
in the project. The demo disclaimer appears in four places built on
different technologies — React header, React footer, JSON plan payloads, and
the PDF watermark. Four copies of a legally meaningful string drift, and the
drift is invisible until someone compares them.

One definition in `config/disclaimer.php`, wrapped by a `DisclaimerText`
value object, feeds all four:

- `GET /api/disclaimer` — public, cached; the SPA reads it once for chrome copy.
- `WorkoutPlanResource` / `NutritionPlanResource` — attach it to every payload.
- `PdfExporterInterface` implementations — render it as the watermark.

Changing the wording is a one-file edit. A test asserts the string appears
in a plan response and in generated PDF output, so a future refactor that
accidentally drops it fails the build.

---

## 6. Adapter for PDF generation

**Problem.** `ExportPlanToPdfAction` should express *what* is exported and
under what authorization — not how a specific library rasterises HTML.
Coupling domain logic to dompdf's API makes the library unswappable and
drags a heavy dependency into every test touching export.

**Solution.** A narrow interface owned by the domain, implemented by
infrastructure:

```php
interface PdfExporterInterface
{
    public function export(PlanExportData $data): PdfDocument;
}
```

`DompdfPlanExporter` is the single implementation, bound in a provider.
Watermark rendering belongs to the implementation, since it is a rendering
concern; the Action supplies the plan data and the disclaimer text and knows
nothing about diagonal text or opacity.

**Testing leverage.** Action tests substitute a fake exporter and assert on
the `PlanExportData` handed to it — fast, deterministic, no PDF parsing. One
narrower integration test exercises the real implementation and asserts the
watermark text is present in the output.

---

## 7. Rules as data, not as code

The exercise and meal-template catalogues live in seeded database tables, not
in `match` expressions or hard-coded arrays inside generators.

Strategies do not query those tables directly. They express a filter and hand
it to the `ExerciseCatalogue` contract from §4:

```php
$candidates = $this->catalogue->matching(
    new ExerciseFilter(muscleGroups: $split->groupsFor($day), maxDifficulty: $level)
);
```

Two things fall out of this. Adding an exercise becomes a data change — a
seeder — rather than an edit to logic that is under test. And the strategy
stays short enough to read in one screen, because it expresses *selection
rules* rather than enumerating content.

Catalogue rows carry their user-visible text as JSONB translation columns, so
what the strategy receives already contains every supported locale — which is
what lets the generated snapshot embed all of them
([ADR-005](adr/ADR-005-internationalisation.md)).

---

## 8. Architecture tests as an enforcement mechanism

Every boundary above is a convention, and conventions decay. Pest's
architecture testing turns them into build failures:

- Controllers may not reference `DB`, `Illuminate\Support\Facades\DB`, or
  Eloquent query builders.
- Classes in `App\Domain\*` may not depend on `Illuminate\*` at all — the
  entire framework, which is the claim §1 rests on.
- `App\Domain` may not depend on `App\Infrastructure`; the dependency runs
  the other way.
- `Workouts` may not reference `Documents` or `Auth` internals.
- Action classes are `final` and expose exactly one public method.
- Value objects are `readonly`.

This is what makes the patterns above load-bearing rather than aspirational.
Details in [Testing](TESTING.md).

---

## Summary

| Concern | Approach | Rationale |
|---|---|---|
| Goal-specific plan rules | Strategy, one per goal | Open/Closed with a real payoff; framework-free unit tests |
| Application operations | Action per use case | Bounded responsibility; minimal constructor dependencies |
| BMI, weights, criteria | Value objects + enums | Ends primitive obsession; validity guaranteed at construction |
| Persisting user data | Eloquent directly — **no repository** | Eloquent is already the abstraction; no second store exists |
| Reading the catalogue | One read-only contract per catalogue | Keeps the most-run unit tests off the database |
| Disclaimer copy | One config source, four consumers | Legally meaningful string that must not drift |
| PDF generation | Domain-owned interface, infrastructure adapter | Library swappable; Action tests need no real PDF |
| Exercise / meal catalogue | Seeded data, not code | Content changes without touching tested logic |
| Boundary enforcement | Pest architecture tests | Conventions that CI actually verifies |
