# Visual redesign reference (M14)

`FitnessLab.dc.html` is a Claude Design export: a full visual redesign of
the landing page and the dashboard shell (all four tabs), built as its own
prototype outside this codebase. `landing-preview.webp` is its rendered
landing page.

Not yet adopted — deliberately deferred until after M12/M13 (admin CRUD)
so the two pieces of work don't land on top of each other. See M14 in
[Roadmap](../ROADMAP.md).

Key tokens, for whoever picks this up:

- Palette: warm cream background (`oklch(96.5% 0.02 85)`), a customizable
  green accent (`oklch(52% 0.13 150)` default), near-black text
  (`oklch(20% 0.02 150)`) — all oklch, which the frontend's shadcn theme
  (`frontend/src/index.css`) already uses, so this is a CSS-variable swap
  rather than a new theming system.
- Type: Sora (600-800 weight) for headings, Work Sans for body — replacing
  the current Geist Variable.
- Organic "blob" shapes (irregular `border-radius`) with a slow float
  animation, used for icons and hero decoration.
- Rounded cards (white, subtle border, ~16px radius), pill-shaped buttons,
  a donut-chart BMI ring in the hero mockup.
- All copy in the export is hardcoded Polish — implementing this means
  routing it through the existing `react-i18next` keys, not copying the
  literal strings.
