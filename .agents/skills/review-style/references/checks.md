# Check catalogue

Worked through in Phase 2, kept open through Phase 5. Section marks (§) are `AGENTS.md`.

Each check says what to look for and — where it matters — how to tell a defect from a
deliberate decision. **A style is allowed to be unusual. It is not allowed to be broken.**

---

## 1. CSS validity

| Check | Notes |
| --- | --- |
| Unbalanced braces, stray semicolons, unclosed comments | One unclosed `{` silently kills the rest of the file. Count braces before anything else |
| Unknown properties and misspellings | `colour`, `font-weigth`, `alignt-items` |
| Invalid values | `border: 1px solid` with no colour is valid; `margin: 10` is not |
| Missing unit on a non-zero length | `top: 50` never applies |
| `url()` targets that do not exist | Resolve against **`theme/`** for CSS (§3). List `theme/img/` and match every `url()` |
| Shorthand clobbering a longhand set earlier in the same block | `background-color` then `background:` wipes it |
| `@media` that can never match | `(min-width: 900px) and (max-width: 600px)` |
| Selectors the compatibility target may not support | `:has()`, `:is()`, `@container`. Present in this style already, so the target is modern — but flag a new one |
| Duplicated selector blocks | Later wins entirely. Check whether the earlier one has a reason to exist |
| `!important` with no comment naming the inline style it fights | §9. Missing comment = defect |
| Colour format | Lowercase hex, three or six digits, or a `:root` var (§1). A three-digit hex is fine as long as it is a valid one (`#fff`, `#c00`); `#FFF`, `#DDEBF8`, `red`, `rgb(…)` for opaque colours are all house-style violations |
| Indentation, one declaration per line, 4 spaces | §1 |

**Overridden declarations inside one block** are the most common dead code in a style:

```css
.nav-button-right {
    background: #005fcc;                          /* dead: overridden two lines below */
    background: url(img/arrow-left-01.svg) …;
}
```

**Orphan declarations** are the second most common: a `background-position` on an element
that has no `background-image`, a `transition` on a property nothing changes, a `z-index`
on a `position: static` element, a `flex` child of a non-flex parent. Each is inert. Each
is also a *clue* that something was removed and its companion was left behind — say which
you think it is.

---

## 2. Specificity and the cascade (§10)

Load order is `bootstrap → idevice css → exe_effects.css → exe_atools.css → base.css →
your style.css`. Yours is last, so equal specificity wins.

- **`!important` used for an ordinary override** is a defect: it was never needed, and it
  makes the next override harder. Test by removing it.
- **A class rule trying to override an id rule.** `#siteFooter a` cannot be beaten by
  `.exe-content a:hover`. When the base uses an id, your states must use the same id. Grep
  `contents/*/libs/**/*.css` for id selectors on anything you recolour.
- **`base.css` claims `:hover` and `:focus` on content links at low specificity.** Recolour
  a link without listing its states and the base keeps them — the link changes colour but
  its hover does not. Every recoloured link needs `:hover`, `:focus` and `:visited`
  considered explicitly.
- **`base.css` beats `exe_effects.css`.** A component that looks wrong out of the box is
  often the cascade, not the component.
- **`exe_effects.css` pairs a background with a colour on the current pagination chip**, and
  a style rule for every pagination link ties on specificity and loads later, so it takes the
  colour and leaves the background. **§11 below** — check it in every style.
- **Two of your own rules at equal specificity: the later wins entirely, not per-value.**
  Where one property carries two meanings at once — two markers packed into one
  `box-shadow` — only a combined selector declaring both works.

---

## 3. JavaScript

