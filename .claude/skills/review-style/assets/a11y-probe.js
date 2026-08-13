/*
 * a11y-probe — measures one exported eXeLearning page in the browser.
 *
 * Run through mcp__claude-in-chrome__javascript_tool on a *served* page (never file://).
 * Returns JSON. Set the level first; AA is the default:
 *     window.__A11Y_LEVEL = 'AAA';
 *
 * It measures what static reading cannot: the real surface behind each text run, the
 * layout at the current viewport, and whether a control shows a focus indicator.
 * Everything it reports is a measurement — the judgement is still yours. Read the
 * `caveats` field before treating a number as a verdict.
 */
(function () {
    var LEVEL = window.__A11Y_LEVEL || 'AA';
    var CAP = 40; // per-list cap, to keep the payload readable

    /* ---------- colour ---------- */

    function parseColor(str) {
        if (!str) return null;
        if (str === 'transparent') return { r: 0, g: 0, b: 0, a: 0 };
        var m = str.match(/^rgba?\(([^)]+)\)$/);
        if (!m) return null; // color(srgb …), lab(), gradients: not handled
        var p = m[1].split(/[,\/]+/).map(function (v) { return parseFloat(v.trim()); });
        if (p.length < 3 || p.some(isNaN)) return null;
        return { r: p[0], g: p[1], b: p[2], a: p.length > 3 ? p[3] : 1 };
    }

    function over(fg, bg) { // composite fg (with alpha) onto opaque bg
        var a = fg.a;
        return {
            r: fg.r * a + bg.r * (1 - a),
            g: fg.g * a + bg.g * (1 - a),
            b: fg.b * a + bg.b * (1 - a),
            a: 1
        };
    }

    function luminance(c) {
        var ch = [c.r, c.g, c.b].map(function (v) {
            v = v / 255;
            return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
        });
        return 0.2126 * ch[0] + 0.7152 * ch[1] + 0.0722 * ch[2];
    }

    function ratio(a, b) {
        var l1 = luminance(a), l2 = luminance(b);
        var hi = Math.max(l1, l2), lo = Math.min(l1, l2);
        return Math.round(((hi + 0.05) / (lo + 0.05)) * 100) / 100;
    }

    function hex(c) {
        function h(v) { return ('0' + Math.round(v).toString(16)).slice(-2); }
        return '#' + h(c.r) + h(c.g) + h(c.b);
    }

    /* ---------- the surface an element actually sits on ---------- */

    // Climbs ancestors compositing translucent backgrounds until it reaches an opaque one.
    // Returns null when an ancestor paints an image or gradient: the value under the text
    // is then unknowable from the CSSOM and must be judged from a screenshot.
    function surfaceOf(el) {
        var stack = [];
        var node = el;
        while (node && node.nodeType === 1) {
            var cs = getComputedStyle(node);
            if (cs.backgroundImage && cs.backgroundImage !== 'none') {
                return { color: null, imageAt: pathOf(node), image: cs.backgroundImage.slice(0, 80) };
            }
            var bg = parseColor(cs.backgroundColor);
            if (bg && bg.a > 0) {
                stack.push(bg);
                if (bg.a === 1) break;
            }
            node = node.parentElement;
        }
        var base = { r: 255, g: 255, b: 255, a: 1 }; // canvas default
        for (var i = stack.length - 1; i >= 0; i--) base = over(stack[i], base);
        return { color: base, imageAt: null };
    }

    function pathOf(el) {
        var parts = [];
        var node = el;
        for (var d = 0; node && node.nodeType === 1 && d < 3; d++) {
            var s = node.tagName.toLowerCase();
            if (node.id) { parts.unshift(s + '#' + node.id); break; }
            if (node.className && typeof node.className === 'string') {
                s += '.' + node.className.trim().split(/\s+/).slice(0, 2).join('.');
            }
            parts.unshift(s);
            node = node.parentElement;
        }
        return parts.join(' > ');
    }

    function visible(el) {
        var cs = getComputedStyle(el);
        if (cs.display === 'none' || cs.visibility === 'hidden' || parseFloat(cs.opacity) === 0) return false;
        var r = el.getBoundingClientRect();
        if (r.width < 1 || r.height < 1) return false;
        // the clip-rect hidden-text convention: present for screen readers, not painted
        if (cs.clip && cs.clip !== 'auto' && /rect\(\s*(0|1)px/.test(cs.clip)) return false;
        if (cs.clipPath && cs.clipPath.indexOf('inset(50%') === 0) return false;
        return true;
    }

    function hasOwnText(el) {
        for (var i = 0; i < el.childNodes.length; i++) {
            var n = el.childNodes[i];
            if (n.nodeType === 3 && n.nodeValue.trim().length > 1) return true;
        }
        return false;
    }

    /* ---------- 1. text contrast ---------- */

    var thresholds = LEVEL === 'AAA'
        ? { normal: 7, large: 4.5 }
        : LEVEL === 'A' ? { normal: 0, large: 0 } : { normal: 4.5, large: 3 };

    var textFail = [], unknownSurface = [], seen = {};

    Array.prototype.forEach.call(document.querySelectorAll('body *'), function (el) {
        if (!hasOwnText(el) || !visible(el)) return;
        var cs = getComputedStyle(el);
        var fg = parseColor(cs.color);
        if (!fg) return;
        var surf = surfaceOf(el);
        var size = parseFloat(cs.fontSize);
        var weight = parseInt(cs.fontWeight, 10) || 400;
        var large = size >= 24 || (size >= 18.66 && weight >= 700);
        var need = large ? thresholds.large : thresholds.normal;
        if (!need) return;

        if (!surf.color) {
            var uk = pathOf(el) + '|' + surf.imageAt;
            if (!seen['u' + uk] && unknownSurface.length < CAP) {
                seen['u' + uk] = 1;
                unknownSurface.push({
                    element: pathOf(el), color: cs.color,
                    behind: surf.imageAt, image: surf.image,
                    note: 'background image or gradient — judge from a screenshot'
                });
            }
            return;
        }

        var text = fg.a < 1 ? over(fg, surf.color) : fg;
        var r = ratio(text, surf.color);
        if (r >= need) return;

        var key = hex(text) + hex(surf.color) + large;
        if (seen[key]) { seen[key].count++; return; }
        var entry = {
            element: pathOf(el),
            sample: (el.textContent || '').trim().slice(0, 40),
            color: hex(text), surface: hex(surf.color),
            fontSize: size + 'px', weight: weight, large: large,
            ratio: r, required: need, count: 1
        };
        seen[key] = entry;
        if (textFail.length < CAP) textFail.push(entry);
    });

    /* ---------- 2. optional elements present on this page (AGENTS.md §8) ---------- */

    var optional = [
        '.package-subtitle', '.page-counter', '#teacher-mode-toggler-wrapper',
        '#packageLicense', '#siteUserFooter', '.box-toggle', '.box-icon',
        '.box.no-header', '#siteNav', '.nav-buttons', '#siteLogos',
        '.exe-attachment-link', '.exe-fx', '.exe-dl', '[class*="exe-block-"]',
        '.box', '#exe-client-search', '#darkModeToggler', '#made-with-eXe'
    ];
    var presence = {};
    optional.forEach(function (sel) {
        try { presence[sel] = document.querySelectorAll(sel).length; }
        catch (e) { presence[sel] = 'bad selector'; }
    });

    /* ---------- 3. focus indicator (indicative — see caveats) ---------- */

    var focusable = document.querySelectorAll(
        'a[href], button, input:not([type=hidden]), select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    var noFocusRing = [], sx = window.scrollX, sy = window.scrollY;
    var active = document.activeElement;

    Array.prototype.slice.call(focusable, 0, 120).forEach(function (el) {
        if (!visible(el) || noFocusRing.length >= CAP) return;
        var before = getComputedStyle(el);
        var b = [before.outlineStyle, before.outlineWidth, before.boxShadow,
                 before.borderColor, before.backgroundColor, before.textDecorationLine].join('|');
        try { el.focus({ preventScroll: true }); } catch (e) { return; }
        var after = getComputedStyle(el);
        var a = [after.outlineStyle, after.outlineWidth, after.boxShadow,
                 after.borderColor, after.backgroundColor, after.textDecorationLine].join('|');
        if (a !== b) return; // something changed: it has an indicator of some kind
        noFocusRing.push({
            element: pathOf(el),
            text: (el.textContent || el.value || el.getAttribute('aria-label') || '').trim().slice(0, 30),
            outline: after.outlineStyle + ' ' + after.outlineWidth
        });
    });
    if (active && active.focus) { try { active.focus({ preventScroll: true }); } catch (e) {} }
    window.scrollTo(sx, sy);

    /* ---------- 4. accessible names on controls ---------- */

    var unnamed = [];
    Array.prototype.forEach.call(document.querySelectorAll('button, a[href], input[type=submit], input[type=button]'), function (el) {
        if (unnamed.length >= CAP) return;
        var name = (el.getAttribute('aria-label') || el.getAttribute('title') ||
                    el.value || el.textContent || '').trim();
        if (!name && el.getAttribute('aria-labelledby')) {
            var ref = document.getElementById(el.getAttribute('aria-labelledby'));
            if (ref) name = (ref.textContent || '').trim();
        }
        if (!name) {
            var img = el.querySelector('img[alt]');
            if (img) name = img.alt.trim();
        }
        if (!name) unnamed.push({ element: pathOf(el), html: el.outerHTML.slice(0, 90) });
    });

    /* ---------- 5. touch targets (WCAG 2.5.8, AA in 2.2) ---------- */

    var small = [];
    Array.prototype.forEach.call(document.querySelectorAll('a[href], button, input, select, [role=button]'), function (el) {
        if (!visible(el) || small.length >= CAP) return;
        var r = el.getBoundingClientRect();
        if (r.width >= 24 && r.height >= 24) return;
        var cs = getComputedStyle(el);
        if (cs.display === 'inline' && el.tagName === 'A') return; // inline prose link: exempt
        small.push({
            element: pathOf(el),
            size: Math.round(r.width) + 'x' + Math.round(r.height),
            text: (el.textContent || '').trim().slice(0, 25)
        });
    });

    /* ---------- 6. layout at this viewport ---------- */

    var overflow = [];
    var docW = document.documentElement.clientWidth;
    if (document.documentElement.scrollWidth > docW + 1) {
        Array.prototype.forEach.call(document.querySelectorAll('body *'), function (el) {
            if (overflow.length >= 15) return;
            var r = el.getBoundingClientRect();
            if (r.width === 0) return;
            if (r.right > docW + 1 || r.left < -1) {
                var cs = getComputedStyle(el);
                if (cs.position === 'fixed' && r.left < -1) return; // deliberately parked off-screen
                overflow.push({
                    element: pathOf(el),
                    left: Math.round(r.left), right: Math.round(r.right),
                    overhang: Math.round(r.right - docW)
                });
            }
        });
    }

    /* ---------- 7. cheap structural checks ---------- */

    var ids = {}, duplicateIds = [];
    Array.prototype.forEach.call(document.querySelectorAll('[id]'), function (el) {
        if (ids[el.id]) { if (duplicateIds.indexOf(el.id) < 0) duplicateIds.push(el.id); }
        ids[el.id] = 1;
    });

    var imgNoAlt = [];
    Array.prototype.forEach.call(document.querySelectorAll('img:not([alt])'), function (el) {
        if (imgNoAlt.length < CAP) imgNoAlt.push({ src: (el.getAttribute('src') || '').slice(-50), at: pathOf(el) });
    });

    var headings = [], last = 0, headingJumps = [];
    Array.prototype.forEach.call(document.querySelectorAll('h1,h2,h3,h4,h5,h6'), function (el) {
        if (!visible(el)) return;
        var lvl = parseInt(el.tagName[1], 10);
        headings.push(lvl);
        if (last && lvl > last + 1 && headingJumps.length < 10) {
            headingJumps.push({ from: 'h' + last, to: 'h' + lvl, text: (el.textContent || '').trim().slice(0, 40) });
        }
        last = lvl;
    });

    // fonts actually resolved for body prose
    var probe = document.querySelector('.exe-content p, .exe-content li, .exe-content') || document.body;
    var probeCS = getComputedStyle(probe);

    return {
        meta: {
            url: location.href,
            level: LEVEL,
            viewport: window.innerWidth + 'x' + window.innerHeight,
            devicePixelRatio: window.devicePixelRatio,
            htmlClass: document.documentElement.className,
            bodyClass: document.body.className,
            darkMode: /exe-dark-mode|dark/.test(document.documentElement.className),
            jsInjected: /(^|\s)(js|post-js)(\s|$)/.test(document.documentElement.className),
            bodyFont: probeCS.fontFamily,
            bodyFontSize: probeCS.fontSize,
            bodyLineHeight: probeCS.lineHeight,
            fontsLoaded: document.fonts ? document.fonts.status : 'unknown'
        },
        textContrastFailures: textFail.sort(function (a, b) { return a.ratio - b.ratio; }),
        unknownSurface: unknownSurface,
        presence: presence,
        noVisibleFocusChange: noFocusRing,
        controlsWithoutName: unnamed,
        smallTouchTargets: small,
        horizontalOverflow: overflow,
        duplicateIds: duplicateIds,
        imagesWithoutAlt: imgNoAlt,
        headingLevelJumps: headingJumps,
        caveats: [
            'Only rgb()/rgba() computed colours are parsed; gradients and background images are reported under unknownSurface instead of guessed.',
            'noVisibleFocusChange uses programmatic focus(), which does not always match :focus-visible after a real Tab. Confirm the survivors with the keyboard.',
            'Non-text contrast (1.4.11: borders, icons, focus rings) is NOT measured here — check it from screenshots.',
            'Counts are per rendered page. Run on several pages: box-options.html and udl-examples.html exercise the most variants.'
        ]
    };
})();
