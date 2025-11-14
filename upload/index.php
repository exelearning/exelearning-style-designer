<?php
// Enable error reporting (optional for development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define paths
$rootDir = dirname(__DIR__);
$uploadDir = __DIR__;
$contentsDir = $rootDir . '/contents';
$themeTargetDir = $rootDir . '/theme';

$messages = [];  // Feedback messages to display in HTML

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_GET['useFixtures'])) {
        // Path to the fixtures
        $fixtureDir = realpath(__DIR__ . '/../files/fixtures');
        $fixtureFiles = [
            $fixtureDir . '/example_page.zip',
            $fixtureDir . '/example_scorm.zip',
            $fixtureDir . '/example_web.zip'
        ];

        // Simulate $_FILES structure
        $_FILES['zipFiles'] = [
            'name' => array_map('basename', $fixtureFiles),
            'type' => array_fill(0, 3, 'application/zip'),
            'tmp_name' => $fixtureFiles,
            'error' => array_fill(0, 3, UPLOAD_ERR_OK),
            'size' => array_map('filesize', $fixtureFiles)
        ];
    }

    $allowedSuffixes = ['_page.zip', '_scorm.zip', '_web.zip'];
    $folderNames = ['page', 'scorm', 'web'];
    $valid = true;

    // 1. Check POST size limit
    if (!empty($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > (int)ini_get('post_max_size') * 1024 * 1024) {
        $messages[] = ['type' => 'danger', 'text' => 'The uploaded files exceed the maximum size allowed by the server (post_max_size).'];
        $valid = false;
    }

    // 2. Check if any file was uploaded at all
    if (!isset($_FILES['zipFiles'])) {
        $messages[] = ['type' => 'danger', 'text' => 'No files were uploaded.'];
        $valid = false;
    } else {
        // Remove empty values (browsers may submit empty file inputs)
        $uploadedNames = array_filter($_FILES['zipFiles']['name']);
        if (count($uploadedNames) === 0) {
            $messages[] = ['type' => 'danger', 'text' => 'No files were uploaded.'];
            $valid = false;
        }
    }

    // 3. Ensure exactly 3 files are uploaded
    if ($valid && count($uploadedNames) !== 3) {
        $messages[] = ['type' => 'danger', 'text' => 'You must upload exactly 3 .zip files.'];
        $valid = false;
    }

    // 4. Ensure all uploaded files have .zip extension
    if ($valid) {
        foreach ($uploadedNames as $fileName) {
            if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'zip') {
                $messages[] = ['type' => 'danger', 'text' => 'Only .zip files are allowed. "' . $fileName . '" is not a zip file.'];
                $valid = false;
                break;
            }
        }
    }

    // 5. Ensure required suffixes exist (_page.zip, _scorm.zip, _web.zip)
    if ($valid) {
        $uploadedSuffixes = [];
        foreach ($uploadedNames as $fileName) {
            foreach ($allowedSuffixes as $suffix) {
                if (str_ends_with($fileName, $suffix)) {
                    $uploadedSuffixes[] = $suffix;
                }
            }
        }
        if (count(array_unique($uploadedSuffixes)) !== 3) {
            $messages[] = ['type' => 'danger', 'text' => 'File names must include suffixes: _page.zip, _scorm.zip, and _web.zip (each once).'];
            $valid = false;
        }
    }

    // 6. Only process files if all validations passed
    if ($valid) {

        // Delete existing /contents and /theme directories
        if (is_dir($contentsDir)) {
            exec("rm -rf " . escapeshellarg($contentsDir));
        }
        if (is_dir($themeTargetDir)) {
            exec("rm -rf " . escapeshellarg($themeTargetDir));
        }

        // Create new /contents folder only if it doesn't exist
        if (!is_dir($contentsDir)) {
            mkdir($contentsDir, 0777, true);
        }

        // Process and extract ZIP files
        foreach ($_FILES['zipFiles']['name'] as $index => $fileName) {
            $tmpPath = $_FILES['zipFiles']['tmp_name'][$index];
            $error = $_FILES['zipFiles']['error'][$index];

            if ($error !== UPLOAD_ERR_OK) {
                $messages[] = ['type' => 'danger', 'text' => 'Error uploading file: ' . $fileName];
                continue;
            }

            foreach ($allowedSuffixes as $key => $suffix) {
                if (str_ends_with($fileName, $suffix)) {
                    $targetFolder = $contentsDir . '/' . $folderNames[$key];

                    if (is_dir($targetFolder)) {
                        exec("rm -rf " . escapeshellarg($targetFolder));
                    }
                    // Create target folder only if it doesn't exist
                    if (!is_dir($targetFolder)) {
                        mkdir($targetFolder, 0777, true);
                    }

                    $zip = new ZipArchive();
                    if ($zip->open($tmpPath) === TRUE) {
                        $zip->extractTo($targetFolder);
                        $zip->close();
                        // Do NOT delete fixture ZIPs
                        if (strpos($tmpPath, 'fixtures/') === false && file_exists($tmpPath)) {
                            unlink($tmpPath);
                        }
                    } else {
                        $messages[] = ['type' => 'danger', 'text' => 'Failed to unzip file: ' . $fileName];
                    }
                }
            }
        }

        // Copy theme folder from /contents/web/theme to /theme
        function copyDir($src, $dst) {
            $dir = opendir($src);
            if (!is_dir($dst)) mkdir($dst, 0777, true);
            while (false !== ($file = readdir($dir))) {
                if ($file != '.' && $file != '..') {
                    $srcPath = $src . '/' . $file;
                    $dstPath = $dst . '/' . $file;
                    if (is_dir($srcPath)) {
                        copyDir($srcPath, $dstPath);
                    } else {
                        copy($srcPath, $dstPath);
                    }
                }
            }
            closedir($dir);
        }

        $webThemeDir = $contentsDir . '/web/theme';
        if (is_dir($webThemeDir)) {
            copyDir($webThemeDir, $themeTargetDir);
        }

        // eXeLearning v3.0.0 compatibility
        // Define target files
        $isV3 = false;
        $defaultJs = $themeTargetDir . '/default.js';
        $defaultCss = $themeTargetDir . '/content.css';
        $styleJs = $themeTargetDir . '/style.js';
        $styleCss = $themeTargetDir . '/style.css';
        // Rename files only if style.js and style.css do not already exist
        if (!file_exists($styleJs) && file_exists($defaultJs)) {
            rename($defaultJs, $styleJs);
            $isV3 = true;
        }
        if (!file_exists($styleCss) && file_exists($defaultCss)) {
            rename($defaultCss, $styleCss);
            $isV3 = true;
        }

        // Replace HTML script and CSS paths
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($contentsDir));
        foreach ($iterator as $file) {
            if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === 'html') {
                $content = file_get_contents($file);

                $styleJsFile = 'style';
                $styleCssFile = 'style';
                // eXeLearning v3.0.0 compatibility
                if ($isV3) {
                    $styleJsFile = 'default';
                    $styleCssFile = 'content';
                }

                $replacements = [
                    '<script src="../theme/'.$styleJsFile.'.js"> </script>' =>
                        '<script>document.write(\'<script src="../../../theme/style.js?v=\'+Date.now()+\'"><\/script>\');</script>',
                    '<link rel="stylesheet" href="../theme/'.$styleCssFile.'.css">' =>
                        '<script>document.write(\'<link rel="stylesheet" href="../../../theme/style.css?v=\'+Date.now()+\'">\');</script>',
                    '<script src="theme/'.$styleJsFile.'.js"> </script>' =>
                        '<script>document.write(\'<script src="../../theme/style.js?v=\'+Date.now()+\'"><\/script>\');</script>',
                    '<link rel="stylesheet" href="theme/'.$styleCssFile.'.css">' =>
                        '<script>document.write(\'<link rel="stylesheet" href="../../theme/style.css?v=\'+Date.now()+\'">\');</script>',
                ];

                $content = str_replace(array_keys($replacements), array_values($replacements), $content);

                if (basename($file) === 'index.html') {
                    $content = str_replace('</body>', '<script src="../../files/js/style-designer.js"></script></body>', $content);
                } else {
                    $content = str_replace('</body>', '<script src="../../../files/js/style-designer.js"></script></body>', $content);
                }

                file_put_contents($file, $content);
            }
        }

        // Add success message
        $messages[] = ['type' => 'success', 'text' => 'Processing completed successfully.'];
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta name="robots" content="noindex">
    <meta charset="utf-8">
    <title>Uploader | Style Designer</title>
    <link rel="icon" href="../files/favicon.ico">
    <link rel="stylesheet" href="../files/css/bootstrap.min.css">
    <script src="../files/js/jquery.min.js"></script>
    <script src="../files/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="../files/css/style-designer.css">