| Check | Notes |
| --- | --- |
| Syntax errors, unreachable code, unused functions and variables | An unused function may be an API for a future flag — ask before removing |
| Handlers bound to selectors that match nothing | Apply the typo rule in `SKILL.md`. Never delete on your own judgement. Its closed-identifier exception covers the other half: a misspelled id the style both writes and reads can be renamed — grep, count the sites, rename them all at once, no browser check |
| `.html()`, `.attr()`, `.offset()` on an empty jQuery set | jQuery tolerates most calls on an empty set, but `.html()` returns `undefined` and the next `.replace()` **throws**, killing everything after it |
| Native DOM on a possibly-absent element | `document.querySelector(x).classList` throws on `null`. jQuery does not; raw DOM does |
| `localStorage` read or write without `try/catch` | §9. It **throws** in a sandboxed iframe or with third-party cookies off. In SCORM/IMS that is not an edge case, it is Safari's default |
| Parse-time code touching `<body>` | A script running before `</head>` cannot read the format classes of §7 — `<body>` does not exist yet |
| Parse-time and `init()` out of sync | If one sets a class before first paint, the other must be able to undo it where the feature does not apply |
| A `.replace()` on a label the application wrote | ⚠️ **The word order is not yours.** A style that rebrands the footer link with `html.replace(' eXeLearning', ' eXeLearning + <brand>')` matches nothing in Basque, whose label is `eXeLearning-ekin egina` — it *starts* with the product name, so the leading space never appears (`translations/messages.eu.xlf` upstream). The replacement silently does not happen and nobody notices. Match the bare token, and check that the subtree really is text: `.html()` of an `<a>` excludes its own `href`, so a token there cannot be hit by accident |
| Hardcoded interface text | §9. Use `$exe_i18n` — exports are translated. Keys: `menu`, `search`, `more`, `mode_toggler`, `teacher_mode`, `hide`, `download`, `block`, `toggleContent`, and the rest in `libs/common_i18n.js` |
| A URL rewritten by hand | §9. `$exeExport.setUrlParam(href, name, value)` sets one parameter keeping the rest and the fragment; `null` removes it. See below |
| A key read from `$exe_i18n` that the export does not define | Gives `undefined` in the UI. Grep `libs/common_i18n.js` |
| Feature applied in a format where it cannot work | A dark-mode toggle inside an LMS iframe. §7: an intermittent control is worse than no control — disable it for that format rather than letting the browser decide. The shape that works is a declared setting at the top of the style object, `darkModeToggler: 'web'` with `'web' \| true \| false`, read by the same helper that checks `localStorage`. ⚠️ **It forces a second change**: the parse-time `setMode()` runs before `<body>` exists and cannot know the format, so `init()` must undo what it did — `$('html').removeClass('exe-dark-mode')` in the branch where the feature does not apply. Change one, revisit the other, and verify with a stored `exeDarkMode=on` that the other two formats still come up light |
| Global namespace pollution | One object (`myTheme`) is the convention. A bare `var x` at top level can collide with the application's |
| Guard flags that can deadlock | An "in progress" boolean that a failing animation callback never clears freezes the control for good |
| `$(window).width()` compared to a number | Must match a CSS breakpoint exactly, or behaviour and layout switch at different widths. **Reading the breakpoint back from the CSS is the better pattern and not a finding**: it cannot drift. But read a property that actually changes — see the row below |
| A breakpoint probe reading `float` | ⚠️ **Broken whenever the element is positioned.** `position: fixed`/`absolute` computes `float` to `none`, so `$('#siteNav').css('float') == 'none'` returns `true` at **every** width as soon as the menu is fixed above the breakpoint — and the desktop branch it guards becomes dead code. The symptom is silent: the menu still opens and closes, but the branch that removes `nav=false` never runs. Read `css('position') == 'static'` instead, and **verify at four widths**, not one |
| jQuery API removed in the bundled version | Check the version first: `contents/*/libs/jquery/`. `.bind()`, `.delegate()`, `.size()` are *deprecated* in jQuery 3 and still work; they are removed in 4. Deprecated-but-working is a suggestion, not a defect — say which it is |
| Markup built by the JS | The page must stay readable with JS off (§9). Check that no content is *only* reachable through injected markup |
| Quote style | Single quotes (§1) |

**Query parameters are a shared channel, and hand-rolled URL surgery breaks it.** A style
that carries its own state across navigation — `nav=false` for a collapsed menu is the
usual one — rewrites links the application generated. Through those links also travel
`exe-teacher`, `q`, `print` and the four xAPI credentials. The three broken patterns, all
of which survive a casual test because the sample content has no other parameters:

```js
e.href = ref.split('?')[0];                    // drops every parameter AND the fragment
e.href = ref + (cond ? '&' : '?') + 'nav=false';  // guesses the separator; a second '?'
window.location = this.href + '?nav=false';    // lands inside the fragment if there is a #ancla
```

All three are replaced by `$exeExport.setUrlParam(getAttribute('href'), 'nav', value)`,
with `value` `null` to remove. Use `getAttribute('href')`, not the `.href` property, which
absolutises the export's relative links. Detection belongs to `URLSearchParams`:
`new URLSearchParams(location.search).get('nav') === 'false'`, not `indexOf('nav=false')`,
which also matches `?xnav=false` and a fragment that happens to contain the text.

It is a pattern several styles inherited from one another, so **check it even when the
style looks tidy**, and check the removal branch as well as the addition — dropping the
query silently breaks LMS tracking and teacher mode, and nothing on screen says so.

**Acceptance test — run these seven through both branches.** Reading the code is not enough;
each defect below survives a casual click because the sample content has no other parameters.

```
page.html                       page.html?nav=false
page.html#sec3                  page.html?nav=false#sec3
page.html?exe-teacher=1         page.html?a=1&b=2
page.html?exe-teacher=1#sec3
```

| Input and branch | Defective result | What it proves |
| --- | --- | --- |
| `?exe-teacher=1` + REMOVE | `page.html` | drops the whole query |
| `?nav=false#sec3` + REMOVE | `page.html` | drops the fragment too |
| `#sec3` + ADD | `page.html#sec3?nav=false` | parameter lands inside the fragment |
| `?exe-teacher=1#sec3` + ADD | `...?exe-teacher=1#sec3&nav=false` | same, with `&` |
| `?exe-teacher=1` + ADD | `...?exe-teacher=1?nav=false` | second `?`, malformed URL |

ADD must be idempotent: applying it twice does not duplicate the parameter. And when
filtering, compare **the key**, not the string — `p.split('=')[0] != 'nav'`, never
`p != 'nav=false'`, which lets `nav=FALSE` and a repeated pair through.

Detection has its own four, all of which must return `false`:

```
page.html#nav=false      page.html?nav=falsey
page.html?xnav=false     page.html?q=nav%3Dfalse
```

⚠️ **Fix reading and writing in the same pass.** Correcting one and not the other leaves the
two out of sync, which is harder to diagnose than either defect alone.

**Injected markup must be accessible.** A `<button>` the style builds needs an accessible
name (visible text, or a visually-hidden `<span>`, or `aria-label` from `$exe_i18n`), and a
toggle needs `aria-expanded` kept in sync. A `<div>` with a click handler is not a button:
it is not focusable and not operable from the keyboard (WCAG 2.1.1). Clipping the label with
`clip: rect(1px 1px 1px 1px)` keeps it available to a screen reader — `display: none` does
not.

