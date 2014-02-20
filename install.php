<?php include 'install_files/php/boot.php'; ?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width">
        <title>October Installation</title>

        <!-- Styles -->
        <link href="install_files/css/vendor.css" rel="stylesheet">
        <link href="install_files/css/layout.css" rel="stylesheet">
        <link href="install_files/css/controls.css" rel="stylesheet">
        <link href="install_files/css/animations.css" rel="stylesheet">

        <script>
        <!--
            installerBaseUrl = '<?= $installer->getBaseUrl() ?>';
        // -->
        </script>
    </head>
    <body class="js">

        <!-- Header -->
        <header>
            <div class="container" id="containerHeader"></div>
        </header>

        <!-- Title -->
        <section class="title">
            <div class="container" id="containerTitle"></div>
        </section>

        <!-- Body -->
        <section class="body">
            <div class="container" id="containerBody"></div>
        </section>

        <!-- Footer -->
        <footer>
            <div class="container" id="containerFooter"></div>
        </footer>

        <!-- Render Partials -->
        <?php
            $partialList = array(
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
                'extras',
                'extras/suggestion',
                'extras/product',
                'progress',
                'progress/fail',
                'complete',
            );
        ?>

        <?php foreach($partialList as $file): ?>
            <script type="text/template" data-partial="<?= $file ?>">
                <?php include 'install_files/partials/'.$file.'.htm'; ?>
            </script>
        <?php endforeach ?>

        <!-- Scripts -->
        <script src="install_files/js/vendor.js"></script>
        <script src="install_files/js/app.js"></script>
        <script src="install_files/js/check.js"></script>
        <script src="install_files/js/config.js"></script>
        <script src="install_files/js/extras.js"></script>
        <script src="install_files/js/progress.js"></script>
        <script src="install_files/js/complete.js"></script>

        <!-- Bespoke Properties -->
        <script>
            /*
             * Checker Page
             */
            Installer.Pages.systemCheck.title = 'System Check'
            Installer.Pages.systemCheck.nextButton = 'Agree & Continue'

            Installer.Pages.systemCheck.requirements = [
                { code: 'liveConnection', label: 'Test connection to the installation server' },
                { code: 'writePermission', label: 'Permission to write to the installation directory' },
                { code: 'phpVersion', label: 'PHP version 5.4 or greater installed' },
                { code: 'safeMode', label: 'Safe mode PHP setting is not enabled' },
                { code: 'pdoLibrary', label: 'PDO PHP Extension is installed' },
                { code: 'curlLibrary', label: 'cURL PHP Extension is installed' },
                { code: 'mcryptLibrary', label: 'MCrypt PHP Extension is installed' },
                { code: 'zipLibrary', label: 'ZipArchive PHP Library is installed' }
            ]

            /*
             * Config Page
             */
            Installer.Pages.configForm.title = 'Configuration'
            Installer.Pages.configForm.nextButton = 'Continue'

            Installer.Pages.configForm.sections = [
                { code: 'database', label: 'Database', handler: 'onValidateDatabase', partial: 'config/database' },
                { code: 'admin', label: 'Administrator', handler: 'onValidateAdminAccount', partial: 'config/admin' },
                { code: 'advanced', label: 'Advanced', handler: 'onValidateAdvancedConfig', partial: 'config/advanced', isAdvanced: true }
            ]

            /*
             * Extras Page
             */
            Installer.Pages.installExtras.title = 'Extras'
            Installer.Pages.installExtras.nextButton = 'Install!'

            Installer.Pages.installExtras.includedPlugins = [
                // { code: 'October.Demo', name: 'Demo', author: 'October', description: 'Demonstration features.', image: 'http://placehold.it/100x100' },
                // { code: 'RainLab.Blog', name: 'Blog', author: 'October', description: 'A robust blogging platform.', image: 'http://placehold.it/100x100' }
            ]

            Installer.Pages.installExtras.suggestedProducts = [
                // { code: 'October.Demo', name: 'Demo', author: 'October', description: 'Demonstration features.', image: 'http://placehold.it/100x100' },
                // { code: 'October.Demo', name: 'Demo', author: 'October', description: 'Demonstration features.', image: 'http://placehold.it/100x100' },
                // { code: 'October.Demo', name: 'Demo', author: 'October', description: 'Demonstration features.', image: 'http://placehold.it/100x100' },
                // { code: 'RainLab.Blog', name: 'Blog', author: 'October', description: 'A robust blogging platform.', image: 'http://placehold.it/100x100' },
                // { code: 'October.Demo', name: 'Demo', author: 'October', description: 'Demonstration features.', image: 'http://placehold.it/100x100' },
                // { code: 'RainLab.Blog', name: 'Blog', author: 'October', description: 'A robust blogging platform.', image: 'http://placehold.it/100x100' },
                // { code: 'October.Demo', name: 'Demo', author: 'October', description: 'Demonstration features.', image: 'http://placehold.it/100x100' },
                // { code: 'RainLab.Blog', name: 'Blog', author: 'October', description: 'A robust blogging platform.', image: 'http://placehold.it/100x100' }
            ]

            /*
             * Progress Page
             */
            Installer.Pages.installProgress.title = 'Installing October...'
            Installer.Pages.installProgress.steps = [
                { code: 'getMetaData', label: 'Download file hashes' },
                { code: 'downloadCore', label: 'Download the core archive' },
                { code: 'downloadPlugins', label: 'Downloading plugin: ' },
                { code: 'extractCore', label: 'Extracting core' },
                { code: 'extractPlugins', label: 'Extracting plugin: ' },
                { code: 'setupConfig', label: 'Set up config files' },
                { code: 'createAdmin', label: 'Create admin account' },
                { code: 'finishInstall', label: 'Finalizing installation' }
            ]

            /*
             * Final Pages
             */
            Installer.Pages.installComplete.title = 'Congratulations!'

        </script>

    </body>
</html>