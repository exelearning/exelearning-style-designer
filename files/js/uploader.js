(() => {
    const SUFFIX_TO_VIEW = {
        '_web.zip': 'web',
        '_page.zip': 'page',
        '_scorm.zip': 'scorm'
    };

    const TEXT_EXTENSIONS = new Set([
        '.html', '.htm', '.css', '.js', '.json', '.txt', '.xml', '.xhtml', '.svg'
    ]);

    const messagesContainer = document.getElementById('messages');
    const uploadForm = document.getElementById('uploadForm');
    const fileInput = document.getElementById('zipFiles');

    const replacements = [
        {
            regex: /<script\s+src=["']\.\.\/theme\/style\.js["']>\s*<\/script>/gi,
            replacement: '<script>document.write(\'<script src="../../../theme/style.js?v=\'+Date.now()+\'"><\\/script>\');</script>'
        },
        {
            regex: /<link\s+rel=["']stylesheet["']\s+href=["']\.\.\/theme\/style\.css["']\s*\/?>/gi,
            replacement: '<script>document.write(\'<link rel="stylesheet" href="../../../theme/style.css?v=\'+Date.now()+\'">\');</script>'
        },
        {
            regex: /<script\s+src=["']theme\/style\.js["']>\s*<\/script>/gi,
            replacement: '<script>document.write(\'<script src="../../theme/style.js?v=\'+Date.now()+\'"><\\/script>\');</script>'
        },
        {
            regex: /<link\s+rel=["']stylesheet["']\s+href=["']theme\/style\.css["']\s*\/?>/gi,
            replacement: '<script>document.write(\'<link rel="stylesheet" href="../../theme/style.css?v=\'+Date.now()+\'">\');</script>'
        },
        {
            regex: /<link\s+rel=["']stylesheet["']\s+href=["']\.\.\/theme\/content\.css["']\s*\/?>/gi,
            replacement: '<script>document.write(\'<link rel="stylesheet" href="../../../theme/style.css?v=\'+Date.now()+\'">\');</script>'
        },
        {
            regex: /<link\s+rel=["']stylesheet["']\s+href=["']theme\/content\.css["']\s*\/?>/gi,
            replacement: '<script>document.write(\'<link rel="stylesheet" href="../../theme/style.css?v=\'+Date.now()+\'">\');</script>'
        }
    ];

    function clearMessages() {
        if (messagesContainer) {
            messagesContainer.innerHTML = '';
        }
    }

    function showMessage(type, text) {
        if (!messagesContainer) return;
        const div = document.createElement('div');
        div.className = `alert alert-${type}`;
        div.role = 'status';
        div.textContent = text;
        messagesContainer.appendChild(div);
    }

    function getExtension(name) {
        const match = name.toLowerCase().match(/\.[^.]+$/);
        return match ? match[0] : '';
    }

    function categorizeFiles(fileList) {
        const map = new Map();

        for (const file of fileList) {
            const fileName = file.name.toLowerCase();
            for (const suffix of Object.keys(SUFFIX_TO_VIEW)) {
                if (fileName.endsWith(suffix)) {
                    map.set(SUFFIX_TO_VIEW[suffix], file);
                    break;
                }
            }
        }

        return map;
    }

    function patchHtml(content, isIndex) {
        let result = content;
        for (const { regex, replacement } of replacements) {
            result = result.replace(regex, replacement);
        }

        const injection = isIndex
            ? '<script src="../../files/js/style-designer.js"></script>'
            : '<script src="../../../files/js/style-designer.js"></script>';

        if (!/files\/js\/style-designer\.js/.test(result)) {
            if (/<\/body>/i.test(result)) {
                result = result.replace(/<\/body>/i, `${injection}</body>`);
            } else {
                result += injection;
            }
        }

        return result;
    }

    function shouldTreatAsText(ext) {
        return TEXT_EXTENSIONS.has(ext);
    }

    async function processArchive(file, viewId, metadata) {
        const zip = await JSZip.loadAsync(file);
        const tasks = [];
        const basePath = `contents/${viewId}/`;

        zip.forEach((relativePath, zipEntry) => {
            if (zipEntry.dir) return;
            if (relativePath.startsWith('__MACOSX')) return;
            if (relativePath.includes('..')) return;

            const isThemeAsset = relativePath.startsWith('theme/');
            const themeRelativePath = isThemeAsset ? relativePath.slice('theme/'.length) : null;
            const normalizedThemePath = themeRelativePath === 'content.css'
                ? 'style.css'
                : themeRelativePath;
            const storageRelativePath = isThemeAsset ? `theme/${normalizedThemePath}` : relativePath;
            const destPath = basePath + storageRelativePath;
            tasks.push((async () => {
                const ext = getExtension(relativePath);
                const fileName = relativePath.split('/').pop() || '';
                let textContent = null;
                let binaryContent = null;

                if (ext === '.html') {
                    textContent = await zipEntry.async('string');
                    const patched = patchHtml(textContent, fileName === 'index.html');
                    await StyleStorage.saveText(destPath, patched);

                    if (viewId === 'web' && relativePath.startsWith('html/') && fileName !== 'index.html' && !metadata.defaultEntry) {
                        metadata.defaultEntry = fileName;
                    }
                } else if (shouldTreatAsText(ext)) {
                    textContent = await zipEntry.async('string');
                    await StyleStorage.saveText(destPath, textContent);
                } else {
                    binaryContent = await zipEntry.async('uint8array');
                    await StyleStorage.saveBinary(destPath, binaryContent);
                }

                if (isThemeAsset) {
                    if (shouldTreatAsText(ext)) {
                        const themeContent = textContent ?? await zipEntry.async('string');
                        // Compat: los paquetes antiguos usan theme/content.css; lo renombramos a theme/style.css para el editor.
                        await StyleStorage.saveText(`theme/${normalizedThemePath}`, themeContent);
                    } else {
                        const themeBinary = binaryContent ?? await zipEntry.async('uint8array');
                        await StyleStorage.saveBinary(`theme/${normalizedThemePath}`, themeBinary);
                    }
                }
            })());
        });

        await Promise.all(tasks);
    }

    async function handleSubmit(event) {
        event.preventDefault();
        clearMessages();

        if (!fileInput || !fileInput.files || !fileInput.files.length) {
            showMessage('danger', 'No files selected.');
            return;
        }

        const mapping = categorizeFiles(fileInput.files);
        const missingViews = Object.values(SUFFIX_TO_VIEW).filter((view) => !mapping.has(view));

        if (missingViews.length) {
            showMessage('danger', `Missing files for: ${missingViews.join(', ')}.`);
            return;
        }

        if (uploadForm) uploadForm.classList.add('is-processing');

        try {
            await StyleStorage.init();
            await StyleStorage.clear();

            const metadata = { defaultEntry: null };

            for (const view of Object.values(SUFFIX_TO_VIEW)) {
                const file = mapping.get(view);
                if (file) {
                    await processArchive(file, view, metadata);
                }
            }

            if (metadata.defaultEntry) {
                localStorage.setItem('style-designer-default-entry', metadata.defaultEntry);
            } else {
                localStorage.removeItem('style-designer-default-entry');
            }

            showMessage('success', 'Processing completed successfully.');
        } catch (err) {
            console.error(err);
            showMessage('danger', `Error processing files: ${err.message || err}`);
        } finally {
            if (uploadForm) uploadForm.classList.remove('is-processing');
            if (fileInput) fileInput.value = '';
        }
    }

    async function init() {
        if (typeof JSZip === 'undefined') {
            showMessage('danger', 'JSZip library is missing.');
            return;
        }

        try {
            await StyleStorage.init();
        } catch (err) {
            console.error(err);
            showMessage('danger', `Your browser does not support the required features. ${err && err.message ? err.message : ''}`);
            return;
        }

        if (uploadForm) {
            uploadForm.addEventListener('submit', handleSubmit);
        }
    }

    document.addEventListener('DOMContentLoaded', init, { once: true });
})();
