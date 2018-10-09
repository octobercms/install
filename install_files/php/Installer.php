<?php

class Installer
{
    /**
     * @var InstallerRewrite Configuration rewriter object.
     */
    protected $rewriter;

    /**
     * @var string Application base path.
     */
    protected $baseDirectory;

    /**
     * @var string A temporary working directory.
     */
    protected $tempDirectory;

    /**
     * @var string Expected path where configuration files can be found.
     */
    protected $configDirectory;

    /**
     * Constructor/Router
     */
    public function __construct()
    {
        $this->rewriter = new InstallerRewrite;

        /*
         * Establish directory paths
         */
        $this->baseDirectory = PATH_INSTALL;
        $this->tempDirectory = PATH_INSTALL . '/install_files/temp'; // @todo Use sys_get_temp_dir()
        $this->configDirectory = $this->baseDirectory . '/config';
        $this->logFile = PATH_INSTALL . '/install_files/install.log';
        $this->logPost();

        if (!is_null($handler = $this->post('handler'))) {
            if (!strlen($handler)) exit;

            try {
                if (!preg_match('/^on[A-Z]{1}[\w+]*$/', $handler))
                    throw new Exception(sprintf('Invalid handler: %s', $this->e($handler)));

                if (method_exists($this, $handler) && ($result = $this->$handler()) !== null) {
                    $this->log('Execute handler (%s): %s', $handler, print_r($result, true));
                    header('Content-Type: application/json');
                    die(json_encode($result));
                }
            }
            catch (Exception $ex) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
                $this->log('Handler error (%s): %s', $handler, $ex->getMessage());
                $this->log(array('Trace log:', '%s'), $ex->getTraceAsString());
                die($ex->getMessage());
            }

            exit;
        }
    }

    protected function onCheckRequirement()
    {
        $checkCode = $this->post('code');
        $this->log('System check: %s', $checkCode);

        $result = false;
        switch ($checkCode) {
            case 'liveConnection':
                $result = ($this->requestServerData('ping') !== null);
                break;
            case 'writePermission':
                $result = is_writable(PATH_INSTALL) && is_writable($this->logFile);
                break;
            case 'phpVersion':
                $result = PHP_VERSION_ID >= OCTOBER_MINIMUM_PHP_VERSION_ID;
                break;
            case 'pdoLibrary':
                $result = defined('PDO::ATTR_DRIVER_NAME');
                break;
            case 'mbstringLibrary':
                $result = extension_loaded('mbstring');
                break;
            case 'fileinfoLibrary':
                $result = extension_loaded('fileinfo');
                break;
            case 'sslLibrary':
                $result = extension_loaded('openssl');
                break;
            case 'gdLibrary':
                $result = extension_loaded('gd');
                break;
            case 'curlLibrary':
                $result = function_exists('curl_init') && defined('CURLOPT_FOLLOWLOCATION');
                break;
            case 'zipLibrary':
                $result = class_exists('ZipArchive');
                break;
        }

        $this->log('Requirement %s %s', $checkCode, ($result ? '+OK' : '=FAIL'));
        return array('result' => $result);
    }

    protected function onValidateDatabase()
    {
        if ($this->post('db_type') != 'sqlite' && !strlen($this->post('db_host')))
            throw new InstallerException('Please specify a database host', 'db_host');

        if (!strlen($this->post('db_name')))
            throw new InstallerException('Please specify the database name', 'db_name');

        $config = array_merge(array(
            'type' => null,
            'host' => null,
            'name' => null,
            'port' => null,
            'user' => null,
            'pass' => null,
        ), array(
            'type' => $this->post('db_type'),
            'host' => $this->post('db_host'),
            'name' => $this->post('db_name'),
            'user' => $this->post('db_user'),
            'pass' => $this->post('db_pass'),
            'port' => $this->post('db_port'),
        ));

        extract($config);

        switch ($type) {
            case 'mysql':
                $dsn = 'mysql:host='.$host.';dbname='.$name;
                if ($port) $dsn .= ";port=".$port;
                break;

            case 'pgsql':
                $_host = ($host) ? 'host='.$host.';' : '';
                $dsn = 'pgsql:'.$_host.'dbname='.$name;
                if ($port) $dsn .= ";port=".$port;
                break;

            case 'sqlite':
                $dsn = 'sqlite:'.$name;
                $this->validateSqliteFile($name);
                break;

            case 'sqlsrv':
                $availableDrivers = PDO::getAvailableDrivers();
                $_port = $port ? ','.$port : '';
                if (in_array('dblib', $availableDrivers)) {
                    $dsn = 'dblib:host='.$host.$_port.';dbname='.$name;
                }
                else {
                    $dsn = 'sqlsrv:Server='.$host.(empty($port) ? '':','.$_port).';Database='.$name;
                }
            break;
        }
        try {
            $db = new PDO($dsn, $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }
        catch (PDOException $ex) {
            throw new Exception('Connection failed: ' . $ex->getMessage());
        }

        /*
         * Check the database is empty
         */
        if ($type == 'sqlite') {
            $fetch = $db->query("select name from sqlite_master where type='table'", PDO::FETCH_NUM);
        }
        elseif ($type == 'pgsql') {
            $fetch = $db->query("select table_name from information_schema.tables where table_schema = 'public'", PDO::FETCH_NUM);
        }
        elseif ($type === 'sqlsrv') {
            $fetch = $db->query("select [table_name] from information_schema.tables", PDO::FETCH_NUM);
        }
        else {
            $fetch = $db->query('show tables', PDO::FETCH_NUM);
        }

        $tables = 0;
        while ($result = $fetch->fetch()) $tables++;

        if ($tables > 0) {
            throw new Exception(sprintf('Database "%s" is not empty. Please empty the database or specify another database.', $this->e($name)));
        }
    }

    protected function onValidateAdminAccount()
    {
        if (!strlen($this->post('admin_first_name')))
            throw new InstallerException('Please specify the administrator first name', 'admin_first_name');

        if (!strlen($this->post('admin_last_name')))
            throw new InstallerException('Please specify the administrator last name', 'admin_last_name');

        if (!strlen($this->post('admin_email')))
            throw new InstallerException('Please specify administrator email address', 'admin_email');

        if (!filter_var($this->post('admin_email'), FILTER_VALIDATE_EMAIL))
            throw new InstallerException('Please specify valid email address', 'admin_email');

        if (!strlen($this->post('admin_password')))
            throw new InstallerException('Please specify password', 'admin_password');

        if (strlen($this->post('admin_password')) < 4)
            throw new InstallerException('Please specify password length more than 4 characters', 'admin_password');

        if (strlen($this->post('admin_password')) > 255)
            throw new InstallerException('Please specify password length less than 64 characters', 'admin_password');

        if (!strlen($this->post('admin_confirm_password')))
            throw new InstallerException('Please confirm chosen password', 'admin_confirm_password');

        if (strcmp($this->post('admin_password'), $this->post('admin_confirm_password')))
            throw new InstallerException('Specified password does not match the confirmed password', 'admin_password');
    }

    protected function onValidateAdvancedConfig()
    {
        if (!strlen($this->post('encryption_code')))
            throw new InstallerException('Please specify encryption key', 'encryption_code');

        $validKeyLengths = [32];
        if (!in_array(strlen($this->post('encryption_code')), $validKeyLengths))
            throw new InstallerException('The encryption key should be of a valid length ('.implode(', ', $validKeyLengths).').', 'encryption_code');

        if (!strlen($this->post('folder_mask')))
            throw new InstallerException('Please specify folder permission mask', 'folder_mask');

        if (!strlen($this->post('file_mask')))
            throw new InstallerException('Please specify file permission mask', 'file_mask');

        if (!preg_match("/^[0-9]{3}$/", $this->post('folder_mask')) || $this->post('folder_mask') > 777)
            throw new InstallerException('Please specify a valid folder permission mask', 'folder_mask');

        if (!preg_match("/^[0-9]{3}$/", $this->post('file_mask')) || $this->post('file_mask') > 777)
            throw new InstallerException('Please specify a valid file permission mask', 'file_mask');
    }

    protected function onGetPopularPlugins()
    {
        return $this->requestServerData('plugin/popular');
    }

    protected function onGetPopularThemes()
    {
        return $this->requestServerData('theme/popular');
    }

    protected function onSearchPlugins()
    {
        return $this->requestServerData('plugin/search', array('query' => $this->post('query')));
    }

    protected function onSearchThemes()
    {
        return $this->requestServerData('theme/search', array('query' => $this->post('query')));
    }

    protected function onPluginDetails()
    {
        return $this->requestServerData('plugin/detail', array('name' => $this->post('name')));
    }

    protected function onThemeDetails()
    {
        return $this->requestServerData('theme/detail', array('name' => $this->post('name')));
    }

    protected function onProjectDetails()
    {
        return $this->requestServerData('project/detail', array('id' => $this->post('project_id')));
    }

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

                $result = $this->unzipFile($name.'-plugin', 'plugins/');
                if (!$result)
                    throw new Exception('Unable to open plugin archive file');
                break;

            case 'extractTheme':
                $name = $this->post('name');
                if (!$name)
                    throw new Exception('Theme download failed, missing name');

                $result = $this->unzipFile($name.'-theme', 'themes/');
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
                $this->moveHtaccess(null, 'installer');
                $this->moveHtaccess('october', null);
                $this->cleanUp();
                break;
        }

        $this->log('Step %s +OK', $installStep);

        return array('result' => $result);
    }

    //
    // Installation Steps
    //

    protected function buildConfigFile()
    {
        $this->bootFramework();

        $this->rewriter->toFile($this->configDirectory . '/app.php', array(
            'url'    => $this->getBaseUrl(),
            'locale' => 'en',
            'key'    => $this->post('encryption_code', 'CHANGE_ME!!!!!!!!!!!!!!!!!!!!!!!'),
        ));

        $activeTheme = $this->post('active_theme');
        if ($activeTheme) {
            $activeTheme = strtolower(str_replace('.', '-', $activeTheme));
        }
        else {
            $activeTheme = 'demo';
        }

        $this->rewriter->toFile($this->configDirectory . '/cms.php', array(
            'activeTheme' => $activeTheme,
            'backendUri'  => $this->post('backend_uri', '/backend'),
            'defaultMask.file' => $this->post('file_mask', '777'),
            'defaultMask.folder' => $this->post('folder_mask', '777'),
        ));

        $this->rewriter->toFile($this->configDirectory . '/database.php', $this->getDatabaseConfigValues());
    }

    protected function getDatabaseConfigValues()
    {
        $config = array_merge(array(
            'type' => null,
            'host' => null,
            'name' => null,
            'port' => null,
            'user' => null,
            'pass' => null,
            'prefix' => null,
        ), array(
            'type' => $this->post('db_type'),
            'host' => $this->post('db_host', ''),
            'name' => $this->post('db_name', ''),
            'port' => $this->post('db_port', ''),
            'user' => $this->post('db_user', ''),
            'pass' => $this->post('db_pass', ''),
            'prefix' => $this->post('db_prefix', ''),
        ));

        extract($config);

        switch ($type) {
            default:
            case 'mysql':
                $result = array(
                    'connections.mysql.host'     => $host,
                    'connections.mysql.port'     => empty($port) ? 3306 : $port,
                    'connections.mysql.database' => $name,
                    'connections.mysql.username' => $user,
                    'connections.mysql.password' => $pass,
                    'connections.mysql.prefix'   => $prefix,
                );
                break;

            case 'sqlite':
                $result = array(
                    'connections.sqlite.database' => $name,
                );
                break;

            case 'pgsql':
                $result = array(
                    'connections.pgsql.host'     => $host,
                    'connections.pgsql.port'     => empty($port) ? 5432 : $port,
                    'connections.pgsql.database' => $name,
                    'connections.pgsql.username' => $user,
                    'connections.pgsql.password' => $pass,
                    'connections.pgsql.prefix'   => $prefix,
                );
                break;

            case 'sqlsrv':
                $result = array(
                    'connections.sqlsrv.host'     => $host,
                    'connections.sqlsrv.port'     => empty($port) ? 1433 : $port,
                    'connections.sqlsrv.database' => $name,
                    'connections.sqlsrv.username' => $user,
                    'connections.sqlsrv.password' => $pass,
                    'connections.sqlsrv.prefix'   => $prefix,
                );
                break;
        }

        if (in_array($type, array('mysql', 'sqlite', 'pgsql', 'sqlsrv')))
            $result['default'] = $type;

        return $result;
    }

    protected function createAdminAccount()
    {
        $this->bootFramework();

        /*
         * Prepare admin seed defaults
         */
        $seeder = 'Backend\Database\Seeds\SeedSetupAdmin';
        $seederObj = new $seeder;
        $seederObj->setDefaults(array(
            'email' => $this->post('admin_email', 'admin@email.xxx'),
            'login' => $this->post('admin_login', 'admin'),
            'password' => $this->post('admin_password', 'admin'),
            'firstName' => $this->post('admin_first_name', 'Admin'),
            'lastName' => $this->post('admin_last_name', 'Person'),
        ));

        /*
         * Install application
         */
        $updater = call_user_func('System\Classes\UpdateManager::instance');
        $updater->update();
    }

    public function setProjectDetails()
    {
        if (!$projectId = $this->post('code'))
            return;

        $this->bootFramework();

        call_user_func('System\Models\Parameter::set', array(
            'system::project.id'    => $projectId,
            'system::project.name'  => $this->post('name'),
            'system::project.owner' => $this->post('owner'),
        ));
    }

    public function setCoreBuild()
    {
        $this->bootFramework();

        call_user_func('System\Models\Parameter::set', array(
            'system::core.hash'  => $this->post('uhash'),
            'system::core.build' => $this->post('build'),
        ));
    }

    //
    // File Management
    //

    protected function moveHtaccess($old = null, $new = null)
    {
        $oldFile = $this->baseDirectory . '/.htaccess';
        if ($old) $oldFile .= '.' . $old;

        $newFile = $this->baseDirectory . '/.htaccess';
        if ($new) $newFile .= '.' . $new;

        if (file_exists($oldFile))
            rename($oldFile, $newFile);
    }

    protected function unzipFile($fileCode, $directory = null)
    {
        $source = $this->getFilePath($fileCode);
        $destination = $this->baseDirectory;

        $this->log('Extracting file (%s): %s', $fileCode, basename($source));

        if ($directory)
            $destination .= '/' . $directory;

        if (!file_exists($destination))
            mkdir($destination, 0777, true); // @todo Use config

        $zip = new ZipArchive;
        if ($zip->open($source) === true) {
            $zip->extractTo($destination);
            $zip->close();
            return true;
        }

        return false;
    }

    protected function getFilePath($fileCode)
    {
        $name = md5($fileCode) . '.arc';
        return $this->tempDirectory . '/' . $name;
    }

    //
    // Logging
    //

    public function cleanLog()
    {
        $message = array(
            ".====================================================================.",
            "                                                                      ",
            " .d8888b.   .o8888b.   db  .d8888b.  d8888b. d88888b d8888b.  .d888b. ",
            ".8P    Y8. d8P    Y8   88 .8P    Y8. 88  `8D 88'     88  `8D .8P , Y8.",
            "88      88 8P      oooo88 88      88 88oooY' 88oooo  88oobY' 88  |  88",
            "88      88 8b      ~~~~88 88      88 88~~~b. 88~~~~  88`8b   88  |/ 88",
            "`8b    d8' Y8b    d8   88 `8b    d8' 88   8D 88.     88 `88. `8b | d8'",
            " `Y8888P'   `Y8888P'   YP  `Y8888P'  Y8888P' Y88888P 88   YD  `Y888P' ",
            "                                                                      ",
            "`========================== INSTALLATION LOG ========================'",
            "",
        );

        file_put_contents($this->logFile, implode(PHP_EOL, $message) . PHP_EOL);
    }

    public function logPost()
    {
        if (!isset($_POST) || !count($_POST)) return;
        $postData = $_POST;

        if (array_key_exists('disableLog', $postData))
            $postData = array('disableLog' => true);

        /*
         * Sensitive data fields
         */
        if (isset($postData['admin_email'])) $postData['admin_email'] = '*******@*****.com';
        $fieldsToErase = array(
            'encryption_code',
            'admin_password',
            'admin_confirm_password',
            'db_pass',
            'project_id',
        );
        foreach ($fieldsToErase as $field) {
            if (isset($postData[$field])) $postData[$field] = '*******';
        }

        file_put_contents($this->logFile, '.============================ POST REQUEST ==========================.' . PHP_EOL, FILE_APPEND);
        $this->log('Postback payload: %s', print_r($postData, true));
    }

    public function log()
    {
        $args = func_get_args();
        $message = array_shift($args);

        if (is_array($message))
            $message = implode(PHP_EOL, $message);

        $message = "[" . date("Y/m/d h:i:s", time()) . "] " . vsprintf($message, $args) . PHP_EOL;
        file_put_contents($this->logFile, $message, FILE_APPEND);
    }

    //
    // Helpers
    //

    protected function bootFramework()
    {
        $autoloadFile = $this->baseDirectory . '/bootstrap/autoload.php';
        if (!file_exists($autoloadFile))
            throw new Exception('Unable to find autoloader: ~/bootstrap/autoload.php');

        require $autoloadFile;

        $appFile = $this->baseDirectory . '/bootstrap/app.php';
        if (!file_exists($appFile))
            throw new Exception('Unable to find app loader: ~/bootstrap/app.php');

        $app = require_once $appFile;
        $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
        $kernel->bootstrap();
    }

    protected function requestServerData($uri = null, $params = array())
    {
        $result = null;
        $error = null;
        try {
            $curl = $this->prepareServerRequest($uri, $params);
            $result = curl_exec($curl);

            $this->log('Server request: %s', $uri);

            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($httpCode == 500) {
                $error = $result;
                $result = '';
            }

            $this->log('Request information: %s', print_r(curl_getinfo($curl), true));

            curl_close($curl);
        }
        catch (Exception $ex) {
            $this->log('Failed to get server data (ignored): ' . $ex->getMessage());
        }

        if ($error !== null)
            throw new Exception('Server responded with error: ' . $error);

        if (!$result || !strlen($result))
            throw new Exception('Server responded had no response.');

        try {
            $_result = @json_decode($result, true);
        }
        catch (Exception $ex) {}

        if (!is_array($_result)) {
            $this->log('Server response: '. $result);
            throw new Exception('Server returned an invalid response.');
        }

        return $_result;
    }

    protected function requestServerFile($fileCode, $expectedHash, $uri = null, $params = array())
    {
        $result = null;
        $error = null;
        try {
            if (!is_dir($this->tempDirectory)) {
                $tempDirectory = mkdir($this->tempDirectory, 0777, true); // @todo Use config
                if ($tempDirectory === false) {
                    $this->log('Failed to get create temporary directory: %s', $this->tempDirectory);
                    throw new Exception('Failed to get create temporary directory in ' . $this->tempDirectory . '. Please ensure this directory is writable.');
                }
            }

            $filePath = $this->getFilePath($fileCode);
            $stream = fopen($filePath, 'w');

            $curl = $this->prepareServerRequest($uri, $params);
            curl_setopt($curl, CURLOPT_FILE, $stream);
            curl_exec($curl);

            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($httpCode == 500) {
                $error = file_get_contents($filePath);
            }

            curl_close($curl);
            fclose($stream);
        }
        catch (Exception $ex) {
            $this->log('Failed to get server delivery: ' . $ex->getMessage());
            throw new Exception('Server failed to deliver the package');
        }

        if ($error !== null)
            throw new Exception('Server responded with error: ' . $error);

        $fileHash = md5_file($filePath);
        if ($expectedHash != $fileHash) {
            $this->log('File hash mismatch: %s (expected) vs %s (actual)', $expectedHash, $fileHash);
            $this->log('Local file size: %s', filesize($filePath));
            @unlink($filePath);
            throw new Exception('Package files from server are corrupt');
        }

        $this->log('Saving to file (%s): %s', $fileCode, $filePath);

        return true;
    }

    protected function prepareServerRequest($uri, $params = array())
    {
        $params['url'] = base64_encode($this->getBaseUrl());
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, OCTOBER_GATEWAY.'/'.$uri);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3600);
        // curl_setopt($curl, CURLOPT_FOLLOWLOCATION , true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));

        if (defined('OCTOBER_GATEWAY_AUTH')) {
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, OCTOBER_GATEWAY_AUTH);
        }

        return $curl;
    }

    protected function post($var, $default = null)
    {
        if (array_key_exists($var, $_REQUEST)) {
            $result = $_REQUEST[$var];
            if (is_string($result)) $result = trim($result);
            return $result;
        }

        return $default;
    }

    protected function getHashFromMeta($targetCode, $packageType = 'plugin')
    {
        $meta = $this->post('meta');
        $packageType .= 's';

        if ($targetCode == 'core')
            return (isset($meta['core']['hash'])) ? $meta['core']['hash'] : null;

        if (!isset($meta[$packageType]))
            return null;

        $collection = $meta[$packageType];
        if (!is_array($collection))
            return null;

        foreach ($collection as $code => $hash) {
            if ($code == $targetCode)
                return $hash;
        }

        return null;
    }

    public function getBaseUrl()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $baseUrl = !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
            $baseUrl .= '://'. $_SERVER['HTTP_HOST'];
            $baseUrl .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        }
        else {
            $baseUrl = 'http://localhost/';
        }

        return $baseUrl;
    }

    public function cleanUp()
    {
        $path = $this->tempDirectory;
        if (!file_exists($path))
            return;

        $d = dir($path);
        while (($entry = $d->read()) !== false) {
            $filePath = $path.'/'.$entry;

            if ($entry == '.' || $entry == '..' || $entry == '.htaccess' || is_dir($filePath))
                continue;

            $this->log('Cleaning up file: %s', $entry);
            @unlink($filePath);
        }

        $d->close();
    }

    public function e($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    protected function validateSqliteFile($filename)
    {
        if (file_exists($filename))
            return;

        $directory = dirname($filename);
        if (!is_dir($directory))
            mkdir($directory, 0777, true);

        new PDO('sqlite:'.$filename);
    }
}
