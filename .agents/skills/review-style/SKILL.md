---
name: review-style
description: Review the eXeLearning style in theme/ against the exports in contents/ — responsive behaviour, WCAG accessibility at a level chosen each run, dead code, CSS/JS errors, typography coverage, and the third-party credits in config.xml. Applies the corrections to theme/config.xml, theme/style.css and theme/style.js. Use when asked to review, audit, check or validate a style, or before downloading/shipping one.
---

# Review a style

Audit the style in `theme/` against the three exports in `contents/`, then fix what is
broken. The output is not a document: it is a corrected `theme/`.

**`AGENTS.md` at the repository root is the authority.** Read it first — this skill tells
you what to check and in what order; `AGENTS.md` tells you what is true about the
workspace. Where they disagree, `AGENTS.md` wins. Section references below (§3, §8…) point
at it.

`references/checks.md` holds the full check catalogue. Load it at Phase 2 and keep it open
through Phase 5 — it is the part you must not improvise.

## The boundary that governs every edit

You are correcting a style, not designing one. **You may not make relevant changes to the
general presentation.**

| Apply directly | Ask first |
| --- | --- |
| Remove code proven dead (Phase 2 rules) | Remove **or rename** anything that *might* be a typo — always |
| Fix invalid CSS, broken selectors, JS that throws | Change a colour enough to read as a different colour |
| Add missing guards, null checks, missing `:focus-visible` | Change spacing, sizes, radii, layout, breakpoints |
| Minimal contrast nudges: same hue, darker/lighter | Change or add a font |
| Missing link states `base.css` would otherwise keep (§10) | Add or remove a visible component |
| Add absent third-party credits to `config.xml` | Alter `name`, `version` or `license` in `config.xml` |
| Delete a declaration a later rule already overrides | Anything a designer would *notice* as a design change |

When in doubt it goes in the right-hand column. A fix that lands in the left column but
happens to be visible — a contrast nudge that changes a heading from grey to noticeably
darker grey — is still worth naming in the report.

**Never edit `contents/`, `contents/*/libs/` or `files/`** (§1). They are the specimen, not
the patient. The one exception is §3: an image the JS or the HTML points at must be copied
into all three `contents/*/theme/` folders to be *previewed* — that is a preview action, not
an edit to the content, and the shipped `.zip` is built from `theme/` alone.

## Phase 0 — Open the file and agree the terms

1. Read `AGENTS.md`. Read `theme/config.xml`.
2. Check the workspace: `theme/style.css` must exist, and `contents/web`, `contents/page`,
   `contents/scorm` must all be present. If `contents/` is missing, stop and tell the user
   to run **Start → Use sample contents** (or upload three exports) in `index.php` — a
   review against no content is worthless.
3. **Ask the accessibility level.** Every run, even if you have asked before:

   > ¿Qué nivel de accesibilidad aplico? **AA (WCAG 2.2)** es el que exige `AGENTS.md` §13
   > y el que recomiendo. **AAA** sube el contraste a 7:1 y suele obligar a repintar la
   > paleta. **A** deja fuera casi todos los mínimos de contraste.
   > También puedo aplicar AA y listar aparte lo que además incumpliría AAA.

   The chosen level is the **failure threshold**. Anything below it is a defect you fix or
   escalate; anything above is a suggestion, never an edit.
4. If the user named a concern ("el menú en móvil", "el modo oscuro"), do the full pass
   anyway but lead the report with that.

## Phase 1 — Inventory: what *this* style is

### ⚠️ First: is this style a delta over another one?

Many styles are a derivation — `educablue-max` over `educablue`, an institutional variant
over a base. If it is, **the review is the delta, not the file**, and the first thing to
establish is where the delta starts. Get the parent from
`public/files/perm/themes/base/<name>/` in the eXeLearning repository (raw URLs, `curl`)
and diff:

```bash
diff <(head -<n> theme/style.css) parent/style.css     # must be empty
```

Two things this buys, and both matter:

- **A divergence inside the shared part is itself a finding.** It means the derived style
  drifted from its parent, and the fix is almost always to restore the parent's line — that
  is what keeps the two files mergeable. Ask before doing anything else with it.
- **Everything the parent already carries is out of scope** — its palette, its contrast, its
  typography, its breakpoints. Do not re-measure what the parent's own review closed; read
  its notes instead and say in the report that you took them as given.

The parent's `config.xml` is also the reference for `version` and for the credits the derived
style must keep. Deriving adds credits, it never removes them, and the derivation itself has
to be stated (§6, Phase 4).

