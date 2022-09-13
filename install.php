<?php include 'install_files/php/boot.php'; ?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width">
        <title>October CMS Installation</title>
        <link type="image/png" href="install_files/images/october.png" rel="icon">

        <!-- Styles -->
        <link href="install_files/css/vendor.css" rel="stylesheet">
        <link href="install_files/css/layout.css" rel="stylesheet">
        <link href="install_files/css/controls.css" rel="stylesheet">
        <link href="install_files/css/animations.css" rel="stylesheet">
        <link href="install_files/css/fonts.css" rel="stylesheet">

        <!-- Base URL -->
        <?php if (!isset($fatalError)): ?>
            <script>
            <!--
                installerBaseUrl = '<?= $installer->getBaseUrl() ?>';
            // -->
            </script>
        <?php endif ?>
    </head>
    <body class="js">

        <div id="wrap">

            <!-- Header -->
            <header>
                <div class="container" id="containerHeader"></div>

                <!-- Title -->
                <section class="title">
                    <div class="container" id="containerTitle"></div>
                </section>

            </header>

            <!-- Body -->
            <section class="body">
                <?php if (isset($fatalError)): ?>
                    <div class="container">
                        <div class="callout callout-danger"><?= $fatalError ?></div>
                    </div>
                <?php else: ?>
                    <div class="container" id="containerBody"></div>
                <?php endif ?>
            </section>

        </div>

        <!-- Footer -->
        <footer>
            <div class="container" id="containerFooter"></div>
        </footer>

        <?php if (!isset($fatalError)): ?>

            <!-- Render Partials -->
            <?php
                $partialList = [
                    'header',
                    'title',
                    'footer',
                    'check',
                    'check/fail',
                    'config',
                    'config/mysql',
                    'config/pgsql',
                    'config/sqlite',
                    'config/sqlsrv',
                    'config/fail',
                    'config/database',
                    'config/admin',
                    'config/advanced',
                    'project',
                    'project/project',
                    'project/plugins',
                    'project/plugin',
                    'project/themes',
                    'project/theme',
                    'project/fail',
                    'progress',
                    'progress/fail',
                    'complete'
                ];
            ?>

            <?php foreach ($partialList as $file): ?>
                <script type="text/template" data-partial="<?= $file ?>">
                    <?php include 'install_files/partials/'.$file.'.htm'; ?>
                </script>
            <?php endforeach ?>

            <!-- Scripts -->
            <script src="install_files/js/vendor.js"></script>
            <script src="install_files/js/app.js"></script>
            <script src="install_files/js/check.js"></script>
            <script src="install_files/js/config.js"></script>
            <script src="install_files/js/project.js"></script>
            <script src="install_files/js/progress.js"></script>
            <script src="install_files/js/complete.js"></script>

        <?php endif ?>

    </body>
</html>
