<?php

class Installer
{
    /**
     * @var Illuminate\Foundation\Application Framework application object, when booted.
     */
    protected $app;

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
        $this->configDirectory = $this->baseDirectory . '/app/config';
        $this->logFile = PATH_INSTALL . '/install_files/install.log';

        if (!is_null($handler = $this->post('handler'))) {
            if (!strlen($handler)) exit;

            try {
                $this->log('Execute AJAX handler: %s', $handler);

                if (!preg_match('/^on[A-Z]{1}[\w+]*$/', $handler))
                    throw new Exception(sprintf('Invalid handler: %s', $handler));

                if (method_exists($this, $handler) && ($result = $this->$handler()) !== null) {
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
                $result = is_writable(PATH_INSTALL);
                break;
            case 'phpVersion':
                $result = version_compare(PHP_VERSION , "5.4", ">=");
                break;
            case 'safeMode':
                $result = !ini_get('safe_mode');
                break;
            case 'pdoLibrary':
                $result = defined('PDO::ATTR_DRIVER_NAME');
                break;
            case 'mcryptLibrary':
                $result = extension_loaded('mcrypt');
                break;
            case 'gdLibrary':
                $result = extension_loaded('gd');
                break;
            case 'curlLibrary':
                $result = function_exists('curl_init');
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
                break;

            case 'sqlsrv':
                $availableDrivers = PDO::getAvailableDrivers();
                $_port = $port ? ','.$port : '';
                if (in_array('dblib', $availableDrivers))
                    $dsn = 'dblib:host='.$host.$_port.';dbname='.$name;
                else {
                    $_name = ($name != '') ? ';Database='.$name : '';
                    $dsn = 'dblib:host='.$host.$_port.$_name;
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
        if ($type == 'sqlite')
            $fetch = $db->query("select name from sqlite_master where type='table'", PDO::FETCH_NUM);
        else
            $fetch = $db->query('show tables', PDO::FETCH_NUM);

        $tables = 0;
        while ($result = $fetch->fetch()) $tables++;

        if ($tables > 0)
            throw new Exception(sprintf('Database "%s" is not empty. Please empty the database or specify another database.', $name));
    }

    protected function onValidateAdminAccount()
    {
        if (!strlen($this->post('admin_first_name')))
            throw new InstallerException('Please specify the administrator first name', 'admin_first_name');

        if (!strlen($this->post('admin_last_name')))
            throw new InstallerException('Please specify the administrator first name', 'admin_last_name');

        if (!strlen($this->post('admin_email')))
            throw new InstallerException('Please specify administrator email address', 'admin_email');

        if (!filter_var($this->post('admin_email'), FILTER_VALIDATE_EMAIL))
            throw new InstallerException('Please specify valid email address', 'admin_email');

        if (!strlen($this->post('admin_password')))
            throw new InstallerException('Please specify password', 'admin_password');
    }

    protected function onValidateAdvancedConfig()
    {
        if (!strlen($this->post('encryption_code')))
            throw new InstallerException('Please specify encryption key', 'encryption_code');

        if (strlen($this->post('encryption_code')) < 6)
            throw new InstallerException('The encryption key should be at least 6 characters in length.', 'encryption_code');

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
        return $this->requestServerData('project/detail', array('id' => $this->post('code')));
    }

    protected function onInstallStep()
    {
        $installStep = $this->post('step');
        $this->log('Install step: %s', $installStep);
        $result = false;

        switch ($installStep) {
            case 'getMetaData':
                $plugins = $this->post('plugins', array());
                $pluginCodes = array();
                foreach ($plugins as $plugin) {
                    if (isset($plugin['code'])) $pluginCodes[] = $plugin['code'];
                }

                $params = array('plugins' => $pluginCodes);
                if ($project = $this->post('project', false))
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
                if ($project = $this->post('project', false))
                    $params['project'] = $project;

                $hash = $this->getHashFromMeta($name, 'plugin');
                $this->requestServerFile($name, $hash, 'plugin/get', $params);
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

                $result = $this->unzipFile($name, 'plugins/');
                if (!$result)
                    throw new Exception('Unable to open plugin archive file');
                break;

            case 'setupConfig':
                $this->buildConfigFile();
                break;

            case 'createAdmin':
                $this->createAdminAccount();
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

    private function buildConfigFile()
    {
        $this->bootFramework();

        $this->rewriter->toFile($this->configDirectory . '/app.php', array(
            'url'    => $this->getBaseUrl(),
            'locale' => 'en',
            'key'    => $this->post('encryption_code', 'ChangeMe!123'),
        ));

        $this->rewriter->toFile($this->configDirectory . '/cms.php', array(
            'activeTheme' => 'demo',
            'backendUri'  => $this->post('backend_uri', '/backend'),
        ));

        $this->rewriter->toFile($this->configDirectory . '/database.php', $this->getDatabaseConfigValues());
    }

    private function getDatabaseConfigValues()
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
            'user' => $this->post('db_user', ''),
            'pass' => $this->post('db_pass', ''),
            'prefix' => $this->post('db_prefix', ''),
        ));

        extract($config);

        switch ($type) {
            default:
            case 'mysql':
                return array(
                    'connections.mysql.host'     => $host,
                    'connections.mysql.database' => $name,
                    'connections.mysql.username' => $user,
                    'connections.mysql.password' => $pass,
                    'connections.mysql.prefix'   => $prefix,
                );

            case 'sqlite':
                return array(
                    'connections.sqlite.database' => $name,
                );

            case 'pgsql':
                return array(
                    'connections.pgsql.host'     => $host,
                    'connections.pgsql.database' => $name,
                    'connections.pgsql.username' => $user,
                    'connections.pgsql.password' => $pass,
                    'connections.pgsql.prefix'   => $prefix,
                );

            case 'sqlsrv':
                return array(
                    'connections.sqlsrv.host'     => $host,
                    'connections.sqlsrv.database' => $name,
                    'connections.sqlsrv.username' => $user,
                    'connections.sqlsrv.password' => $pass,
                    'connections.sqlsrv.prefix'   => $prefix,
                );
        }
    }

    private function createAdminAccount()
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
        $updater->install();
    }

    public function setCoreBuild()
    {
        $this->bootFramework();

        call_user_func('System\Models\Parameters::set', array(
            'system::core.hash'  => post('hash'),
            'system::core.build' => post('build'),
        ));
    }

    //
    // File Management
    //

    private function moveHtaccess($old = null, $new = null)
    {
        $oldFile = $this->baseDirectory . '/.htaccess';
        if ($old) $oldFile .= '.' . $old;

        $newFile = $this->baseDirectory . '/.htaccess';
        if ($new) $newFile .= '.' . $new;

        if (file_exists($oldFile))
            rename($oldFile, $newFile);
    }

    private function unzipFile($fileCode, $directory = null)
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

    private function getFilePath($fileCode)
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

    public function log()
    {
        $args = func_get_args();
        $message = array_shift($args);

        if (is_array($message))
            $message = implode(PHP_EOL, $message);

        $filename = $this->logFile;
        $stream = fopen($filename, 'a');
        $string = "[" . date("Y/m/d h:i:s", time()) . "] " . vsprintf($message, $args);
        fwrite($stream, $string . PHP_EOL);
        fclose($stream);
    }

    //
    // Helpers
    //

    private function bootFramework()
    {
        require $this->baseDirectory . '/bootstrap/autoload.php';
        $this->app = $app = require_once $this->baseDirectory . '/bootstrap/start.php';
        $app->boot();
    }

    private function requestServerData($uri = null, $params = array())
    {
        $result = null;
        $error = null;
        try {
            $params['url'] = base64_encode($this->getBaseUrl());
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

    private function requestServerFile($fileCode, $expectedHash, $uri = null, $params = array())
    {
        $result = null;
        $error = null;
        try {

            if (!file_exists($this->tempDirectory))
                mkdir($this->tempDirectory, 0777, true); // @todo Use config

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

    private function prepareServerRequest($uri, $params = array())
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, OCTOBER_GATEWAY.'/'.$uri);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3600);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION , true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));

        if (defined('OCTOBER_GATEWAY_AUTH')) {
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, OCTOBER_GATEWAY_AUTH);
        }

        return $curl;
    }

    private function post($var, $default = null)
    {
        if (array_key_exists($var, $_REQUEST)) {
            $result = $_REQUEST[$var];
            if (is_string($result)) $result = trim($result);
            return $result;
        }

        return $default;
    }

    private function getHashFromMeta($targetCode, $packageType = 'plugin')
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
            $baseUrl = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
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
}