### Then: what this style has

Styles differ, and a check that assumes a feature the style does not have produces noise.
Before checking anything, write down what you are looking at:

- **Files.** `ls theme/`. Several `.css`/`.js` are legal and load alphabetically (§6). Is
  there a `style.js` at all? A `screenshot.png`? `icons/`? `fonts/`? `img/`?
- **Dark mode.** Absent / filter-based / variable-based (§12). This decides a whole branch
  of the review. Grep for `exe-dark-mode`, `prefers-color-scheme`, `invert(`.
- **Logos and trademarks.** Any image in `img/` that is a brand mark. Is it removable
  (§6)? Does `config.xml` say it is not covered by the style's licence?
- **Palette.** `:root` variables, or literal hex spread through the file.
- **Feature flags** in `style.js` — a `dropdownNavigation: true` style switch means both
  states must survive.
- **Breakpoints.** Every `@media` width in the CSS, plus any width compared in the JS
  (`$(window).width() <= 576`). **CSS and JS breakpoints that disagree are a defect** — the
  layout switches at one width and the behaviour at another.

Then the mirror image: **what the exports contain**. Grep the three `contents/*/` trees for
each optional element in §8 and record present / absent.

⚠️ **Grep the whole tree, not `html/`.** The website's home page is `contents/web/index.html`,
a sibling of `html/`, not a file inside it — and SCORM has `index.html` too. A sweep of
`contents/web/html/` alone silently misses whatever only the home page carries, and reporting
an element as absent when it is sitting in `index.html` sends the rest of the review down the
wrong path. `<html>` carries `id="exe-index"` there and its relative paths differ by one
level, so it is a distinct case in its own right. Before writing "absent" for anything, re-run
the grep over `contents/` unfiltered.

The elements to record:

`.package-subtitle` · `.page-counter` · `#teacher-mode-toggler-wrapper` · `#packageLicense`
· `#siteUserFooter` · `.box-toggle` · `.box-icon` · `.box.no-header` · `#siteNav` ·
`.nav-buttons` · `#siteLogos` · `.exe-attachment-link` · `.exe-fx` · `.exe-dl` ·
`.exe-block-*` · `.pre-code` · `.highlighted-code`

**An element absent from these exports is not absent from the world.** Some are injected at
runtime (`#teacher-mode-toggler-wrapper`), some depend on export preferences, some on what
the author wrote. For each absent one, you cannot verify it in the browser — so verify it
by reading: does the CSS handle it missing *and* present? Say plainly in the report which
ones you could not see.

Real project exports routinely exercise less than half the list. `files/fixtures/` holds
sample exports built for the purpose — `box-options.html`, `udl-examples.html`,
`effects-*.html`, `warnings-*.html` cover the box variants, the message blocks and the
interactive effects. **When the loaded content leaves a wide gap, say so and offer to re-run
against the fixtures** (`index.php` → Start → *Use sample contents*). Two passes over two
content sets is the only way to cover the table.

**A feature the style does not have is not a finding.** No dark mode, no `fonts/`, no logo,
no `style.js` are all legitimate. Report an absence only when something in the style refers
to it — a `fonts/` folder with no `@font-face`, a dark-mode rule with no toggle, a sprite
offset for an icon the sprite does not contain.

## Phase 2 — Static review of CSS and JS

Load `references/checks.md` now and work through it. The two rules that outrank the
catalogue:

⚠️ **Start with its §11 (Effects).** Upstream PR exelearning/exelearning#2344 moved the focus
rings and the underline policy of the five effects into `exe_effects.css`, so a style written
before it carries rules that are now redundant, and possibly an `outline: none` that
suppresses a ring the sheet guarantees. It costs one grep to know which case you are in, and
it changes what you do with every FX rule you meet afterwards. The styles bundled with
eXeLearning are already adapted; the ones the user hands you are not.

### The typo rule — never silently delete a reference to something missing

When a selector or a JS call targets an element that does not exist, there are three
explanations and only one of them means "delete it":

1. **Typo.** `#searchBarTogger` vs `#searchBarToggler`, `#example-id` vs `#exampleId`,
   `.box-header` vs `.box-head`. The code is *meant* to work and does not.
2. **Optional element** (§8). The element exists on other projects. The rule is correct and
   must stay.
3. **Genuinely dead.** Left over from a previous version of the style.

