<?php include 'install_files/php/boot.php'; ?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width">
        <title><?= Lang::get('title') ?></title>

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
                Installer.Pages.systemCheck.title = '<?= Lang::get('system_check_title') ?>'
                Installer.Pages.systemCheck.nextButton = '<?= Lang::get('system_check_next') ?>'

                Installer.Pages.systemCheck.requirements = [
                    { code: 'phpVersion', label: '<?= Lang::get('system_check_php_version') ?>' },
                    { code: 'curlLibrary', label: '<?= Lang::get('system_check_required_curl') ?>' },
                    { code: 'liveConnection', label: '<?= Lang::get('system_check_test_connection') ?>' },
                    { code: 'writePermission', label: '<?= Lang::get('system_check_write_permission') ?>', reason: '<?= Lang::get('system_check_write_permission_reason') ?>' },
                    { code: 'pdoLibrary', label: '<?= Lang::get('system_check_required_pdo') ?>' },
                    { code: 'mcryptLibrary', label: '<?= Lang::get('system_check_required_mcrypt') ?>' },
                    { code: 'mbstringLibrary', label: '<?= Lang::get('system_check_required_mbstring') ?>' },
                    { code: 'sslLibrary', label: '<?= Lang::get('system_check_required_openssl') ?>' },
                    { code: 'zipLibrary', label: '<?= Lang::get('system_check_required_zip') ?>' },
                    { code: 'gdLibrary', label: '<?= Lang::get('system_check_required_gd') ?>' }
                ]

                /*
                 * Config Page
                 */
                Installer.Pages.configForm.title = '<?= Lang::get('config_form_title') ?>'
                Installer.Pages.configForm.nextButton = '<?= Lang::get('config_form_next') ?>'

                Installer.Pages.configForm.sections = [
                    { code: 'database', label: '<?= Lang::get('config_form_database_label') ?>', category: '<?= Lang::get('config_form_database_category') ?>', handler: 'onValidateDatabase', partial: 'config/database' },
                    { code: 'admin', label: '<?= Lang::get('config_form_admin_label') ?>', category: '<?= Lang::get('config_form_admin_category') ?>', handler: 'onValidateAdminAccount', partial: 'config/admin' },
                    { code: 'advanced', label: '<?= Lang::get('config_form_advanced_label') ?>', category: '<?= Lang::get('config_form_advanced_category') ?>', handler: 'onValidateAdvancedConfig', partial: 'config/advanced' }
                ]

                /*
                 * Starter Page
                 */
                Installer.Pages.starterForm.title = '<?= Lang::get('starter_form_title') ?>'

                /*
                 * Themes Page
                 */
                Installer.Pages.themesForm.title = '<?= Lang::get('themes_form_title') ?>'

                /*
                 * Project Page
                 */
                Installer.Pages.projectForm.title = '<?= Lang::get('project_form_title') ?>'
                Installer.Pages.projectForm.nextButton = '<?= Lang::get('project_form_next') ?>'

                Installer.Pages.projectForm.sections = [
                    { code: 'project', label: '<?= Lang::get('project_form_project') ?>', partial: 'project/project' },
                    { code: 'plugins', label: '<?= Lang::get('project_form_plugins') ?>', partial: 'project/plugins' },
                    { code: 'themes', label: '<?= Lang::get('project_form_themes') ?>', partial: 'project/themes' }
                ]

                /*
                 * Progress Page
                 */
                Installer.Pages.installProgress.title = '<?= Lang::get('install_progress_title') ?>'
                Installer.Pages.installProgress.steps = [
                    { code: 'getMetaData', label: '<?= Lang::get('install_progress_get_meta_data') ?>' },
                    { code: 'downloadCore', label: '<?= Lang::get('install_progress_download_core') ?>' },
                    { code: 'downloadPlugins', label: '<?= Lang::get('install_progress_download_plugins') ?>' },
                    { code: 'downloadThemes', label: '<?= Lang::get('install_progress_download_themes') ?>' },
                    { code: 'extractCore', label: '<?= Lang::get('install_progress_extract_core') ?>' },
                    { code: 'extractPlugins', label: '<?= Lang::get('install_progress_extract_plugins') ?>' },
                    { code: 'extractThemes', label: '<?= Lang::get('install_progress_extract_themes') ?>' },
                    { code: 'setupConfig', label: '<?= Lang::get('install_progress_setup_config') ?>' },
                    { code: 'createAdmin', label: '<?= Lang::get('install_progress_create_admin') ?>' },
                    { code: 'setupProject', label: '<?= Lang::get('install_progress_setup_project') ?>' },
                    { code: 'finishInstall', label: '<?= Lang::get('install_progress_finish_install') ?>' }
                ]

                /*
                 * Final Pages
                 */
                Installer.Pages.installComplete.title = '<?= Lang::get('install_complete_title') ?>'

            </script>

        <?php endif ?>

    </body>
</html>