</head>
<body class="upload">
<div id="sdHeader">
    <span id="actionsPanel">
        <a href="../files/example.zip" class="btn btn-link btn-sm" id="exampleLnk" download>Example style</a>
    </span>
</div>
<div class="container p-4">
    <h1 class="h5 mb-4 visually-hidden">Upload .zip files</h1>

    <!-- Display error/success messages -->
    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $msg): ?>
            <div class="alert alert-<?php echo htmlspecialchars($msg['type']); ?>">
                <?php echo htmlspecialchars($msg['text']); ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Instructions -->
    <?php if (!isset($valid) || !$valid) { ?>
    <ol class="mt-4 mb-4 text-muted">
        <li>Open a content file (.elpx) with eXeLearning 3 and export it in these formats: Website, Single page, SCORM 1.2.</li>
        <li>Then select the <strong>3 .zip files</strong> and click on "Upload and process."</li>
        <li>Do not change the default names assigned by eXeLearning (they end with _page.zip, _scorm.zip, and _web.zip).</li>
    </ol>
    <!-- Upload form -->
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="zipFiles" class="form-label">Select 3 exported .zip files</label>
            <input type="file" class="form-control" name="zipFiles[]" id="zipFiles" multiple required>
            <div class="form-text">Files must end with _page.zip, _scorm.zip or _web.zip</div>
        </div>
        <button type="submit" class="btn btn-primary me-1">Upload and process</button>
        <button type="button" id="useFixturesBtn" class="btn btn-secondary">Use sample contents</button>
    </form>
    <?php } else { ?>
        <ol class="mt-4 mb-4 text-muted">
            <li>Open the "theme" folder at the root of your project.</li>
            <li>Click on "Style Designer" to see the different export types.</li>
            <li>Start editing (style.css), etc.</li>
        </ol>
        <p>
            <a href="../" class="btn btn-primary me-1">Style Designer</a>
            <a href="./" class="btn btn-secondary">Replace uploaded files</a>
        </p>
    <?php } ?>
