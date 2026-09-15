[Versión en español](README_es.md)

# eXeLearning Style Designer

**Version:** 4.0.5 · **License:** AGPL

## Description

eXeLearning Style Designer is a tool designed to facilitate the creation of custom styles for **eXeLearning 4.0.5**.

With this tool, you can easily edit and customize the look and feel of your eXeLearning projects by creating new styles and packaging them for distribution.

---

## Important Notices

ℹ️ **Intended audience notice:**

This tool is **not intended for non-technical users**. It is designed for web designers and developers. To use it properly, you should be able to install and configure a PHP project and work comfortably with CSS (and preferably JavaScript).

⚠️ **This application must never be used in production.**

It does not include the necessary security measures. This tool is intended solely for designers to simplify the creation of styles for eXeLearning 4.

⚠️ **Version compatibility: eXeLearning 4.0.5.**

This release is built for **eXeLearning 4.0.5**, and that is the version it is recommended for. The three exports you load must be produced by 4.0.5 — **content exported with an earlier version is not supported**.

The reason is that the Designer previews your style against the real exported markup, and 4.0.5 changed it: the focus colour the effects components read from the style, the teacher-mode switch, the General Icons drawn as Material Symbols, and the URL helper the navigation uses. A style checked against an older export can look finished and still be wrong in 4.0.5.

---

## Installation

### 1. Direct Installation on Apache

You can run the application directly on a local Apache server:

1. Make sure you have **PHP 8+** and Apache installed.
2. Copy the project files to your Apache document root (e.g., `htdocs` or `www`).
3. Ensure the `path-to-project/` folder is accessible via your browser.
4. Adjust PHP settings if needed for large file uploads:

```ini
upload_max_filesize = 50M
post_max_size = 50M
memory_limit = 128M
```

5. Open your browser and visit:

```
http://localhost/path-to-project/index.php
```

---

### 2. Docker

This is the easiest way to run the application without installing PHP or Apache locally.

1. Make sure **Docker Desktop** is installed and running.
2. Clone or download this repository.
3. Navigate to the project root in a terminal.
4. Build and start the container:

```bash
docker-compose up --build
```

5. Open your browser and visit:

```
http://localhost:8000/
```

> You can change the port mapping in `docker-compose.yml` if needed.

---

## How to Use — Quick Start Guide

### 1. Getting Started

1. Download and install the tool.
2. Click **Start** to begin.

You will see two options:

- **Upload three exported contents** in ZIP format:
  - A website
  - A single page
  - A SCORM 1.2 package
- **Use the sample contents** (the easiest way to start)

If you choose to upload your own exports, they must come from **eXeLearning 4.0.5** (see the compatibility notice above), and you should make sure **all export preferences** (page counter, search box, etc.) are enabled. This way, your exports will include all optional elements, allowing you to ensure your style is fully compatible with them. You should also include all kinds of box combinations: with and without icon, with and without title, without the minimize button, etc. This will let you verify that your style works properly in all cases.

---

### 2. Loading the Designer

Once the contents are loaded, you can open the **Style Designer**.

At the top of the page, you will find three preview buttons:

- **Website**
- **Single page**
- **SCORM 1.2**

Click each one to see how your style will look in that export format.

---

### 3. Editing Your Style

All editable files are located in the **theme** folder inside the application directory.

You can modify:

- `style.css`
- `style.js`
- Any other files required by your design

#### Images
Place all required images in `/theme/img`.
If you add `favicon.png` or `favicon.ico` in this folder, exports will use it instead of the default eXeLearning favicon.

#### Fonts
You can add custom fonts to `/theme/fonts`.

#### iDevice Icons
The folder `/theme/icons` contains the default iDevice icons.
It is recommended to work with these during development and replace them with your own collection once the style is complete.

#### Example CSS Files
The folder `/files/example_css_files` contains several CSS files similar to the one used by the included style. They may serve as a useful starting point for your design.

---

### 4. Finalizing Your Style

When you're ready, click **Finish / Restart** and follow the instructions:

1. Edit your style’s `config.xml` file.
2. Download your style as a ZIP file.
3. Import the ZIP into eXeLearning.

If you want to start from scratch, go to **Finish / Restart** and select **Delete all files and create a new Style**.

---

### 5. Testing and Licensing

- Test your style in eXeLearning to ensure everything works correctly and your CSS does not interfere with the application’s presentation.
- Respect all third-party licenses for images, fonts, or any other assets you use.
- Add any required attributions or legal information directly into your style’s `config.xml`.

---

### 6. Reviewing Your Style Automatically

