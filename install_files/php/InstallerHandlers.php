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
                'extension:mbstring' => extension_loaded('mbstring'),
                'extension:fileinfo' => extension_loaded('fileinfo'),
                'extension:openssl' => extension_loaded('openssl'),
                'extension:gd' => extension_loaded('gd'),
                'extension:filter' => extension_loaded('filter'),
                'extension:hash' => extension_loaded('hash'),
                'extension:pdo' => defined('PDO::ATTR_DRIVER_NAME'),
                'extension:zip' => class_exists('ZipArchive'),
                'extension:json' => function_exists('json_decode'),
                'extension:curl' => function_exists('curl_init') && defined('CURLOPT_FOLLOWLOCATION'),
                'memory_limit:128M' => !$this->checkMemoryLimit(128),
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
            $name = $this->post('db_name'),
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
     * onProjectDetails
     */
    protected function onProjectDetails()
    {
        // Validate input with gateway
        try {
            $result = $this->requestServerData('project/detail', ['id' => $this->post('project_id')]);
        }
        catch (Exception $ex) {
            throw new Exception("The supplied license key could not be found. Please visit octobercms.com to obtain a license.");
        }

        // Check project status
        $isActive = $result['is_active'] ?? false;
        if (!$isActive) {
            throw new Exception("License is unpaid or has expired. Please visit octobercms.com to obtain a license.");
        }

        return $result;
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
                $params = [];

                if ($project = $this->post('project_id', false)) {
                    $params['project'] = $project;
                }

                $result = $this->requestServerData('install/detail', $params);
                break;

            case 'downloadCore':
                $hash = $this->getHashFromMeta('core');
                $this->requestServerFile('core', $hash, 'install/download');
                break;

            case 'extractCore':
                $this->moveHtaccess(null, 'installer');

                $result = $this->unzipFile('core');
                if (!$result) {
                    throw new Exception('Unable to open application archive file');
                }

                if (
                    !file_exists(PATH_INSTALL . '/index.php') ||
                    !is_dir(PATH_INSTALL . '/modules') ||
                    !is_dir(PATH_INSTALL . '/vendor')
                ) {
                    throw new Exception('Could not extract application files');
                }

                $this->moveHtaccess(null, 'october');
                $this->moveHtaccess('installer', null);
                break;

            case 'setupConfig':
                $this->bootFramework();
                $this->buildConfigFile();
                $this->flushOpCache();
                $this->refreshEnvVars();
                break;

            case 'setupProject':
                $this->bootFramework();
                $this->setProjectDetails();
                break;

            case 'composerUpdate':
                $this->bootFramework();
                $this->runComposerUpdate();
                break;

            case 'composerInstall':
                $this->bootFramework();
                $this->runComposerInstall();
                break;

            case 'migrateDatabase':
                $this->bootFramework();
                $this->migrateDatabase();
                break;

            case 'cleanInstall':
                $this->moveHtaccess(null, 'installer');
                $this->moveHtaccess('october', null);
                $this->cleanUp();
                break;
        }

        // Skip cleanInstall step to prevent writing to nonexisting folder
        if ($installStep !== 'cleanInstall') {
            $this->log('Step %s +OK', $installStep);
        }

        return ['result' => $result];
    }
}