</div>
<script>
    const zipFiles = document.getElementById('zipFiles');
    if (zipFiles) {
        zipFiles.addEventListener('change', function() {
            const files = Array.from(this.files);
            const requiredSuffixes = ['_page.zip', '_scorm.zip', '_web.zip'];
            let suffixesFound = [];
            const errorWrapper = $("#wrongFilesError");

            let errorMsg = ''
            files.forEach(file => {
                if (!file.name.endsWith('.zip')) {
                    errorMsg = 'Only .zip files are allowed.';
                    this.value = '';
                    return;
                }
                requiredSuffixes.forEach(suffix => {
                    if (file.name.endsWith(suffix)) suffixesFound.push(suffix);
                });
            });

            if (files.length !== 3 || new Set(suffixesFound).size !== 3) {
                if (errorMsg == '') errorMsg = 'You must select exactly 3 ZIP files with the correct suffixes (_page.zip, _scorm.zip, _web.zip).';
                this.value = '';
            }
            if (errorMsg != '') {
                if (errorWrapper.length == 0) {
                    $('form').before('<div id="wrongFilesError" class="alert alert-danger">' + errorMsg + '</div>');
                } else {
                    errorWrapper.html(errorMsg);
                }
            }
        });
    }
</script>
<script>
    const useFixturesBtn = document.getElementById('useFixturesBtn');
    if (useFixturesBtn) {
        useFixturesBtn.addEventListener('click', function() {
            $('ol').before('<p class="alert alert-info">This may take a few seconds...</p>').hide();
            $('form').hide();
            // Post with no files
            fetch(window.location.href + '?useFixtures=1', {
                method: 'POST'
            })
            .then(res => res.text())
            .then(html => {
                document.open();
                document.write(html);
                document.close();
            })
            .catch(err => alert('Error processing fixtures: ' + err));
        });
    }
</script>
</body>
</html>