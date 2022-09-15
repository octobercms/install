<?php

/**
 * InstallerHandlers
 */
trait InstallerHandlers
{
    /**
     * onCheckRequirement
     */
    protected function onCheckRequirement()
    {
        $checkCode = $this->post('code');
        $this->log('System check: %s', $checkCode);

        if ($checkCode === 'phpExtensions') {
            $subChecks = array_keys(array_filter([
                'mbstring' => extension_loaded('mbstring'),
                'fileinfo' => extension_loaded('fileinfo'),
                'openssl' => extension_loaded('openssl'),
                'gd' => extension_loaded('gd'),
                'filter' => extension_loaded('filter'),
                'hash' => extension_loaded('hash'),
                'pdo' => defined('PDO::ATTR_DRIVER_NAME'),
                'zip' => class_exists('ZipArchive'),
                'json' => function_exists('json_decode'),
                'curl' => function_exists('curl_init') && defined('CURLOPT_FOLLOWLOCATION'),
            ], function($v) { return !$v; }));
            $result = count($subChecks) === 0;
            $this->log('Requirement %s %s %s', $checkCode, print_r($subChecks, true), ($result ? '+OK' : '=FAIL'));
            return ['result' => $result, 'subChecks' => $subChecks];
        }

        $result = false;
        switch ($checkCode) {
            case 'phpVersion':
                $result = version_compare(trim(strtolower(PHP_VERSION)), REQUIRED_PHP_VERSION, '>=');
                break;
            case 'liveConnection':
                $result = ($this->requestServerData('ping') !== null);
                break;
            case 'writePermission':
                $result = is_writable(PATH_INSTALL) && is_writable($this->logFile);
                break;
        }

        $this->log('Requirement %s %s', $checkCode, ($result ? '+OK' : '=FAIL'));
        return ['result' => $result];
    }

    /**
     * onValidateDatabase
     */
    protected function onValidateConfig()
    {
        if ($this->post('db_type') !== 'sqlite' && !strlen($this->post('db_host'))) {
            throw new InstallerException('Please specify a database host', 'db_host');
        }

        if (!strlen($this->post('db_name'))) {
            throw new InstallerException('Please specify the database name', 'db_name');
        }

        // Check the database credentials
        $db = $this->checkDatabase(
            $type = $this->post('db_type'),
            $this->post('db_host'),
            $this->post('db_port'),
            $this->post('db_name'),
            $this->post('db_user'),
            $this->post('db_pass'),
        );

        // Check the database is empty
        $exptectedTablesAndViewsCount = 0;
        if ($type == 'sqlite') {
            $fetch = $db->query("select name from sqlite_master where type='table'", PDO::FETCH_NUM);
        }
        elseif ($type == 'pgsql') {
            $fetch = $db->query("select table_name from information_schema.tables where table_schema = 'public'", PDO::FETCH_NUM);
        }
        elseif ($type === 'sqlsrv') {
            $fetch = $db->query("select [table_name] from information_schema.tables", PDO::FETCH_NUM);
            $exptectedTablesAndViewsCount = 1;
        }
        else {
            $fetch = $db->query('show tables', PDO::FETCH_NUM);
        }

        $tables = 0;
        while ($fetch->fetch()) {
            $tables++;
        }

        if ($tables > $exptectedTablesAndViewsCount) {
            throw new Exception(sprintf('Database "%s" is not empty. Please empty the database or specify another database.', $this->e($name)));
        }
    }

    /**
     * onGetPopularPlugins
     */
    protected function onGetPopularPlugins()
    {
        return $this->requestServerData('plugin/popular');
    }

    /**
     * onGetPopularThemes
     */
    protected function onGetPopularThemes()
    {
        return $this->requestServerData('theme/popular');
    }

    /**
     * onSearchPlugins
     */
    protected function onSearchPlugins()
    {
        return $this->requestServerData('plugin/search', array('query' => $this->post('query')));
    }

    /**
     * onSearchThemes
     */
    protected function onSearchThemes()
    {
        return $this->requestServerData('theme/search', array('query' => $this->post('query')));
    }

    /**
     * onPluginDetails
     */
    protected function onPluginDetails()
    {
        return $this->requestServerData('plugin/detail', array('name' => $this->post('name')));
    }