Before proposing any deletion, run the checks that separate them:
- Grep the identifier across `contents/*/libs/`, `contents/*/html/` and `theme/*.js` — if
  the application creates it, it is case 2.
- Grep for **near misses**: same name with different case, with/without hyphens, singular
  vs plural, one character different. A near miss that *does* exist is case 1.
- Check whether the style's own JS was supposed to create it and does not.

⚠️ **A misspelling may be the canonical upstream name.** Styles are derived from one
another, and a name that looks like a slip is often the spelling the whole ecosystem uses.
`#searchBarTogger` — one `l` short of "Toggler" — is real: the Default style's `style.js`
creates a button with exactly that id, so it is correct there, while a derived style that
kept the CSS rule and dropped the button has genuinely dead code. **"Correcting" the
spelling would have broken the working style.** Never normalise an identifier because it
reads wrong. If the same name appears in the application's own code or in another style,
it is the name.

### The closed-identifier exception — a misspelling the style owns can be corrected

The warning above is about names with an owner **elsewhere**. The deciding question is not
how the name reads, it is **who creates the element in the style you are reviewing**. When
the style writes the element *and* holds every reference to it, there is no contract with
anyone and the misspelling is just a misspelling.

`#searchBarTogger` is both examples at once, which is why the distinction matters:

- In a style that **kept the CSS rule and dropped the button**, the reference is dead code
  and renaming it fixes nothing — that is the case the ⚠️ above is warning about.
- In a style whose own `style.js` **builds that button**, the id never leaves the style.
  `exe_export.js` does not name it, the exported HTML does not name it, nothing upstream
  reads it. It can be corrected.

The test is a grep, and it has to come back empty everywhere but the style itself:

```bash
grep -rn "<id>" theme/style.js theme/style.css          # every site the style owns
grep -rn "<id>" contents/*/libs/ contents/*/html/        # must be empty
```

Two kinds of echo are **not** a reason to keep the typo. `contents/*/theme/` is a frozen copy
of the export and is rewritten on the next upload. `theme/other-style.js` and
`files/example_css_files/*.css` belong to the application — that is where the slip was
inherited from, it is out of scope, and it is worth **reporting** so the next style does not
inherit it again.

Then rename **every site in one change**: the JS that builds the element, the JS that selects
it, and every CSS rule. Count them before starting — `#searchBarTogger` → `#searchBarToggler`
was seven — because a half-done rename leaves the element silently unstyled, which is worse
than the typo.

⚠️ **No browser check is needed, in Phase 6 or anywhere else.** A closed identifier is
internal to the style by definition: if the grep is clean and the rename is complete, nothing
outside can be looking at the old spelling, and there is no format, state or breakpoint in
which it reappears. Verify by counting occurrences, not by loading a page.

It is still a change the user decides. Report it with the other typo candidates.

**Report cases 1 and 3 and wait for the user's answer. Never delete on your own judgement.**
Present each one as: what the code says, what exists instead, which of the three it looks
like, and what you would do. Case 2 is not a finding at all.

### The dead-code rule — proof, not suspicion

Delete only what you can prove is inert *in every format and every state*:

- A declaration a later rule at equal-or-higher specificity fully overrides — and no
  intermediate state uses it. `background: #005fcc` immediately followed by
  `background: url(…)` in the same block is proof. A rule in a `:hover` block is not.
- A duplicated block, identical property for identical selector, later one wins.
- A `@media` block that can never match (`max-width` below `min-width`).
- A vendor prefix for a browser the compatibility target excludes.

Not proof: "I did not see it used". The DOM you style is not the DOM in the file (§9), the
three formats differ (§7), and teacher mode, presentation mode, dark mode, `?nav=false` and
the minimised box state all produce markup you will not find by grepping one page.

## Phase 3 — Typography

The font has to carry the text, not just look right in a heading.

**First establish which of three cases you are in**, because two of them have no
`@font-face` to inspect and checking for one is noise:

- **The style ships fonts** — `theme/fonts/` plus `@font-face`. The full list below applies.
- **The style declares a family but ships no files** — a web-safe stack. Checks 2, 3 and 6
  apply, read against the stack's real members, not a file.
- **The style declares no font at all** and inherits from `base.css`/Bootstrap. Legitimate,
  and common in minimal styles. There is nothing to audit statically: measure instead. The
  probe reports `bodyFont`, `bodyFontSize` and `bodyLineHeight` as actually computed, and
  check 6 still applies to whatever it resolved to. Do **not** report "no font defined" as a
  defect, and do not add one — that is a change to the general presentation.

