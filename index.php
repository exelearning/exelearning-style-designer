<?php 
    $defaultIndexFile = 'index.html';
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="robots" content="noindex">
        <meta charset="utf-8">
        <title>Style Designer</title>
        <link rel="icon" href="files/favicon.ico">
        <link rel="stylesheet" href="files/css/bootstrap.min.css">
        <script src="files/js/jquery.min.js"></script>
        <script src="files/js/bootstrap.bundle.min.js" defer></script>
        <link rel="stylesheet" href="files/css/style-designer.css">
        <script>
            var sd = {
                init : function(){
                    this.viewer = $("#viewer");
                    $('.btn-group .btn').on('click', function(){
                        if (this.id == 'page' || '<?php echo $defaultIndexFile; ?>' == 'index.html') sd.viewer.attr('src', 'contents/' + this.id + '/index.html?v='+ new Date());
                        else sd.viewer.attr('src', 'contents/' + this.id + '/html/<?php echo $defaultIndexFile; ?>?v='+ new Date());
                    });
                    $("button#_blank").on('click', function(){
                        window.open(sd.viewer.attr('src'));
                    });
                }
            }
            $(function(){
                sd.init();
            });
        </script>
    </head>
    <body class="index">
        <div id="sdHeader">
            <?php if (is_dir('contents')) { ?>
            <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                <button type="button" class="btn btn-light" id="web">Website</button>
                <button type="button" class="btn btn-light" id="page">Single page</button>
                <button type="button" class="btn btn-light" id="scorm">SCORM 1.2</button>
            </div>
            <?php } ?>
            <span id="actionsPanel">
                <?php if (is_dir('contents')) { ?>
                    <button type="button" class="btn btn-text btn-sm" id="_blank" title="Open in new window"><img src="files/img/new-window.svg" width="24" height="24" alt="New window"><span class="visually-hidden">New window</span></button>
                    <a href="./download" class="btn btn-primary btn-sm mw me-2" id="done">Finish / Restart</a>
                <?php } ?>
                <a href="files/example.zip" class="btn btn-link btn-sm" id="exampleLnk" download>Example style</a>
            </span>
        </div>
        <?php if (is_dir('contents')) { ?>
            <?php
                $url = 'contents/web/';
                if ($defaultIndexFile != 'index.html') $url .= 'html/';
                $url .= $defaultIndexFile;
            ?>
            <?php if (file_exists($url)) { ?>
            <iframe width="800" height="600" src="<?php echo $url; ?>?v=<?php echo date("YmdHis"); ?>" id="viewer"></iframe>
            <?php } else { ?>
            <div class="container p-4">
                <h1 class="visually-hidden">Style Designer</h1>
                <div class="alert alert-warning">
                    The "contents" folder exists, but the required HTML is not found.
                </div>
                <p>Click on "Done" to delete all previous files and follow the instructions.</p>
            </div>
            <?php } ?>
        <?php } else { ?>
        <div class="container p-4">
            <h1 class="visually-hidden">Style Designer</h1>
            <div class="alert alert-info">
                No contents available… Just click on "Start" and follow the instructions.
            </div>
            <p></p>
            <p><a href="./upload" class="btn btn-primary mw" id="upload" class="">Start</a></p>
        </div>
        <?php } ?>
    </body>
</html>