**Then check the hiding actually reaches it.** A class the JS writes onto injected markup —
`class="sr-av"`, `class="sr-only"`, `class="visually-hidden"` — only hides the label if a
rule matches *that element*. Two ways it silently fails: the class is defined nowhere, or
it is defined **scoped to another component** (`#eXeAtools .sr-av` does not reach a button
outside the accessibility toolbar). The style's own hidden-text rule usually lists selectors
one by one, so a newly injected control is easy to leave off the list. The symptom is a
label rendered on top of an icon button, which the browser makes obvious and static reading
does not — flag it as a candidate and confirm it in Phase 6.

**`sr-av` is the exception**: the export's `content/css/base.css` defines it unscoped, next
to `.js .js-sr-av`, `.exe-hidden-accessible` and `.js .exe-tooltip-text`, and that sheet is
always loaded. A style that clips an injected label with `class="sr-av"` and defines nothing
itself is correct — do not report it. The check above is for the style's own classes.

---

## 4. Accessibility, at the level agreed in Phase 0

Thresholds (§13): body text and links **4.5:1** (AAA: 7:1); large text ≥24px or ≥19px bold
**3:1** (AAA: 4.5:1); icons, borders, focus rings and any non-text graphic that carries
meaning **3:1** — that last one has no AAA step.

- **Measure against the real surface, never against white.** A colour that clears AA on
  `#ffffff` by a small margin fails on a tinted panel, and tinted panels are everywhere:
  box heads, footer, message blocks, the highlighted menu link, the search bar. Enumerate
  every background colour the style declares and test the text and links that land on each.
- **Author content is not white** (§8). Authors tint things.
- **Focus must be visible and must survive.** The application's stylesheets set
  `outline: none` in several places. Where an outline is removed, a real ring must replace
  it: `outline: 2px solid <accent>; outline-offset: 2px` on `:focus-visible`. A text
  underline alone does not satisfy 2.4.11. **`currentColor` is not safe for a ring** — on a
  filled control the text is white and a white ring on a white page is no ring.
  ⚠️ **The five effects are the exception: `exe_effects.css` draws their rings itself now**
  and the style only sets `--exe-fx-focus-color`. Writing the rules by hand there is
  redundant, and `outline: none` on an FX control is a defect. **§11 below**.
- **Underlines are required for links among prose** (1.4.1); not required for standalone
  controls in a bar of their own.
- **Colour must never be the only signal.** A rule plus an icon is fine; a hue alone is not.
- **Disabled controls** are exempt from contrast, not from being perceivable. `opacity: .3`
  is not a good default, and `filter: grayscale(1)` stacked on top of a low opacity puts a
  control below any threshold at all.
- **Hidden-text technique.** `clip: rect(1px 1px 1px 1px)` with `position: absolute` is the
  convention here. `width: 0`/`height: 0`, `display: none` and `visibility: hidden` remove
  it from the accessibility tree — a button labelled that way has no name.
- **Touch targets** ≥ 24×24 CSS px (2.5.8). Measure the button, not the icon inside it.
- **Reduced motion.** Any `transition` or `animation` over ~200ms should be disabled under
  `@media (prefers-reduced-motion: reduce)` (2.3.3 is AAA, but the media query costs three
  lines and the omission is worth a suggestion at any level).
- **Zoom to 200%** at 1280px, and **text-spacing overrides** (1.4.12): fixed heights on
  anything containing text will clip. `min-height` is safe; `height` is not.

---

## 5. Optional elements (§8)

The table in §8 is the list. The test is the same for each: **does the style look finished
with the element and without it?**

- `.package-subtitle` absent → does the header keep its padding, or collapse?
- `.page-counter` absent → does whatever was positioned around it reflow? **The single-page
  format never carries one** — the whole project is one document, so there is nothing to
  count. Do not report it as unverified there, and do not write rules that expect it.
- `#teacher-mode-toggler-wrapper` — injected by the app, never in the HTML, and ⚠️ **it does
  not land in the same place in every format**. `exe_export.js` (`teacherMode.addToggler`):

  | Format | Insertion | Title that must make room |
  | --- | --- | --- |
  | Single page | `$('.package-header').before(btn)` | `.package-title` |
  | Website and SCORM | `$('.page-header').prepend(btn)` | `.page-title` |

  So `body:has(#teacher-mode-toggler-wrapper) .package-title { padding-right: 100px }` is
  **only half right** — it covers single page and leaves the website and SCORM unhandled,
  which is the shape most styles ship. Two `:has()` rules, one per insertion point, is the
  correct pattern. A bare `padding-right` on either title is not.

  The wrapper is `float: right`, so it inherits `color` from whatever band it lands in — and
  the two bands are usually different colours. **Measure the label on both**, at normal text
  size (4.5:1), not just the heading beside it, which often passes as large text. Same for
  the switch: `.form-check-input:checked` painted the same colour as the band behind it is a
  1:1 boundary and fails 1.4.11.

  **How to make it appear** (it is not in the HTML, so there is nothing to grep for in the
  markup): load the page with **`?teacher-mode=true`** (aliases `?exe-teacher=1`,
  `?exe-teacher-toggler=1`). That only *shows the switch* — flip it, or dispatch `change`,
  to get `mode-teacher` on `<html>`. And the app refuses to inject it at all unless the page
  holds `.box.teacher-only` or `.idevice_node.teacher-only` (`exe_export.js`), so check that
  the content has some **before** concluding the element is unverifiable. ⚠️ And grep the
  whole export tree: in the sample content the only `.teacher-only` boxes live in
  `contents/*/index.html`, so a sweep of `html/` reports the feature as untestable when it is
  not.

  ⚠️ Two measurement traps once it is on screen, both of which invert the verdict:
  - **In single page the wrapper is a *sibling* of `.package-header`, not a child**, so the
    probe climbs to `.exe-content` and reports white-on-white 1:1. It is really painted over
    the band. Check whether the label's rect falls inside the header's rect and measure
    against the header's own background.
  - **In that same layout the header covers the float in the hit test.** `elementsFromPoint`
    on the switch returns `HEADER.package-header` first — the switch takes no clicks at all.
    `position: relative` alone does not fix it; an explicit `z-index` does. Run
    `elementsFromPoint` on both the switch and the label, and confirm the toggle cycles.