1. **Files resolve.** Every `src: url()` in `@font-face` points at a file that exists in
   `theme/fonts/`. Check the path, not just the name.
2. **The four faces — and ⚠️ measure them, do not read them.** Regular, **bold**, *italic*
   and ***bold italic*** must all be reachable: as separate files, or as a variable font
   whose `font-weight` range covers every weight the CSS asks for **plus a separate italic
   file** — the weight axis does not produce italics, and no mainstream variable font has a
   slant axis you can rely on.

   When a face is missing the browser does not fail and does not warn: it **synthesises**
   the missing one by slanting or smearing the face it has. Nothing appears in the console,
   `document.fonts` lists only the faces that exist, and the page looks approximately right.
   That is why this check gets missed. **Treat a missing italic as a defect, not a
   suggestion**, whenever the content uses italics at all — and eXeLearning content
   routinely does: image credits, work titles, emphasis inside author prose.

   One call settles it. Identical advance widths mean the same glyphs are being reused:

   ```js
   function w(style, weight) {
       var s = document.createElement('span');
       s.textContent = 'afgy Hamburgefonstiv';
       s.style.cssText = 'position:absolute;visibility:hidden;font-size:100px;' +
           'font-family:\'<the family>\';font-style:' + style + ';font-weight:' + weight;
       document.body.appendChild(s);
       var out = s.getBoundingClientRect().width; s.remove(); return out;
   }
   // w('normal',400) === w('italic',400)  ->  synthesised, no real italic
   // same for 700; and w(300) === w(400) === w(600) -> no continuous weight axis
   ```

   Run it after `await document.fonts.ready`, and check both 400 and 700 — a style can ship
   a regular italic and still synthesise the bold one.

   Cross-check the range too: grep the CSS for every `font-weight` value used and confirm
   the declared range covers it. `font-weight: 800` against a `300 700` range silently
   clamps.

   **A variable font is the tidy answer** and worth suggesting when faces are missing: two
   files — roman and italic — cover every weight, and split by `unicode-range` they become
   four that load on demand.
3. **Character coverage.** Read the `unicode-range`. Latin-1 (`U+0000-00FF`) covers Spanish,
   French, German. It does **not** cover Latin Extended-A (Polish, Czech, Turkish ı/ğ,
   Welsh), nor Greek, Cyrillic, or the arrows and maths symbols an author may paste.
   eXeLearning exports are translated (§9) and authors write in any language. A gap is not
   fatal — the fallback stack picks it up — but the fallback must be a real stack, not a
   bare `sans-serif`, and mixing two fonts mid-sentence is worth reporting.
4. **Format and weight of the payload.** `.woff2` is the expected format (§6); a `.ttf` is
   typically 2–4× larger for the same face. Report the sizes if they are large.
5. **`font-display`.** Absent means FOIT in some browsers. `swap` is the safe default.
6. **Legibility.** `line-height` under 1.4 on body prose, and any body `font-size` that
   computes below 16px, fail comfortably-readable expectations and interact with WCAG 1.4.4
   (text must survive 200% zoom) and 1.4.12 (text spacing overrides must not clip).
7. **Source code.** Prose typography is not the only typography on the page: `.pre-code` and
   `.highlighted-code` need a monospace family, an untouched `white-space: pre` and line
   numbers that stay in register, and the two of them must come out the same size as each
   other. §9 of the catalogue is the checklist — run it whenever the content has a code
   block, and run it again after any change to font size or scaling.
8. **Licence — and the licence *file*.** Every font file must be credited in `config.xml`
   (Phase 4). Two things beyond the credit line:
   - ⚠️ **Get the licence right, and re-check it when the files change.** The same family
     can ship under different terms in different releases — Open Sans was Apache 2.0 and is
     now SIL OFL 1.1. Swapping the files without swapping the credit leaves a false
     statement in the one place the end user reads.
   - ⚠️ **Some licences require their text to travel with the font.** OFL 1.1 condition 2
     obliges every redistribution to carry the copyright notice and the licence, so a style
     shipping `.woff2` under it needs `fonts/OFL.txt` (or `LICENSE.txt`) inside the `.zip`.
     And if `description` points at such a file, **confirm the file exists** — a pointer to
     a deleted licence is worse than no pointer.
   - Boilerplate belongs to the licence it came from. An "AS IS" disclaimer left over from a
     previous licence, floating without the clause it closed, is a defect: delete it or
     replace it with the right one.