    /**
     * onThemeDetails
     */
    protected function onThemeDetails()
    {
        return $this->requestServerData('theme/detail', array('name' => $this->post('name')));
    }

    /**
     * onProjectDetails
     */
    protected function onProjectDetails()
    {
        return $this->requestServerData('project/detail', array('id' => $this->post('project_id')));
    }

    /**
     * onInstallStep
     */
    protected function onInstallStep()
    {
        $installStep = $this->post('step');
        $this->log('Install step: %s', $installStep);
        $result = false;

        switch ($installStep) {
            case 'getMetaData':
                $params = array();

                $plugins = $this->post('plugins', array());
                $pluginCodes = array();
                foreach ($plugins as $plugin) {
                    if (isset($plugin['code'])) $pluginCodes[] = $plugin['code'];
                }
                $params['plugins'] = $pluginCodes;

                $themes = $this->post('themes', array());
                $themeCodes = array();
                foreach ($themes as $theme) {
                    if (isset($theme['code'])) $themeCodes[] = $theme['code'];
                }
                $params['themes'] = $themeCodes;

                if ($project = $this->post('project_id', false))
                    $params['project'] = $project;

                $result = $this->requestServerData('core/install', $params);
                break;

            case 'downloadCore':
                $hash = $this->getHashFromMeta('core');
                $this->requestServerFile('core', $hash, 'core/get', array('type' => 'install'));
                break;

            case 'downloadPlugin':
                $name = $this->post('name');
                if (!$name)
                    throw new Exception('Plugin download failed, missing name');

                $params = array('name' => $name);
                if ($project = $this->post('project_id', false))
                    $params['project'] = $project;

                $hash = $this->getHashFromMeta($name, 'plugin');
                $this->requestServerFile($name.'-plugin', $hash, 'plugin/get', $params);
                break;

            case 'downloadTheme':
                $name = $this->post('name');
                if (!$name)
                    throw new Exception('Theme download failed, missing name');

                $params = array('name' => $name);
                if ($project = $this->post('project_id', false))
                    $params['project'] = $project;

                $hash = $this->getHashFromMeta($name, 'theme');
                $this->requestServerFile($name.'-theme', $hash, 'theme/get', $params);
                break;

            case 'extractCore':
                $this->moveHtaccess(null, 'installer');

                $result = $this->unzipFile('core');
                if (!$result)
                    throw new Exception('Unable to open application archive file');

                if (!file_exists(PATH_INSTALL . '/index.php')
                        || !is_dir(PATH_INSTALL . '/modules')
                        || !is_dir(PATH_INSTALL . '/vendor'))
                    throw new Exception('Could not extract application files');

                $this->moveHtaccess(null, 'october');
                $this->moveHtaccess('installer', null);
                break;

            case 'extractPlugin':
                $name = $this->post('name');
                if (!$name)
                    throw new Exception('Plugin download failed, missing name');

                $result = $this->unzipFile($name.'-plugin', 'plugins/'.$this->octoberToFolderCode($name, false).'/');
                if (!$result)
                    throw new Exception('Unable to open plugin archive file');
                break;

            case 'extractTheme':
                $name = $this->post('name');
                if (!$name)
                    throw new Exception('Theme download failed, missing name');

                $result = $this->unzipFile($name.'-theme', 'themes/'.$this->octoberToFolderCode($name, true).'/');
                if (!$result)
                    throw new Exception('Unable to open theme archive file');
                break;

            case 'setupConfig':
                $this->buildConfigFile();
                break;

            case 'createAdmin':
                $this->createAdminAccount();
                break;

            case 'setupProject':
                $this->setProjectDetails();
                break;

            case 'finishInstall':
                $this->setCoreBuild();
                break;
            case 'cleanInstall':
                $this->moveHtaccess(null, 'installer');
                $this->moveHtaccess('october', null);
                $this->cleanUp();
                break;
        }

        if ($installStep != 'cleanInstall') { // skip cleanInstall step to prevent writing to nonexisting folder
            $this->log('Step %s +OK', $installStep);
        }

        return array('result' => $result);
    }

    protected function octoberToFolderCode($name, $isTheme)
    {
        if ($isTheme) {
            return str_replace('.', '-', strtolower($name));
        }

        return str_replace('.', '/', strtolower($name));
    }
}