- `#packageLicense` — its class carries *which* licence (`cc-by-sa`, `cc-0`…). Styling one
  variant and not the others leaves the rest unstyled. Check the sprite offsets for each.
- `#siteUserFooter` — arbitrary author HTML, **including links**. The footer link colour
  must clear the threshold **on the footer surface**, not on the page background.
- `.box-toggle` / `.box-icon` / `.box.no-header` — the four-way combination is the classic
  breakage. `contents/web/html/box-options.html` exercises it; use that page, not invented
  markup.
- `#siteNav`, `.nav-buttons`, `#siteLogos` — website only. Anything positioned relative to
  them needs a fallback in the other two formats.
- `#siteLogos` in particular: styles that ship a logo band and styles that do not are both
  normal. If the CSS reserves space for it unconditionally, single-page and SCORM get a gap.
- `.exe-fx`, `.exe-dl`, `.exe-block-*`, `.exe-attachment-link` — only if the author used
  them. §9: some read a colour out of your CSS and write it back inline. Setting `color` on
  the container is the intended door; remember the prose inside then needs its colour back.

---

## 6. Dark mode (§12)

Only if the style has one. Which implementation decides the checks.

**Filter-based** (`filter: invert() hue-rotate()` on `html`):

- The round trip is **not** an identity — saturated colours clip irreversibly. Verify with
  numbers.
- Two regimes coexist: outside a re-inverted element write the *source* colour; inside one
  write the colour you want to *see*. Confusing them is the easy mistake — and the reason a
  dark-mode block full of light greys can be correct.
- **Light hairlines are the usual casualty.** A pale border returns nearly black; an
  outlined control loses its outline and falls under the 3:1 of 1.4.11. Check every border
  and every icon.
- Images, photographs and logos shift colour. `:is(img, video, iframe)` re-inversion is the
  standard patch — check the exception list covers every image-bearing element, including
  CSS `background-image` icons, which the `img` selector does **not** reach.
- A `filter` applies to the whole subtree and cannot be undone by a child.
- Do not rely on `popover`/top-layer to escape an ancestor filter.

**Variable-based** (`:root` palette reassigned under `html.dark-mode`):

- Every variable used in light mode has a dark counterpart — a missing one silently keeps
  the light value.
- Contrast re-measured in dark, all of it. Dark surfaces fail differently.
- `prefers-color-scheme` honoured, or deliberately not.

**Both:**

- The toggle only makes unambiguous sense on the website format (§7).
- ⚠️ **The class goes on `<html>`, written at parse time — not on `<body>` from `ready`.**
  This is what keeps the page from flashing: a script at the end of the style's `.js` runs
  while the head is still being parsed, so `exe-dark-mode` is on the root element before the
  first paint. Set it on `<body>` inside `$(function(){})` instead and the reader gets a
  white page that turns black a moment later, on every single navigation. Check *where* and
  *when*, not just that a class is applied.
  The price is that `<body>` does not exist yet, so **the format cannot be read there** — a
  style that limits the toggle to the website (see `darkModeToggler` in §3) must undo the
  class from `init()`: `if (!darkMode) $('html').removeClass('exe-dark-mode')`. Verify with
  `exeDarkMode=on` stored that the other formats come up light **without erasing the stored
  preference**, which still belongs to the website export.
  A residual flash in the formats that undo it is the accepted trade-off, and only happens in
  local preview, where all three exports share `localhost` as their origin.
- Stored preference is per origin — everything on `localhost` shares it. A preview that
  comes up dark unasked is the stored key, not a CSS bug.
- The toggle must have an accessible name and a state (`aria-pressed`), and must be
  keyboard-operable.
- With `localStorage` unavailable the toggle should be **absent**, not present-and-broken.
- **Dark mode prints.** Browsers apply `filter` when printing, so a reader who prints with
  dark mode on gets an inverted page — a black sheet. `@media print` must switch it off.
  Nothing warns you: the screen looks right.
- **Switching it off means every filter, not just the one on `html`.** A filter-based dark
  mode also carries re-inversions (`:is(img, video, iframe)`, a licence badge, a top bar).
  Those exist to *cancel* the global filter. Reset only the global one and they stop
  cancelling and start inverting for real: the photographs and the logo come out negative,
  which is worse than what you set out to fix. List every re-inverted selector in the print
  reset.

---

## 7. Images and sprites (§3, §11)

- **Where each one resolves is not uniform.** `url()` in the CSS → `theme/img/` (root,
  visible immediately). `src` written by `style.js` → `contents/*/theme/img/`. `src` in the
  exported HTML → `contents/*/theme/icons/`. An image that "did not change" is usually this,
  not a cache miss.
- **Sprite cell grids are a contract.** A sprite is addressed by pixel offset at several
  `background-size` values at once. Changing the cell size moves every icon in the style.
- **`mask-image` does not work over `file://`** (§5) — the icon simply disappears. Exports
  are opened from disk all the time. Use `background-image`.