## Phase 4 — `config.xml`: legal and authorship

`description` is the only place the end user ever sees third-party credits (§6). Treat an
omission as a defect, not a nicety.

1. **Required fields present and well-formed**: `name` (folder id — no spaces, no accents,
   no special characters), `title`, `version`, `compatibility`, `author`, `license`,
   `license-url`, `description`. The file must be valid XML and UTF-8.
   **`<downloadable>` is obsolete** — eXeLearning no longer reads it. Remove it where you
   find it; it is the one deletion in this file you do not need to ask about.
2. **Inventory every asset, then match it against the text.** List `theme/fonts/*`,
   `theme/icons/*`, `theme/img/*`. For each, decide: authored by the style's author, or
   third-party? Every third-party font, icon set, image, sprite and logo must appear in
   `description` **with its licence**. Missing credits are the single most common finding
   here, and logos are the most commonly missed: a brand mark dropped into `img/` rarely
   gets written down. **Stock assets count too.** `img/licenses.gif` — the Creative Commons
   badge sprite behind `#packageLicense` — ships with nearly every style and reads as
   platform plumbing, but it is a Creative Commons trademark and needs its own line saying
   it is not under the style's licence. Check it in every style; it is the credit most
   often absent.
3. **Trademarks and logos** not covered by the style's own licence must say so explicitly
   *and should be removable* — the usual mechanism is a flag in `style.js` that drops the
   element (§6). If a logo is present with no such flag, report it and offer to add one
   (that is an addition to the style's behaviour: ask).
4. **Licence compatibility.** A style under CC BY-SA that bundles an asset whose licence
   forbids redistribution or relicensing must say that the asset is *not* under the style's
   licence. If the style is derived from another style, the original's terms come along
   (§6) and the derivation must be stated.
5. **Consistency.** The `author` and licence headers in the `/*! … */` comment at the top of
   `style.css` and `style.js` must not contradict `config.xml`.

Adding a missing credit is a direct fix. Changing `name`, `version`, `license` or the
authorship itself is never yours to decide — ask.

## Phase 5 — Responsive

Derive the widths to test; do not use a canned list:

- every `@media` boundary in the CSS, tested **just below and just above** (575 / 577 for a
  `575.98px` breakpoint);
- every width the JS compares against;
- **320px**, the narrowest width WCAG 1.4.10 expects to work;
- a wide one past the layout's `max-width` (typically 1440 or 1920) — the content must not
  strand in a corner or stretch to unreadable line lengths.

At each width, in each of the three formats, check: no horizontal scrollbar; nothing
clipped or overlapping; the menu reachable and closable; fixed bars not covering content;
touch targets not smaller than 24×24 CSS px (WCAG 2.5.8, AA in 2.2); text still reflowing
rather than shrinking. Then 200% zoom at 1280px, which is a different failure mode from a
narrow viewport.

`@media print` matters for the single-page format (§7) — it is what that format is used for.

## Phase 6 — Verify in the browser

Static reading finds errors; only the browser finds *problems*. **Ask for permission before
opening Chrome**, and say what you intend to do:

> Para comprobar contraste real, resoluciones y errores de consola necesito abrir Chrome
> sobre `contents/`. ¿Lo autorizas? Si prefieres que no, sigo con revisión estática y te
> marco lo que queda sin verificar.

If granted, load the Chrome tools in one `ToolSearch` call, then for each of the three
formats:

1. Serve, do not use `file://` (§5) — the workspace is under XAMPP or `docker-compose`, so
   use the `http://` URL. If you must also check `file://`, do it as a separate pass and
   only for image/mask questions.
2. **Hard reload** (§4). Anything other than `style.css`/`style.js` is cached, and a stale
   file produces conclusions that look exactly like real bugs.
3. Read the console. Any error from `style.js` is a defect; errors from `libs/` are not
   yours but are worth reporting.
4. Run `assets/a11y-probe.js` in the page, through whatever tool your harness gives you for
   evaluating JavaScript in a live tab (with Claude in Chrome, `javascript_tool`). It
   returns computed contrast failures
   against the level you agreed, a presence map of the optional elements, focusable
   elements with no visible focus indicator, images without `alt`, and duplicate ids. It
   measures the **real** surface, which is the whole point (§8, §13).
5. Resize through the Phase 5 widths and look. Screenshot the failures.
6. If the style has dark mode: repeat 3–5 with it on. For a filter-based dark mode, check
   hairlines and icons specifically — they are the usual casualty (§12). **Measuring colour
   through a filter needs a filter**: the CSSOM gives you source values, not what the reader
   sees. Push each one through a canvas carrying the same filter — `ctx.filter =
   'invert(1) hue-rotate(180deg)'`, fill, read the pixel back — which is the same
   implementation CSS uses. Count the filters on the text and on the element that paints the
   background **separately**: inside a re-inverted island the text is filtered twice while an
   ancestor's background is filtered once, and treating both the same invents failures that
   are not there.
   **Presentation mode**, on the website only: load it with `?exe-presentation=1` (light and
   dark) and check the `#exe-presentation-toggler` badge, then `M` to collapse and reopen the
   menu and `←`/`→` to change page at a wide and a narrow width. `references/checks.md` §3
   and §5 have what the runtime expects from the style.
7. **Print.** There is no preview to open, so make the print rules live instead: walk
   `document.styleSheets`, find every `CSSMediaRule` whose `conditionText` is `print`, set
   `media.mediaText = 'all'`, measure, then set it back. Check it with dark mode **on** as
   well — that combination is where the damage is (§6 of this catalogue). Do not stop at the
   first print block; a style may have several.
8. Re-run the probe after every fix. A contrast fix on one surface routinely breaks another.
9. **The eXeLearning editor, if a workarea is open.** The exports are only half the surface:
   the same `style.css` loads into the application, where `#node-content-container` also
   carries `.exe-content`. **`references/checks.md` §12** has the checklist — the icon-picker
   colour in particular has to be checked in **every** style, because the application declares
   `--exe-icon-color` itself and a style that does not override it shows the application’s
   green instead of its own palette. The Style Designer cannot show any of this; it needs the
   workarea open, and the rules can be tried by injecting them live rather than re-importing
   the zip. If there is no workarea to look at, say so in **No verificado**.

If permission is refused, state in the report exactly which findings are unverified
inferences rather than measurements.

**Do not trigger `alert`/`confirm`** — a modal freezes the extension.

## Phase 7 — Apply

Work in this order, because later fixes depend on earlier ones: JS errors → invalid CSS →
dead code (only what the user approved) → contrast and focus → responsive → typography →
`config.xml`.

House style is not optional (§1): English comments, one line where possible, 4-space
indent, one declaration per line, lowercase hex (three or six digits) or a `:root` variable, single
quotes in JS. Every `!important` carries a one-line comment naming the inline style it
fights — if you add one without that comment, you have added a defect.

**The style files ship to third parties as they are.** No rationale, no history, no
instructions in the comments. Your reasoning goes in the report, not in the file.

**Never annotate a fix with the reason it was needed.** This is the rule most often broken,
because a contrast fix feels like it needs justifying. It does not — not in the file.
Contrast ratios, WCAG clause numbers, thresholds, surface colours, measurements and "this
used to be X" are all report material. The comment below is exactly what not to write:

```css
/* Wrong — the surface, the number and the diagnosis all belong in the report */
/* The current tab is painted #efefef, where the link colour drops below 4.5:1 */
.exe-content .fx-tabs a {
    color: #a94f2d;
}

/* Right */
.exe-content .fx-tabs a {
    color: #a94f2d;
}
```

Keep only two kinds of comment: a short section label (`/* Teacher mode */`, `/* Focus */`),
and the one-line note §1 requires beside an `!important`, naming the inline style it fights.
Everything else: **no comment, no detail**. When in doubt, write none.

Applies to the whole pass, not just new rules — if you leave a comment of this kind behind
after editing a declaration, remove it.

Re-verify after applying. A fix you did not re-check is a claim, not a result.

## Phase 8 — Report

Short, in the user's language, grouped by severity. For each finding: file and line, what
is wrong, what you did — or what you need decided.

- **Corregido** — applied, with the one-line reason.
- **Requiere tu decisión** — the typo candidates, the deletions you did not make, anything
  that would touch the general presentation. State your recommendation for each.
- **Sugerencias** — beyond the agreed level or beyond the remit: things worth doing that
  you are not going to do unasked.
- **No verificado** — optional elements absent from this content set, formats you could not
  open, checks refused. Be explicit; an unstated gap reads as a pass.

Numbers, not adjectives: `4.31:1 sobre #ddebf8, mínimo 4.5:1` beats "poco contraste". If
the content set could not exercise the optional elements, say so here and ask for a second
export set that does.
