<?php
if (isset($_POST['confirmDeleteAction'])) {
    // Function to recursively delete a folder and its contents
    function deleteDirectory($dir) {
        if (!file_exists($dir)) return;

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                @rmdir($item->getRealPath());
            } else {
                @unlink($item->getRealPath());
            }
        }

        @rmdir($dir);
    }

    // Paths to theme and contents folders
    $basePath = realpath(__DIR__ . '/..');
    $themePath = $basePath . '/theme';
    $contentsPath = $basePath . '/contents';

    // Delete both folders if they exist
    deleteDirectory($themePath);
    deleteDirectory($contentsPath);

    // Redirect to index.php located at the same level
    header("Location: ../");
    exit;
}
?>
<?php
if (isset($_POST['download'])) {
    // Absolute path to the theme folder (relative to this script)
    $themeFolder = realpath(__DIR__ . '/../theme');

    if (!file_exists($themeFolder)) {
        die('Error: Theme folder does not exist.');
    }

    // Path to the XML file containing the theme name
    $configFile = $themeFolder . '/config.xml';

    if (!file_exists($configFile)) {
        die('Error: config.xml not found in the theme folder.');
    }

    // Read <name> from config.xml
    $xml = simplexml_load_file($configFile);
    $zipName = (string) $xml->name . '.zip';

    // Temporary path to create the ZIP
    $zipPath = __DIR__ . '/' . $zipName;

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        die('Error: Cannot create ZIP file.');
    }

    // Add files recursively
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($themeFolder, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($files as $file) {
        $filePath = $file->getRealPath();
        // Correct relative path inside ZIP
        $relativePath = str_replace('\\', '/', substr($filePath, strlen($themeFolder) + 1));

        if ($file->isDir()) {
            if (!empty($relativePath)) { // avoid adding empty root
                $zip->addEmptyDir($relativePath);
            }
        } else {
            $zip->addFile($filePath, $relativePath);
        }
    }

    $zip->close();

    // Force download
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zipName) . '"');
    header('Content-Length: ' . filesize($zipPath));
    readfile($zipPath);

    // Delete temporary ZIP
    unlink($zipPath);
    exit;
}
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="robots" content="noindex">
        <meta charset="utf-8">
        <title>Finish / Restart | Style Designer</title>
        <link rel="icon" href="../files/favicon.ico">
        <link rel="stylesheet" href="../files/css/bootstrap.min.css">
        <script src="../files/js/jquery.min.js"></script>
        <script src="../files/js/bootstrap.bundle.min.js" defer></script>
        <link rel="stylesheet" href="../files/css/style-designer.css">
    </head>
    <body class="finish">
        <div id="sdHeader">
            <span id="actionsPanel">
                <a href="../files/example.zip" class="btn btn-link btn-sm" id="exampleLnk" download>Example style</a>
            </span>
        </div>
        <div class="container p-4">
            <h1 class="h5 mb-4 visually-hidden">Download style</h1>
            <ol class="mt-4 mb-4 text-muted">
                <li>Edit the <strong>config.xml</strong> file before downloading the style.</li>
                <li>Remember that "name" is an ID and cannot contain spaces or special characters.</li>
                <li>If your work is based on another style, you must respect the license terms.</li>
                <li>You can add any additional information about licenses, authorship, or anything else you need in the "description" field.</li>
                <li>If you want to customize the favicon for exports, you can include a "favicon.ico" or "favicon.png" file in the "img" folder of your style.</li>
                <li>When you're finished, click "Download Style".</li>
            </ol>
            <form method="post">
                <button type="submit" name="download" class="btn btn-primary me-1">Download Style</button>
                <a href="../" class="btn btn-outline-primary me-1">Go back to the Style Designer</a>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#confirmModal">Delete all files and create a new Style</button>
            </form>
            <form id="deleteForm" method="post" style="display:none;">
                <input type="hidden" name="confirmDeleteAction" value="1">
            </form>
        </div>
        <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="h5 modal-title" id="confirmModalLabel">Are you sure?</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>If you click "Continue", <strong>all files will be permanently deleted</strong>, including the uploaded content and the Style you’ve been working on. <strong>This action cannot be undone.</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmDeleteAction">Continue</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $('#confirmDeleteAction').on('click', function(){
                document.getElementById('deleteForm').submit();
            });
        </script>
</body>
</html>
