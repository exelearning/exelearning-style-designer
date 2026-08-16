# AGENTS.md

Working rules for building an **eXeLearning style** with the Style Designer and an AI
coding agent.

Read this before editing anything. It is the only place where the rules live: **the style
files ship to third parties as they are**, so they must carry no instructions, no
conventions and no rationale beyond short comments that explain the code itself.

Two audiences, one file: a human designer directing the work, and the agent doing it. If
you are the agent, everything here is binding.

---

## 1. Ground rules

| Rule | Value |
| --- | --- |
| Files you may edit | **`theme/` only** |
| Files you may never edit | `contents/` (the exported sample content), `contents/*/libs/` (the application's own JS and CSS), and everything in `files/` |
| Code comments | **English**, always, in every `.css` and `.js` |
| Comment length | **One line whenever possible.** Two or three only when a value would be a mystery without them |
| Indentation | **4 spaces**, never tabs. One declaration per line |
| Colours | Lowercase hex, three or six digits, or a `:root` variable |
| Strings in JS | Single quotes |
| Accessibility | **WCAG 2.2 level AA** (§12) |

Three more that matter in practice:

- **Prefer overriding an existing selector to inventing one.** Your stylesheet loads last
  (§9), so equal specificity already wins. A new selector is a decision — say so, and say
  why the existing one could not carry the change.
- **Ask when the design goal and the exported HTML disagree.** The HTML is fixed (§7);
  guessing produces a rule that works on one page and breaks on the next.
- **Report briefly.** The change is reviewed in the code, not in prose. State what you
  actually verified and what you did not — a measured number beats an adjective.

### What a comment is for

**Almost nothing.** A comment earns its place only as a short section label, or as the
one-line note beside an `!important` naming the inline style it fights (§9).

**When a value is not obvious, explain it on screen, not in the file.** One brief line in
your reply — the ratio you landed on, the surface you measured against, why this value and
not the neighbouring one — and nothing in the stylesheet. Whoever receives the shipped
style gets the rule; whoever is directing the work gets the reasoning, while they are still
directing it.

```css
/* Bad: explains what the reader can already see, and tells a story */
/* Links the author writes into the footer. They used to take --exe-ink, the heading
   color, so they read as bold text rather than as links, and no hover rule could
   reach them: an id beats .exe-content a:hover, so the color never moved. */
```

Design rationale, decisions and history go in your own working notes, **outside the
style** — and, if the notes are about one specific style rather than about styles in
general, outside this repository too.

---

## 2. The workspace

The Style Designer is a local previewer. It takes three exports of the same eXeLearning
project and wires them all to a single editable copy of the style.

```
theme/           <- THE STYLE. Everything you edit is here
contents/web/    <- Website export        preview target
contents/page/   <- Single page export    preview target
contents/scorm/  <- SCORM 1.2 export      preview target
```

Both folders are recreated from scratch on every upload and are **not** part of the
repository.

### Getting a workspace

`index.php` → **Start** → either upload three exports named `*_web.zip`, `*_page.zip`,
`*_scorm.zip` (the names eXeLearning gives them — do not rename), or click **Use sample
contents** to load the bundled fixtures.

If you upload your own project, **turn every export preference on** (page counter, search
box, and so on) and include every box variant you can — with and without icon, with and
without title, with and without the minimise button. The optional elements in §7 are
exactly what a style breaks on, and you cannot test what you did not export.

### Previewing

The three buttons at the top of `index.php` swap one iframe between the formats. For
iterating on a single page it is faster to open it directly:

```
contents/web/html/<page>.html
contents/page/index.html
contents/scorm/html/<page>.html
```

The sample content ships pages built to exercise specific cases — box variants, message
blocks, the interactive effects, a highlighted menu link. Prefer them to invented markup.

### Finishing

**Finish / Restart** → edit `theme/config.xml` → **Download Style** → import the `.zip`
into eXeLearning. Same screen deletes everything and starts over.

---

## 3. ⚠️ Path resolution: there are three mechanisms, and they do not agree

**This is the single thing to internalise about the workspace.** The exported HTML loads
`../../../theme/style.css`, so **the CSS and the JS at the root are the ones in force**.
Images are not so simple:

| Referenced from | Resolves in |
| --- | --- |
| `url()` inside `theme/style.css` — sprites, background images | **`theme/img/`** (root). Visible immediately |
| `src` written by `theme/style.js` — anything the script injects | **`contents/*/theme/img/`** (each export's own copy) |
| `src` in the exported HTML — iDevice icons | **`contents/*/theme/icons/`** (each export's own copy) |

So: edit a background image and the preview updates. Edit an image that the script or the
HTML points at, and **you must copy it to all three `contents/*/theme/` folders** or you
will be looking at the old one and debugging the wrong thing.

None of this affects the downloaded `.zip`, which is built from `theme/` alone. It is a
preview artefact only.

---

## 4. ⚠️ Cache

The exported HTML adds `?v=` to **`style.css` and `style.js` only**. The HTML itself, the
images referenced from CSS, and the application's stylesheets get no cache buster.

**Hard reload (`Ctrl+Shift+R`) after editing anything other than those two files**, or you
will diagnose a file the browser never loaded. This produces false conclusions that look
exactly like real bugs: a sprite that "did not change", an application stylesheet whose
edit "had no effect".

---

## 5. ⚠️ `file://` is not the same as being served

Exports are frequently opened straight from disk. A browser treats a local file as an
**opaque origin**, which discards anything that needs a CORS check.

- **`mask-image` does not work over `file://`.** An icon painted with a sprite mask simply
  disappears. Use `background-image`, which has no such restriction.
- `background-image`, `<img>` and local `@font-face` are fine.
- Rule of thumb: if something is visible when served but not from disk, suspect CORS
  before suspecting the CSS.

---

## 6. What a style package is

```
<style-name>/
  config.xml        required
  style.css         required
  style.js          optional
  screenshot.png    required, 1200x550 recommended
  icons/            required, iDevice icons
  img/              optional; favicon.png or favicon.ico here replaces the default
  fonts/            optional, .woff2
```

- **Several `.css` or `.js` files are allowed. They load in alphabetical order.**
- The distributable is a `.zip` named after `<name>` in `config.xml`, **with no wrapping
  parent folder**. The Finish screen builds it for you.
- `config.xml` fields: `name` (folder id, no spaces or special characters), `title`,
  `version`, `compatibility`, `author`, `license`, `license-url`, `description`.
- `description` is the only place the end user ever sees third-party credits. **Every
  font, icon set and image that is not yours goes there, with its licence.** Trademarks
  and logos that are not covered by the style's own licence must say so explicitly, and
  should be removable — a setting in `style.js` that drops the element is the usual way.

If a style is derived from another one, its licence terms come along with it.

---

## 7. Export formats

Every export carries `exe-export` on `<body>` plus one format class:

| Format | Body class |
| --- | --- |
| Website | `exe-web-site` |
| Single page | `exe-single-page` |
| SCORM | `exe-scorm` (plus a version class such as `exe-scorm12`) |
| IMS | `exe-ims` |
| EPUB | `exe-epub` / `exe-epub3` |

**Design for the default first, then override for the format that differs.** The website
is almost always the exception: it is the only format with a top bar, a navigation menu
and a logo band, so anything positioned there is the special case, not the baseline.

What each format is really used for, and what it changes:

- **Website** — fully standalone, owns the whole page. The only format where a
  reader-facing control such as a dark mode toggle makes unambiguous sense.
- **Single page** — the whole project in one file, commonly used for printing. Check
  `@media print`.
- **SCORM / IMS** — displayed inside an LMS **iframe**. The host decides the surrounding
  appearance and usually brings its own dark mode. `localStorage` is third-party storage
  there: Safari blocks it and Chrome partitions it or throws when third-party cookies are
  off, so anything that depends on it appears for some students and not others. **An
  intermittent control is worse than no control** — prefer to disable the feature in this
  format rather than let the browser decide.
- **EPUB** — the reader controls a lot. Some application JS deliberately skips EPUB.

`<html>` carries `id="exe-index"` on the index page and `id="exe-<node-id>"` elsewhere.
Useful to target the home page, and the reason relative paths differ by one level between
the index and the rest.

---

## 8. The HTML you do not control

Three different origins, and the difference matters:

1. **Generated by the application** — page header, navigation, footer, boxes. Fixed
   structure, never edit it.
2. **Written by the author** — everything inside the content area. Arbitrary and
   unbounded: any heading level, tables, images, lists, embedded media.
3. **Injected at runtime by the application's JavaScript** — see §9.

`.exe-content` wraps all exported content and exists to keep the style from leaking into
the eXeLearning editor interface. **Scope content rules to it.**

### Elements that may or may not be there

This is the single most common source of broken styles. Never assume any of these exists:

| Element | Missing when |
| --- | --- |
| `.package-subtitle` | The author left the subtitle empty |
| `.page-counter` | Page numbering disabled in the export preferences |
| `#teacher-mode-toggler-wrapper` | The project has no `.teacher-only` content. Injected by the app, not present in the HTML |
| `#packageLicense` | No licence chosen. Its class also carries **which** licence (`cc-by-sa`, `cc-0`…) |
| `#siteUserFooter` | Empty, or filled with arbitrary author HTML **including links** |
| `.box-toggle` | The iDevice is not collapsible |
| `.box-icon` | The iDevice has no icon; the header then carries `.no-icon` |
| `.box.no-header` | No title, no icon and no toggle at all |
| `#siteNav`, `.nav-buttons`, `#siteLogos` | Every format except the website |
| `.exe-attachment-link`, `.exe-fx`, `.exe-dl`, `.exe-block-*` | Only if the author used them |

Two consequences worth stating:

- **A style must look finished with the minimum and with the maximum.** Test a box with
  title, icon and toggle, and a box with none of them.
- **Optional does not mean rare.** Author-written footer links, for instance, need a link
  colour that works on the footer surface, not on the page background.

### Author content is not white

Contrast has to be checked **against the surface the element actually sits on**, not
against the page. A link colour verified at 4.6:1 on white can fall under AA on a tinted
panel. Every tinted surface in the style is a place to re-check.

---

## 9. The application's JavaScript, and how it reaches into your CSS

The exported page runs jQuery plus the application's own scripts
(`contents/*/libs/`). Read them before assuming a structure — they are the specification
for anything you cannot see in the HTML.

### It reads colours out of your CSS and writes them back inline

Some components take their colour from the computed `color` of their container and then
write it into a `style=` attribute:

```js
var mainColor = $exeFX.timeline.getColor(e);   // the CSS color of .exe-timeline
var bg = $(e).css('color');                    // the CSS color of dl.exe-dl
```

**These are doors, not obstacles: set `color` on the container and the component
follows.** No `!important` needed. But the value is inherited by everything inside, so
**the prose within the component has to be given its colour back** — otherwise the body
text of a timeline event comes out in the accent colour.

Other components write inline styles you did not ask for — a container shadow tinted with
a child's background, a border colour taken from the container's own background (which
erases hairlines when the container is white). Those can only be contradicted with
`!important`. **Every `!important` must have a one-line comment naming the inline style it
is fighting.** If it has no such comment, it should not exist.

### It injects markup after load

Togglers, pagination, accordion sections, definition-list chips, and more. Consequences:

- The DOM you style is not the DOM in the file.
- Anything the JS builds is absent when JS is off. The page must still be readable.
- The application also adds `js` / `post-js` classes and `mode-teacher` on `<html>`.
  Teacher mode can also be forced with `?exe-teacher=1`.

### Useful globals

`$exe_i18n` holds the interface strings — `menu`, `search`, `more`, `mode_toggler`,
`teacher_mode`, `hide`, `download`, `block`, `toggleContent`. **Never hardcode interface
text: the export is translated.**

### If your own `style.js` runs at parse time

A script that must set a class on `<html>` before first paint (a stored theme choice, for
instance) runs while the head is still being parsed. At that moment **`<body>` does not
exist**, so the format classes of §7 cannot be read, and `localStorage.getItem` **throws**
in a sandboxed iframe or with cookies blocked — an unguarded call kills the rest of the
file and the page loses its menu, its breadcrumbs and everything else the script builds.

Guard the access, and let `init()` undo the class where the feature does not apply. The
parse-time call and `init()` have to stay in sync: change one, revisit the other.

---

## 10. Specificity, load order and the base stylesheets

The exported page loads, in this order:

```
bootstrap.min.css → idevice css → exe_effects.css → exe_atools.css → base.css → your style.css
```

Your stylesheet is last, so **equal specificity is enough to win** — no `!important` for
ordinary overrides.

Three traps that follow:

- **`base.css` beats `exe_effects.css`.** Rules in the effects stylesheet written with one
  class lose to `base.css` rules of the same specificity that come later. If a component
  looks wrong out of the box, check the cascade before blaming the component.
- **An `id` beats any class combination.** A rule like `#siteFooter a` cannot be overridden
  by `.exe-content a:hover`, so hover states silently never apply. When a selector uses an
  id, its states must be written with the same id.
- **Two of your own rules at equal specificity: the later one wins entirely, not
  per-value.** Where a single property carries two meanings at once — two markers packed
  into one `box-shadow`, say — a combined selector that declares both is the only fix.

`base.css` also claims `:hover` and `:focus` on content links at low specificity. When you
recolour a link, **list its states explicitly** or `base.css` keeps them.

---

## 11. Images

Everything in §3 applies to where they live. Two more things decide whether they work.

**Sprites are positioned in pixels.** A sprite is addressed with
`background-position: -Npx` at several `background-size` scales at once. **The cell grid
is a contract**: re-export at a different cell size and every icon in the style moves.
Fix the grid first, then draw.

**Flat ink and transparent backgrounds survive filters; nothing else does.** Icons drawn
as a single flat colour on transparency respond predictably to `filter`, so a dark mode,
a hover brightness or a recolour costs one rule instead of one patch per file. Gradients,
soft shadows and semi-transparency make every subsequent adjustment a special case.

**A `<img>` and a `background-image` are not equivalent under a filter.** A background
image is part of the element and takes every filter its ancestors carry; an `<img>` can be
targeted on its own. Choose deliberately when a filter is in play — and remember §5:
`mask-image` is not an option if the export may be opened from disk.

Deliver icons at 3× the size they are displayed at, and check the displayed size, not the
file.

---

## 12. Dark mode

Two implementations, and the choice is structural — make it early.

**Filter-based** — `filter: invert(…) hue-rotate(180deg)` on `html`, re-inverting specific
elements. Cheap to add, and it is a trap worth understanding before you accept it:

- **The round trip is not a mathematical identity.** `hue-rotate` pushes saturated colours
  out of gamut and the clipping is irreversible. Images, including author photographs and
  any logo, shift colour, and a precompensated file is impossible when the required source
  channel falls outside sRGB. Verify with numbers, not by eye.
- **Two regimes coexist, and confusing them is the easy mistake.** Outside a re-inverted
  element, **write the source colour**; the reader sees the filtered one. Inside a
  re-inverted element the two filters nearly cancel, so **write the colour you want to
  see**.
- **Light hairlines are the usual casualty.** A pale border comes back nearly black and an
  outlined control loses its outline entirely — often falling under the 3:1 that WCAG
  1.4.11 requires for a control's boundary. Check every border and every icon.
- **A `filter` applies to the element's whole subtree.** It cannot tone down a background
  without draining the text and icons with it, and a `grayscale()` on a parent cannot be
  undone by a child. Where you reach for a filter to calm one thing down, a literal colour
  on that one thing is usually the real fix.
- **Do not rely on `popover`/top-layer to escape an ancestor filter.** Chrome and Firefox
  disagree, and the difference is not feature-detectable from JS.

**Variable-based** — reassign the `:root` palette inside a `html.dark-mode` block. More
work up front, exact in every browser, leaves images alone, and it is the only one that
extends cleanly to `prefers-color-scheme`. **Prefer it for a new style.**

Either way, remember the toggle's stored preference is per origin: everything served from
`localhost` shares it. If a preview comes up dark unasked, clear the key rather than
hunting for a CSS bug.

---

## 13. Accessibility

Target: **WCAG 2.2 level AA**. Non-negotiable, and the reason several palette decisions
end up looking odd.

| What | Minimum |
| --- | --- |
| Body text and links | 4.5:1 against the surface behind them |
| Large text (≥ 24px, or ≥ 19px bold) | 3:1 |
| Icons, borders, focus rings, any non-text graphic that conveys meaning | 3:1 (1.4.11) |

Rules that follow, all of them learned the hard way:

- **Check against the real surface**, never against white by default. A colour that clears
  AA on `#ffffff` by a small margin will not clear it on a tinted panel, and tinted panels
  are everywhere: boxes, footer, message blocks.
- **Focus must be visible and must survive.** Removing an underline often removes the only
  focus indicator a control had, and the application's own stylesheets set `outline: none`
  in several places. If you remove one, add a real ring:
  `outline: 2px solid <accent>; outline-offset: 2px`, on `:focus-visible` so it does not
  appear on mouse clicks. A text underline alone does not satisfy 2.4.11. **`currentColor`
  is not safe for a ring**: on a filled control the text is often white, and a white ring
  on a white page is no ring.
- **Underlines are required where a link sits among prose** (1.4.1) and not required for
  standalone controls in a bar of their own, such as pagination chips.
- **Do not let colour be the only signal.** A 3px rule plus an icon is fine; a hue alone is
  not. Reusing one gesture — the same rule, the same chip — for "this is marked" keeps a
  style from reading as several unrelated components.
- **Disabled controls are exempt from contrast, not from being perceivable.**
  `opacity: .3` is not a good default.
- Keep the link colour consistent across contexts so a link is recognisable as one
  everywhere.

### Two layout traps with accessibility consequences

- **A float does not push the following block aside.** An element with `float: right`
  leaves the flow, but a following in-flow block still spans the full width underneath it,
  and in-flow content is hit-tested **above** floats. A `padding-right` on the block only
  shortens its line of text; the box still covers the float and swallows every click on
  it. Fix by positioning the floated element — measured in Chrome, `position: relative`
  **alone is not enough**, an explicit `z-index` is required. Give interactive controls a
  `z-index` above any decorative absolutely positioned neighbour: a switch must never lose
  a click to a line of text.
- **The best fix is often not a new colour.** Before adding one, check whether the problem
  is chroma rather than luminance. A callout that "shouts" may already be closer to white
  than its replacement; what shouts is saturation.

---

## 14. Before calling a change done

1. **All three formats**, not just the one you were looking at.
2. **Light and dark**, if the style has a dark mode.
3. **Both extremes of the optional elements** from §8.
4. **Narrow widths**, and `@media print` for the single page.
5. **Hard reload** (§4) — otherwise the check is worthless.
6. **Contrast measured, not eyeballed**, for anything whose colour changed.
7. **Served and from `file://`**, if anything touched images or masks.
8. **With JavaScript off**, if anything depended on injected markup.
9. Confirm the style does not leak into the eXeLearning editor interface — import the zip
   and look at the application, not only at the export.

State what you verified and what you did not.
