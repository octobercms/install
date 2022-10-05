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

        <!-- Base URL -->
        <?php if (!isset($fatalError)): ?>
            <script> var installerBaseUrl = '<?= $installer->getBaseUrl() ?>'; var installerPhpVersion = '<?= REQUIRED_PHP_VERSION ?>'; </script>
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
                    'lang',
                    'check',
                    'check/fail',
                    'config',
                    'config/config',
                    'config/sql',
                    'config/sqlite',
                    'config/fail',
                    'project',
                    'project/project',
                    'project/plugin',
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

            <!-- Render Lang -->
            <?php
                $langList = [
                    'en',
                    'de',
                    'fi',
                    'fr',
                    'hu',
                    'nl',
                    'pt-br',
                    'ru',
                    'zh-cn',
                ];
                $lastLangKey = end(array_keys($langList));
            ?>

            <script>
                var installerLang = {
                    <?php foreach ($langList as $key => $file): ?>
                        <?php $messages = include 'install_files/lang/'.$file.'.php'; ?>
                        "<?= $file ?>": <?= json_encode($messages) ?><?= $key === $lastLangKey ? '' : ',' ?>
                    <?php endforeach ?>
                };
            </script>

            <!-- Scripts -->
            <script src="install_files/js/vendor.js"></script>
            <script src="install_files/js/app.js"></script>
            <script src="install_files/js/lang.js"></script>
            <script src="install_files/js/check.js"></script>
            <script src="install_files/js/config.js"></script>
            <script src="install_files/js/project.js"></script>
            <script src="install_files/js/progress.js"></script>
            <script src="install_files/js/complete.js"></script>

        <?php endif ?>

    </body>
</html>
