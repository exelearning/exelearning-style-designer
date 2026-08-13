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

`theme/` and `contents/` are in `.gitignore`: they are the designer's working files, not
repository content. A commit that adds them is a mistake.

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
timestamp as a cache buster. If `contents/` does not exist it shows the Start link
instead. `$defaultIndexFile` at the top switches the entry file for all three formats.

**`upload/index.php`** — the importer, and the only non-trivial logic in the project. It
takes three eXeLearning exports (`*_page.zip`, `*_scorm.zip`, `*_web.zip`, matched **by
filename suffix**), or the bundled `files/fixtures/*.zip` when posted with `?useFixtures=1`,
and then:

- wipes `contents/` and `theme/` and extracts each zip into `contents/{page,scorm,web}/`;
- copies `contents/web/theme/` up to the root as `theme/` — **the editable copy**;
- renames eXeLearning 3's `default.js` / `content.css` to `style.js` / `style.css`;
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
