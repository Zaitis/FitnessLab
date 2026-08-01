# ADR-005: Bilingual interface, with translations embedded in plan snapshots

Date: 2026-08-01
Status: Accepted

## Context

FitnessLab has two distinct audiences: Polish visitors, and international
reviewers reading the repository. Documentation is written in English for the
latter, but the running application has to serve both — a Polish-only
interface makes the live demo unusable for a reviewer who cannot read it,
while an English-only interface abandons the local audience.

The application is therefore bilingual, Polish and English, with a language
switcher.

That decision propagates further than UI strings. Three things carry
user-visible text: interface copy, framework validation messages, and the
seeded exercise and meal catalogues that plan generators draw from. The
catalogues are the awkward case, because their text ends up inside the
immutable plan snapshots mandated by
[ADR-003](ADR-003-plan-snapshots.md).

## Options considered

### Translating the catalogue

1. **Parallel columns** — `name_en`, `name_pl`, `description_en`,
   `description_pl`.
   *Pros:* trivially simple, plainly indexable.
   *Cons:* every new locale is a migration across every translatable table,
   and every query has to select the right column by locale.

2. **A separate translations table** keyed by model, field, and locale.
   *Pros:* fully normalised; locales added as rows.
   *Cons:* a join for every read of every translatable field, and the
   catalogue is read on every plan generation.

3. **JSONB translation columns** — `name` holds
   `{"en": "Bench press", "pl": "Wyciskanie sztangi"}`, accessed through
   `spatie/laravel-translatable`.
   *Pros:* one column per field regardless of locale count; a new locale is
   a data change, not a migration; the package handles fallback.
   *Cons:* text lives inside JSON rather than a plain column, so full-text
   search across it is more awkward.

### Locale inside plan snapshots

1. **Store the snapshot in the generation locale only.**
   *Cons:* a plan generated in Polish stays Polish forever. Switching the
   interface to English leaves the dashboard half-translated, and the PDF
   export cannot honour the reader's language.

2. **Store catalogue IDs and resolve text at read time.**
   *Cons:* directly contradicts ADR-003. A renamed or corrected catalogue row
   would retroactively alter a plan the user was already given, which is the
   exact failure ADR-003 exists to prevent.

3. **Embed all supported locales in the snapshot at generation time.**
   *Cons:* the snapshot roughly doubles in size, and a locale added later is
   absent from existing snapshots.

## Decision

JSONB translation columns via `spatie/laravel-translatable` for the
catalogues, and **plan snapshots embed every supported locale at generation
time**.

The snapshot decision is the load-bearing one. It is the only option that
keeps both guarantees the project has already committed to: a plan is
immutable once given (ADR-003), and the interface is fully bilingual. The
cost is size, and the quantity is small — a plan holds on the order of tens
of items, each with a short name and description, so a second locale adds
kilobytes to a row that is read whole anyway.

Interface strings use `react-i18next` with JSON resource files per locale.
Validation and framework messages use Laravel's own `lang` directory, with
Polish translations sourced from `laravel-lang` rather than hand-written.

**Locale negotiation:** the SPA sends `Accept-Language` on every request. An
authenticated user's chosen locale is persisted on the user record and takes
precedence; for anonymous visitors the switcher's choice is held in
`localStorage`. English is the fallback whenever a key or translation is
missing.

## Consequences

- **Easier:** language switching is instant and complete, including for plans
  generated long before the switch; PDF export renders in the reader's
  language; adding a third locale needs no migration on the catalogue.
- **Harder:** every user-visible string now has two sources of truth to keep
  in step, and a missing Polish translation silently falls back to English
  rather than failing loudly — so a test asserts that both locale files
  expose identical key sets.
- **Snapshots are locale-set-versioned:** a locale added after a plan was
  generated will not appear in that plan, which falls back to English for the
  missing language. This is accepted rather than solved; backfilling old
  snapshots would mean re-deriving them from a catalogue that has since
  changed, reintroducing the mutability ADR-003 rejects.
- **Committed to:** a `supported_locales` config read by both the generator
  and the frontend, so the two cannot disagree about which languages exist;
  and key-parity tests across locale files in both applications.
- **Legal copy is translated, not localised.** The English terms and
  disclaimer are the source; the Polish version is a translation of the same
  text. Neither is claimed to be legally reviewed
  ([Terms & Disclaimer](../legal/TERMS-AND-DISCLAIMER.md)).