- Icons should be flat ink on transparency: only those survive a `filter` predictably.
- Deliver at 3× displayed size; check the displayed size, not the file.
- **iDevice icons that do not load are a workspace artefact, not a style defect.** The
  exported HTML asks for `theme/icons/<name>.png` using the name of the iDevice the author
  picked; the folder served comes from whatever style was bundled into the export. Editing a
  style against content exported with a different one breaks that agreement by definition, so
  it happens with every style. Two separate mismatches, often at once: the naming convention
  (`eng_aprenderaprender.png` vs `udl_eng_aprenderaprender.svg` vs the generic `info.png`)
  and the extension (an SVG never loads into `<img src="*.png">`). **Report it once, in one
  line, and stop there** — never rename, convert or move a style's icons to match one export.
  That would make the style wrong for every other project. It belongs to the application:
  the style is not the broken part.
- Images referenced nowhere are dead weight in the `.zip` — report, do not delete
  unilaterally (an icon may be for an iDevice absent from *this* project).

---

## 8. The three formats (§7)

Every export carries `exe-export` plus one format class: `exe-web-site`, `exe-single-page`,
`exe-scorm` (+ `exe-scorm12`), `exe-ims`, `exe-epub`/`exe-epub3`.

Design targets the default; the **website** is the exception — it is the only one with a top
bar, a navigation menu and a logo band.

- A rule written for the website but not scoped to `.exe-web-site` leaks into the other two.
  Look for fixed positioning, `margin-left` compensations for the menu, and `padding-top`
  for the top bar.
- **Single page** is used for printing — `@media print` is part of the deliverable, not a
  nicety. Check that fixed bars, togglers and the page counter are hidden, that link URLs
  are exposed, that margins reset, and that nothing forces a background the printer must
  render.
- ⚠️ **Light text stranded on a dropped background is the most common print defect, and the
  screen gives no hint of it.** Browsers omit background colours unless the reader ticks
  "Background graphics", but they keep `color`. Every band the style paints — package header,
  page header, footer, any coloured chip — is `color: #ffffff` on a colour, and prints as
  **white on white**. The title of the project, the title of the page and the licence footer
  are the usual casualties, i.e. everything that identifies the document.
  Enumerate every rule that pairs a light `color` with a `background`, and give each one a
  print counterpart (`background: none; color: #000000`) or `print-color-adjust: exact` where
  the surface is load-bearing, as in `.pre-code`. Verify by flipping the print blocks to
  `all` (Phase 6.7) and **reading the computed `color` against the computed background**.
- **`@media print` adds no specificity, so its position in the file decides whether it
  works.** A print rule loses to any screen rule that is more specific, and to an equally
  specific one written later. Both are common: a `.exe-content` print reset beaten by a
  `.exe-export .exe-content` screen rule, or a dark-mode print reset placed above the
  dark-mode block it is meant to undo. Read every print declaration against what already
  matches the same element — an inert print rule looks identical to a correct one.
  A style may legitimately need **more than one `@media print` block** for this reason;
  that is not duplication to clean up.
- **SCORM/IMS** live in an LMS iframe. The host brings its own surroundings and usually its
  own dark mode. `localStorage` is third-party storage there.
- `<html>` carries `id="exe-index"` on the index page — useful for targeting the home page,
  and the reason relative paths differ by one level between index and the rest.
- The style must not leak into the eXeLearning editor interface: content rules scoped to
  `.exe-content` (§8). Rules targeting `body[installation-type]` or `#node-content-container`
  are deliberately *for* the editor — do not "clean" them away.

---

## 9. Source code blocks

Two unrelated renderings, and the style must leave both usable. `code-*.html` in the web
export exercises them.

| Markup | Comes from | Surface |
| --- | --- | --- |
| `.pre-code > div > pre > code` | `base.css` | `#112c4a`, text `#e7ecf1`, `font-size: 12px` |
| `.highlighted-code.language-*` | Prism (`libs/exe_highlighter/`) | `#f5f2f0`, text `#000` |
| `.highlighted-code.code-style-2` | Prism, Okaidia | `#272822`, text `#f8f8f2` |

`language-*` and `line-numbers` sit on the **outer div**, not on `<pre>`. Prism copies them
onto `pre`/`code` at runtime, so every `pre[class*=language-]` rule only applies after the
JS has run — reading the HTML will not show you the element you are styling (§9).

1. **Monospace must survive.** A style setting `font-family` on `.exe-content` does *not*
   reach `pre`/`code`: the UA and Bootstrap rules match those elements directly and beat
   inheritance. Verify rather than assume, and verify it **with the eXe atools fonts on**
   too — `body.exe-atools-od`, `-ah` and `-mo` each set a family on `.exe-content`, and a
   reader may have any of them active. A style that sets `font-family` on `pre`, `code` or
   `*` breaks alignment in every code block; that is a defect.