The Designer ships an **agent skill** that reviews a style, in `.agents/skills/review-style/`.
It is part of the tool, not a personal working note: it is versioned with the project and
available to anyone who clones it.

It follows the `SKILL.md` convention and sits in the vendor-neutral `.agents/` folder rather
than under any one assistant's own directory, for the same reason `AGENTS.md` sits at the
root: what it knows is about eXeLearning, not about the assistant reading it. It was written
and tested with [Claude Code](https://claude.com/claude-code).

Claude Code only auto-discovers skills under `.claude/skills/`, so the repository also ships
a single pointer file, `.claude/skills/review-style/SKILL.md`, that does nothing but name the
real path. That is the only file here that assumes a particular assistant. With any other
tool, point it at `.agents/skills/review-style/SKILL.md` directly.

Ask it to *review*, *audit* or *check* the style and it audits `theme/` against the
three exports in `contents/`, in eight phases: inventory, static review of the CSS and the
JavaScript, typography, the third-party credits in `config.xml`, responsive behaviour, and a
verification pass in a real browser over all three export formats. It asks which WCAG level
to apply on every run — AA is the default, and the one `AGENTS.md` requires.

**The output is not a report: it is a corrected `theme/`.** The skill applies the fixes to
`theme/config.xml`, `theme/style.css` and `theme/style.js`, and reports what it did.

It corrects a style; it does not redesign one. Dead code, invalid CSS, broken selectors,
JavaScript that throws, missing focus states and minimal contrast nudges are applied
directly. Anything a designer would *notice* as a design change — a different colour, a
different font, new spacing, a component added or removed — is proposed and never applied
without asking.

Two files carry the knowledge, and both are worth reading on their own:

- `references/checks.md` — the check catalogue: how `base.css`, Bootstrap and
  `exe_effects.css` interact with a style's specificity, what the three export formats do
  differently, how filter-based dark mode behaves, and what changes inside the eXeLearning
  editor.
- `assets/a11y-probe.js` — the contrast probe it runs in the browser.

It needs the previews to be served over HTTP (Apache or Docker, as above) and a browser it
can drive. A review with `contents/` missing is worthless, so load the sample contents first.

---

### 7. Additional Resources

- Click **Example style** in the application to download an example style.
- eXeLearning styles official documentation: https://exelearning.github.io/exelearning/development/styles/

---

## Notes

- The tool is intended for **designers only**.
- It is **not secure** for production use.
- When you have finished, it is important to **test your Style within eXeLearning** to ensure that its CSS code does not affect the application interface.

---

## License

This project is licensed under the **AGPL**. You may freely use, modify, and distribute it under the terms of the Affero General Public License.

---

## Credits / Attributions

The following third-party resources are included in this project:

- **Example content:** `files/fixtures/leer-para-aprender.elpx`
  Used to create screenshots of Styles in eXeLearning 4.
  Original content created for [CEDEC](https://cedec.intef.es/).
  License: [Creative Commons BY-SA](https://creativecommons.org/licenses/by-sa/3.0/)

- **Icon:** `files/img/new-window.svg`
  Source: [Google Fonts Icons](https://fonts.google.com/icons)
  License: [Apache License 2.0](https://www.apache.org/licenses/LICENSE-2.0)

- **Bootstrap JavaScript:** `files/js/bootstrap.bundle.min.js` and `bootstrap.bundle.min.js.map`
  Source: [Bootstrap](https://getbootstrap.com/)
  License: [MIT License](https://github.com/twbs/bootstrap/blob/main/LICENSE)

- **jQuery:** `files/js/jquery.min.js`
  Source: [jQuery](https://jquery.com/)
  License: [MIT License](https://jquery.org/license/)

- **Bootstrap CSS:** `files/css/bootstrap.min.css` and `bootstrap.min.css.map`
  Source: [Bootstrap](https://getbootstrap.com/)
  License: [MIT License](https://github.com/twbs/bootstrap/blob/main/LICENSE)

- **eXeLearning Logo:**
  Source: [eXeLearning GitHub](https://github.com/exelearning/exelearning/)
  License: AGPL (same as this project)

- **Open Sans Font:** `files/example.zip/fonts/*`
  Copyright 2020 The Open Sans Project Authors
  Source: [Open Sans on GitHub](https://github.com/googlefonts/opensans)
  License: [SIL Open Font License, Version 1.1](https://openfontlicense.org/) — the full text
  ships inside the zip, as `fonts/OFL.txt`

> All other files in this project were created by [@ignaciogros](https://github.com/ignaciogros) and are licensed under AGPL.
