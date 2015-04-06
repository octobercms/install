<?php include 'install_files/php/boot.php'; ?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width">
        <title>October Installation</title>

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
                    'starter',
                    'themes',
                    'themes/theme',
                    'project',
                    'project/project',
                    'project/plugins',
                    'project/plugin',
                    'project/themes',
                    'project/theme',
                    'project/suggestion',
                    'project/fail',
                    'progress',
                    'progress/fail',
                    'complete',
                );
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
            <script src="install_files/js/starter.js"></script>
            <script src="install_files/js/themes.js"></script>
            <script src="install_files/js/project.js"></script>
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
                    { code: 'phpVersion', label: 'PHP version 5.4 or greater required' },
                    { code: 'curlLibrary', label: 'cURL PHP Extension is required' },
                    { code: 'liveConnection', label: 'Test connection to the installation server' },
                    { code: 'writePermission', label: 'Permission to write to directories and files', reason: 'The installer was unable to write to the installation directories and files.' },
                    { code: 'pdoLibrary', label: 'PDO PHP Extension is required' },
                    { code: 'mcryptLibrary', label: 'MCrypt PHP Extension is required' },
                    { code: 'mbstringLibrary', label: 'Mbstring PHP Extension is required' },
                    { code: 'sslLibrary', label: 'OpenSSL PHP Extension is required' },
                    { code: 'zipLibrary', label: 'ZipArchive PHP Library is required' },
                    { code: 'gdLibrary', label: 'GD PHP Library is required' }
                ]

                /*
                 * Config Page
                 */
                Installer.Pages.configForm.title = 'Configuration'
                Installer.Pages.configForm.nextButton = 'Continue'

                Installer.Pages.configForm.sections = [
                    { code: 'database', label: 'Database', category: 'General', handler: 'onValidateDatabase', partial: 'config/database' },
                    { code: 'admin', label: 'Administrator', category: 'General', handler: 'onValidateAdminAccount', partial: 'config/admin' },
                    { code: 'advanced', label: 'Advanced', category: 'Advanced', handler: 'onValidateAdvancedConfig', partial: 'config/advanced' }
                ]

                /*
                 * Starter Page
                 */
                Installer.Pages.starterForm.title = 'Getting started'

                /*
                 * Themes Page
                 */
                Installer.Pages.themesForm.title = 'Start from a theme'

                /*
                 * Project Page
                 */
                Installer.Pages.projectForm.title = 'Project details'
                Installer.Pages.projectForm.nextButton = 'Install!'

                Installer.Pages.projectForm.sections = [
                    { code: 'project', label: 'Project', partial: 'project/project' },
                    { code: 'plugins', label: 'Plugins', partial: 'project/plugins' },
                    { code: 'themes', label: 'Themes', partial: 'project/themes' }
                ]

                /*
                 * Progress Page
                 */
                Installer.Pages.installProgress.title = 'Installation progress...'
                Installer.Pages.installProgress.steps = [
                    { code: 'getMetaData', label: 'Requesting package information' },
                    { code: 'downloadCore', label: 'Downloading application files' },
                    { code: 'downloadPlugins', label: 'Downloading plugin: ' },
                    { code: 'downloadThemes', label: 'Downloading theme: ' },
                    { code: 'extractCore', label: 'Unpacking application files' },
                    { code: 'extractPlugins', label: 'Unpacking plugin: ' },
                    { code: 'extractThemes', label: 'Unpacking plugin: ' },
                    { code: 'setupConfig', label: 'Building configuration files' },
                    { code: 'createAdmin', label: 'Create admin account' },
                    { code: 'setupProject', label: 'Setting website project' },
                    { code: 'finishInstall', label: 'Finishing installation' }
                ]

                /*
                 * Final Pages
                 */
                Installer.Pages.installComplete.title = 'Congratulations!'

            </script>

        <?php endif ?>

    </body>
</html>