2. **The two blocks must compute the same `font-size`.** This is the check that catches
   real breakage. `base.css` pins `.pre-code` to an absolute `12px` (Bootstrap's `pre
   { font-size: 87.5% }` then drops it to **10.5px**), while Prism uses a relative
   `font-size: 1em`. So the moment a style scales prose — a presentation mode with
   `.idevice_node.text { font-size: 1.2em }` is the usual case — the highlighted block
   grows and the plain one does not, and the same code appears at two sizes on one page.
   Measure both; do not eyeball one.
3. **Line numbers desync silently.** `.line-numbers-rows` is absolutely positioned and
   inherits `line-height` from `pre`. Setting `line-height` or `font-size` on `code` alone,
   or on one of the two and not the other, slides the numbers out of register a little more
   with every line. Compare the computed `line-height` of `pre` and of `.line-numbers-rows`.
4. **`white-space: pre` is load-bearing.** A global `word-break`, `overflow-wrap` or
   `white-space: pre-wrap` reflows code and breaks both the numbering and the indentation.
   Long lines are meant to scroll inside `pre` (`overflow: auto`), which is the correct
   answer to WCAG 1.4.10 for code — a horizontal scrollbar on the block is not a finding, a
   horizontal scrollbar on the **document** is.
5. **`tab-size`.** Prism sets `4`; the plain block inherits the browser default `8`. Same
   file, two indentations.
6. **Print.** Both dark surfaces carry near-white text, and browsers drop backgrounds unless
   asked. Printed, `#e7ecf1` and `#f8f8f2` land on white paper and the code vanishes. Check
   it by flipping the print blocks to `all` (Phase 6.7). `print-color-adjust: exact` fixes
   it without touching a single colour.
7. **Prism's palette is not the style's.** Several default-theme tokens fail AA on `#f5f2f0`
   — punctuation and line numbers `#999999` at 2.56:1, `string` 3.08:1, `function` 3.59:1,
   `comment` 3.64:1, `keyword` 4.47:1. They ship with eXeLearning, the dark `code-style-2`
   theme passes, and repainting them is a change to the general presentation of every code
   block. **Standing decision: never repaint them.** Report the numbers once and leave the
   tokens exactly as eXeLearning ships them — this is settled, not a question to re-open each
   review.

---

## 10. Things that look like defects and are not

Check before reporting. Reporting a deliberate decision as a bug wastes the user's time and
makes the real findings harder to see.

| Looks wrong | Often correct because |
| --- | --- |
| Light colours inside a dark-mode block | Filter-based dark mode inverts them (§12) |
| A rule for `body[installation-type]` or `#node-content-container` | Targets the eXeLearning editor, not the export |
| A selector matching nothing in `contents/` | §8 optional element, absent from *this* project |
| An identifier that looks misspelled | It may be the canonical upstream name inherited from the base style — `#searchBarTogger` is real. Grep the other styles and `libs/` before touching it |
| No `@font-face`, no `fonts/`, no dark mode, no `style.js` | A minimal style. Absence is only a finding when something in the style refers to it |
| `compatibility` below the current eXeLearning version | States which version the style targets; it is not a defect to be bumped |
| A duplicated block differing only in `z-index` | Two states of the same control (`.siteNav-off` and not) |
| An iDevice icon that does not load | Name or extension mismatch between what the export asks for and what the style ships — a workspace artefact, never a style defect (§7) |
| `filter: invert(1)` on an icon | Recolouring flat ink — the cheap, intended technique (AGENTS.md §11) |
| No `:focus-visible` rule for tabs, pagination or the accordion | `exe_effects.css` owns those rings now; the style only sets `--exe-fx-focus-color` (§11 below) |
| A rule using `.fx-carousel-pagination` with no link styling in it | Still needed for the prev/next arrows' position and size; only the *link* rules are duplication (§11 below) |
| `clip: rect(1px 1px 1px 1px)` twice, with and without commas | Deliberate legacy-syntax fallback |
| `!important` in `@media print` | Print overrides frequently need it; still wants its comment |
| `display: block !important` on the search box | Fighting an inline style from the application's JS (§9) |
| A `100vh` with a `calc()` subtracting a bar height | Sticky-footer arithmetic; check the number matches the bar, do not remove it |

### ⚠️ A rule that "does not apply" right after a resize

`getComputedStyle` immediately after changing the viewport width returns the **pre-transition**
value, and a style that animates the property you are measuring will read exactly as if the
`@media` rule had not applied at all. `educablue` transitions `background-position` on
`.nav-buttons` (0.25s) and `base.css` transitions everything on `#made-with-eXe a` (0.5s), so
a logo measured this way sits at its old offset and looks like a broken cascade.

Before concluding that a rule loses, prove it: change that declaration through the CSSOM
(`rule.style.setProperty(...)`) and see whether the computed value follows. If it follows, the
rule wins and your reading was stale.

Waiting is not reliable: CSS transitions advance on the animation timeline, which Chrome
throttles when the window is not focused, so a value can stay stuck at its start for as long
as you care to wait. **Turn the transitions off in the page under test before resizing** and
the first reading is the final one:

```js
const st = doc.createElement('style');
st.textContent = '.nav-buttons, #made-with-eXe a { transition: none !important; }';
doc.head.appendChild(st);
```

One more thing about measuring in an iframe (Phase 5): Chrome evaluates media
queries against `innerWidth`, which **includes** the scrollbar, while the
element you measure is ~15px narrower — say which of the two a number is.

---

## 11. Effects (FX) — what `exe_effects.css` now owns

Upstream PR **exelearning/exelearning#2344** («Improve the presentation and accessibility of
the effects», CSS only, `exe_effects.js` untouched) moved the focus rings and the underline
policy of the five effects **into `exe_effects.css`**. Work a style did by hand before is now
either redundant or actively wrong.

The styles bundled with eXeLearning (`base`, `universal`, `flux`, `neo`, `nova`, `zen`) are
already adapted, and the PR ships a test suite that keeps them that way. **This section is for
the styles the user hands you**, which are not covered by it.

### First: does this content set even have it?

```bash
grep -c "exe-fx-focus-color" contents/*/libs/exe_effects/exe_effects.css
```

**Three non-zero counts, or the section does not apply.** A `0` means the exports predate the
PR: the style still has to draw its own rings, and everything below turns into a suggestion
for when the content is re-exported. Say which case you are in — never apply this against an
old export set and leave the style with no focus indicator at all.

Every effect is wrapped in `<div class="exe-fx exe-…">`, and the new rules are scoped to
`.exe-fx`.

### The ring is a custom property now, not a rule

`exe_effects.css` draws it on `:focus-visible` for tabs, pagination, accordion titles,
`.fx-timeline-expand` and both timeline headings:

