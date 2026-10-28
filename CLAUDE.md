# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Two different jobs in one repository

Be explicit about which one you are doing, because the rules differ:

1. **Authoring a style** — editing `theme/`. This is what the tool exists for and what
   almost every request is about. **`AGENTS.md` is the authority for that work**: read it
   before touching `theme/style.css` or `theme/style.js`. Everything below is context, not
   a replacement for it.
2. **Maintaining the Style Designer application itself** — the PHP harness (`index.php`,
   `upload/`, `download/`, `files/`). Rare. `AGENTS.md` does not cover it; this file does.

`theme/`, `contents/` and `notes/` are in `.gitignore`: the first two are the designer's
working files and the third is the per-style rationale, none of them repository content. A
commit that adds them is a mistake.

## Agent skills live in `.agents/`, not `.claude/`

**This project keeps its agent skills in `.agents/skills/<name>/`**, alongside the root
`AGENTS.md`, and nothing the repository ships lives under `.claude/`. The reason is the same
one that put `AGENTS.md` at the root rather than folding it into this file: the knowledge in
those files is about eXeLearning, not about any one assistant, and a contributor using a
different tool should get it too. `.claude/` in a checkout holds only that developer's own
local settings, which git ignores.

⚠️ **Claude Code does not discover skills under `.agents/skills/`** — verified on 2.1.263,
where `/review-style` did not appear until a stub was added. So the repository ships exactly
one file under `.claude/`: `.claude/skills/review-style/SKILL.md`, a pointer whose only job
is to name the real path. It carries no instructions of its own.

That stub is the whole of the compatibility layer, and the rule for it is narrow: **do not
grow it**. If the skill is not being found, the fix is never to move the real files back or
to copy their content into the stub — that creates two sources of truth that drift. When a
future version reads `.agents/skills/` directly, delete the stub and nothing else.

## The review skill is part of the tool

`.agents/skills/review-style/` is versioned with the project, and deliberately so: it is a
feature of the Style Designer, documented in both READMEs, not a personal working note. It
covers job 1 only.

Invoke it for any request to review, audit, check or validate a style, and before building a
`.zip` to ship. It corrects `theme/` in place across eight phases and ends with a browser
pass over the three formats.

Three files, and none of them stands alone — `SKILL.md` loads the other two by name:

- `SKILL.md` — the procedure, and the boundary between what it fixes directly and what it
  must ask about first.
- `references/checks.md` — the check catalogue. This is the part not to improvise: it holds
  what is true about `base.css`, Bootstrap, `exe_effects.css` and the workarea's `main.css`,
  which is knowledge about eXeLearning's exports rather than about any one style.
- `assets/a11y-probe.js` — the contrast probe.

Neither `SKILL.md` nor `checks.md` names a specific style, and they should stay that way.
Findings about a particular style go in `notes/<style>.md`, which is not versioned.

## Target version: eXeLearning 4.0.6

The tool is released as **4.0.6**, matching the eXeLearning it targets, and the READMEs say
plainly that content exported by earlier versions is **not supported**. The previews are only
meaningful against 4.0.6 markup: the effects components read a focus colour from the style,
the teacher-mode switch exists, General Icons are Material Symbols, navigation goes
through the core's URL helper, and the website has a presentation mode
(exelearning/exelearning#2020) that clicks the style's own `#siteNavToggler` and prev/next
links. None of that is in an older export.

The eXeLearning **3** compatibility path was removed in 4.0.5: `upload/index.php` no
longer renames `default.js` / `content.css`, and the four strings it rewrites in the exported
HTML are now literals. **Do not reintroduce it.** A 3.x export loaded now simply fails to wire
up, which is the intended behaviour.

The bundled `files/fixtures/*.zip` report `exe_version 0.0.0-alpha`. That is the author's
local build carrying the changes that ship in 4.0.6, **not** an older export: it is the
right fixture, and the version string is not a defect.

## Running it

No build step, no test suite, no package manager. It is plain PHP 8 + Apache serving
static files.

```bash
docker-compose up --build     # http://localhost:8000/
```

Under XAMPP the checkout is served from its path in `htdocs`, e.g.
`http://localhost/exelearning/exelearning-style-designer/`.

Requires the `zip` PHP extension and generous `upload_max_filesize` / `post_max_size`
(see `custom-php.ini`). `upload/index.php` shells out to `rm -rf`, so a stock Windows
Apache cannot clear old contents — under XAMPP, delete `contents/` and `theme/` by hand
if a re-upload leaves stale files behind.

## The three PHP entry points

Everything the application does happens in three files.

**`index.php`** — the previewer. Renders a single `<iframe>` and three buttons that swap
its `src` between `contents/web`, `contents/page` and `contents/scorm`, appending a
timestamp as a cache buster, plus `&exe-presentation=1` on the website when the
**Presentation mode** switch is on (it is disabled for the other two). If `contents/` does not exist it shows the Start link
instead. `$defaultIndexFile` at the top switches the entry file for all three formats.

**`upload/index.php`** — the importer, and the only non-trivial logic in the project. It
takes three eXeLearning exports (`*_page.zip`, `*_scorm.zip`, `*_web.zip`, matched **by
filename suffix**), or the bundled `files/fixtures/*.zip` when posted with `?useFixtures=1`,
and then:

- wipes `contents/` and `theme/` and extracts each zip into `contents/{page,scorm,web}/`;
- copies `contents/web/theme/` up to the root as `theme/` — **the editable copy**;
- rewrites every exported `.html` so the `<link>` and `<script>` for the theme are written
  by `document.write()` pointing at **`../../../theme/style.css`** with a `Date.now()`
  cache buster — this rewrite is what makes editing the root `theme/` show up in all three
  previews at once;
- appends `files/js/style-designer.js` (currently an empty stub) to each page.

**`download/index.php`** — reads `<name>` from `theme/config.xml`, zips `theme/` with no
wrapping folder, and streams it. Also owns the "delete everything" action.

## The consequence that trips people up

Only `style.css` and `style.js` are read from the root `theme/`. Images referenced from
exported HTML (`theme/icons/*`) and images injected by `theme/style.js` (`<img src>`)
resolve inside **each** `contents/*/theme/` copy. `AGENTS.md` §3 has the full table; do not
re-derive it.

## Sample content and starting points

`files/fixtures/` holds the three sample exports plus two `.elpx` sources.
`files/example.zip` is a complete example style offered for download from every page.
`files/example_css_files/` holds three alternative palettes.

`contents/web/html/` (once imported) contains pages built to exercise specific cases —
`box-options.html`, `warnings-*.html`, `effects-*.html`, `highlighted-page.html`,
`udl-examples.html`. Use them rather than inventing markup.

## Style, when editing the application

Match what is there: 4-space indent, jQuery, Bootstrap 5 classes, English comments,
inline `<script>` blocks in the PHP files. The application is explicitly a local design
tool and is documented as unsafe for production — do not add auth, sanitising or hardening
unless asked; do not remove the existing validation either.