```css
outline: 2px solid var(--exe-fx-focus-color, #1a1a1a);
outline-offset: 2px;
```

| Check | What to do |
| --- | --- |
| The style writes its own `:focus-visible` outline for `.fx-tabs a`, `.fx-pagination a`, `.fx-carousel-pagination a`… | **Redundant.** Replace the lot with `.exe-content { --exe-fx-focus-color: <accent>; }` |
| The style sets no `--exe-fx-focus-color` | The ring falls back to `#1a1a1a`. Legible, but off-palette — worth setting |
| `outline: none` / `outline: 0` on anything matching `.fx-`, `.exe-accordion`, `.exe-tabs`, `.exe-paginated`, `.exe-carousel`, `.exe-timeline` | **Defect.** It suppresses the ring the sheet now guarantees. The PR's test asserts no bundled style does this |
| The ring painted with `box-shadow` | The sheet deliberately never uses `box-shadow` for the ring, so a style can keep painting the *control* with one and both coexist. Do not take that channel over |
| `overflow: hidden` or `auto` on an FX container in the style | Clips the ring. The PR removed exactly that from `.fx-tabs` and `.fx-carousel-pagination` (clearfix `:after` instead) and added `.js .exe-accordion:has(.fx-accordion-title:focus-visible){overflow:visible}` |

⚠️ **Measure the ring against the page background, not against the control.** `outline` is
drawn *outside* the box, over whatever is behind it. Threshold 3:1 (§13).

### ⚠️ The current-page chip — the trap worth checking in every style

`exe_effects.css` pairs a background and a colour on the current pagination item:

```css
.fx-pagination .fx-current a{background:#333;color:#fff}
```

A style rule that recolours **every** pagination link —
`.exe-content .fx-pagination a { color: … }` — matches the chip too, at equal specificity and
loading later, so it **repaints the text and leaves the dark background underneath**. Dark ink
on `#333`.

It is easy to miss twice over: the chip only fails **at rest**, because
`.fx-pagination .fx-current a:hover, :focus` re-asserts `#fff` at higher specificity. Hovering
the element to inspect it makes the bug disappear.

Two correct shapes, both acceptable:

```css
/* Leave the chip alone */
.exe-content .fx-pagination li:not(.fx-current) a { color: #b14900; }

/* Or restyle it whole — both halves of the pair, never just the colour */
.exe-content .fx-pagination .fx-current a { background: #145cb1; color: #ffffff; }
```

### `#efefef` is the surface that decides the style's link colour

`exe_effects.css` paints `#efefef` in **five** places: the current tab's label, the tab panel,
every pagination chip, the paginated page panel and the carousel panel. Three of those hold
author prose, links included, and the chips are links themselves. So `.exe-content a` lands on
`#efefef` in any project that uses an effect, and that surface — not white — is what binds the
link colour: **4.5:1 on `#efefef` needs a relative luminance ≤ 0.153**, appreciably darker than
what white alone would allow.

Do not try to buy back a brighter link with a scoped rule for the tabs. It is not one special
case, it is five components and the prose inside three of them; the exception would have to be
wider than the rule, and the luminance it buys back is a couple of hex steps. Measure on
`#efefef` and pick one colour.

### The carousel pagination is not a separate component

Its list carries **both** `fx-carousel-pagination` and `fx-pagination`, and the PR deleted the
duplicated link rules from `exe_effects.css` accordingly. So in a style:

- A third selector for `.fx-carousel-pagination a` alongside `.fx-pagination a` is
  **duplication**, not a defect — fold it into `.fx-pagination a` and say so.
- `.fx-carousel-pagination` on its own is **not** dead: it still positions the prev/next
  arrows and sets their font size. Do not delete rules that use it for layout.

### Underlines

The sheet now guarantees **no underline** on the controls in every state (WCAG 1.4.1: they are
not links inside prose), and **keeps** the underline on `.fx-timeline-minor h3 a`, which are
plain text links inside the event list.

- A style adding `text-decoration: underline` to an FX control now fights the sheet.
- A style that carried `text-decoration: none` workarounds for these controls can drop them.
- Removing the underline from `.fx-timeline-minor h3 a` **is** a finding: those are prose links.

---

## 12. Inside the eXeLearning editor

The exports are only half the surface. The same `style.css` is loaded into the **workarea**,
and it is the half nobody looks at, because checking it means importing the `.zip` into
eXeLearning — the Style Designer cannot show it. The application's own reference is
`doc/development/styles.md` in the `exelearning` repository; read it before touching any of
this.

**The editor content pane is `<section id="node-content-container" class="exe-content …">`.**
Both on one element, so **every `.exe-content` rule is already live in the editor** — that is
why a style leaks there when its content rules are unscoped (§8), and equally why the style
is responsible for how the editor looks.

⚠️ **But the workarea's `main.css` writes `#node-content-container.exe-content …`**, which
outranks any plain `.exe-content` rule. Where the editor disagrees with the preview, that is
almost always the reason. Match the app's own selector to override it — equal specificity is
enough, since the style loads last:

```css
#node-content-container.exe-content .box-head .exe-icon img { … }
```

### What "correct in the editor" means

Two rules, in this order (`AGENTS.md` §1):

1. **The author has to be able to tell what the exported page will look like.** Identical is
   impossible and is not the target; *recognisable* is — same typeface, colours, icon
   treatment, and the same weight of a heading against its body text.
2. **Touch the application's interface as little as possible.** The style's reach stops at
   the content area. Toolbars, panels, dialogs and trees belong to the application.

So the defects worth reporting here are the ones that make the editor **misdescribe the
export**, in either direction: something the author will not get, or something they will get
and cannot see.

### ⚠️ Editor-only rules: the ones most likely to be stale

A rule scoped to `#workarea` or `#node-content-container` was written against a host the
author usually could not see, so it outlives its reason. **Check every one of them against
the export**, and specifically for a *second, conflicting decision* about the same edge.

The pattern to look for is a **duplicated separator**. If the export draws the boundary
between two blocks once — typically `.box-content { border-top }` — and an editor-only rule
adds a border to the block above it *plus* a margin, the editor shows **two parallel lines
with a gap between them**, and the detached one reads as a stray rule rather than a boundary.
Real case: `#workarea .box-head { border: 1px solid … !important; margin-bottom: 10px }`
against `.exe-content .box-content { border-top: 1px solid … }` — 10px apart, and the author
reports "a line that is not in the preview".

Before keeping such a rule, check whether the export's own rules already reach the element.
Measure it: read the computed background of the block in the editor. If the style's surface
colour is already there, the border was never load-bearing — the background delimits the
block — and removing it is what matches the export. Zero it explicitly
(`border: 0 !important`) rather than deleting the rule, or the host's own border returns.

### Icon colour — check it in **every** style

⚠️ **The application declares `--exe-icon-color: #6E9F41` itself.** A style that does not
override it gets that green in the icon picker's General icons, whatever its own palette is —
it is not a missing value, it is an inherited one. The resolution order, from
`blockNode.js` (`getCurrentThemeIconColor`), read off the block header, the title and the
icon element in turn:

```
--exe-icon-picker-color  ||  --exe-icon-color  ||  --icon-primary  ||  computed color  ||  #6E9F41
```

- Declare `--exe-icon-color` as the colour of the style's own `icons/` artwork — sample the
  PNG, do not guess from the palette; they are often not the same colour.
- `--exe-icon-picker-color` is needed **only when `--exe-icon-color` is light**, because the
  picker paints its chips on a light background. A dark icon colour needs one variable, not
  two.
- The variable reaches General icons only (inline SVG on `currentColor`). Style icons are
  `<img>`: matching them in the picker takes a `filter`, and only on the `img`, never on the
  chip — the chip has a background and a filter there paints a block over the icon.

### General Icons — the group a style forgets

The picker offers two groups and a style has to serve **both**. Style icons are the `<img>`
artwork in `icons/`; General icons are Material Symbols, a
`<span class="exe-material-icon">` painted with `currentColor` through a CSS mask. A style
that only ever styles `.box-icon img` leaves the second group at the application defaults, and
the two end up different sizes on the same page.

| Check | What to do |
| --- | --- |
| The style sizes `.box-icon img` but never `.exe-material-icon` | Add the span, at the size the artwork actually ends up at — **not** the raw `width` written for the `img` when a `scale` is also in play |
| The span rule drops `transform: scale(1.2)` | **Keep it.** Material Symbols are drawn in a 20px live area on a 24px grid, so without it the glyph comes out ~17% smaller than an edge-to-edge PNG in the same box |
| The style uses the `scale` property anywhere in the chain | ⚠️ `scale` **composes with** `transform` instead of replacing it: `scale: .9` plus `transform: scale(1.2)` leaves the element at `0.9 × 1.2`. Do the arithmetic |
| Only a `.exe-content …` selector | The workarea writes `#node-content-container.exe-content .box-head .exe-material-icon` (1,3,0), which outranks it. One rule carrying both selectors covers editor and export |
| Filter-based dark mode re-inverting `:is(img, video, iframe)` | The span is **not** an `<img>`, so it is not in that list. Add it **only when the Style artwork is light**; when `--exe-icon-color` is dark and meant to invert to light, leaving it out is correct. Either way **measure both groups against the box header** and report how far apart they land — a small gap is not worth a rule |
| The export carries no `exe-material-icon` rule at all | Not a style defect: exports produced before the feature existed have none. Emulate the current default in the browser to test the style's rule, and say the content could not exercise it |

⚠️ General icons are painted with `mask-image`, which **does not work over `file://`** (§5).
An export opened from disk loses them while the `<img>` Style icons survive. That is the
application's technique, not the style's — report it once.

### Text colour — a style that declares none inherits two different hosts

The editor's body colour and the export's are not the same (`#374151` against Bootstrap's
`#212529` at the time of writing). A style that never sets `color` therefore shows the same
box title in two colours, and the author sees the difference the moment they preview.
Declaring it once on `.exe-content` fixes both and changes nothing in the export.

### What to check, and how, without re-importing the zip

Re-importing after every edit is slow, and the editor caches the style. **Inject the
candidate rules into the live workarea and measure**, exactly as with runtime-injected markup:

```js
var s = document.createElement('style'); s.id = 'probe';
s.textContent = '…the rules you are proposing…';
document.head.appendChild(s);
```

Then compare against the same element in the served export. The checklist:

| Check | Where it usually goes wrong |
| --- | --- |
| Box title colour | Inherited from the host; differs between editor and export |
| Box icon size | `main.css` sets `height: 30px` on `.exe-icon img`, and the button's own `padding` caps the image through `img { max-width: 100% }` — so overriding the width alone is not enough |
| Icon picker colour | `--exe-icon-color` left at the application's green |
| Style leaking into the editor chrome | Content rules not scoped to `.exe-content` (§8) |
| `!important` in an editor rule | Needs the §1 comment naming what it fights, like any other |

⚠️ Read the block's real geometry before "fixing" a size: the editor's box header carries
controls the export does not, so matching the preview exactly can push its height. Measure
`.box-head` before and after and say what it did.
