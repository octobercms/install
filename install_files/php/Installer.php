<?php

class Installer
{
    private $debugMode;

    protected $app;

    protected $rewriter;

    protected $dirFramework;
    protected $dirConfig;

    /**
     * Constructor/Router
     */
    public function __construct($debugMode = false)
    {
        $this->debugMode = $debugMode;
        $this->rewriter = new InstallerRewrite;
        $this->dirFramework = PATH_INSTALL . '/temp';
        $this->dirConfig = $this->dirFramework . '/app/config';

        if ($handler = $this->post('handler')) {
            try {
                if (!preg_match('/^on[A-Z]{1}[\w+]*$/', $handler))
                    throw new Exception(sprintf('Invalid handler: %s', $handler));

                if (method_exists($this, $handler) && ($result = $this->$handler()) !== null) {
                    header('Content-Type: application/json');
                    die(json_encode($result));
                }
            }
            catch (Exception $ex) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
                die($ex->getMessage());
            }
        }
    }

    protected function onCheckRequirement()
    {
        $checkCode = $this->post('code');
        $result = false;
        switch ($checkCode) {
            case 'liveConnection':
                $result = ($this->requestServerData('ping') !== null);
                break;
            case 'writePermission':
                $result = is_writable(PATH_INSTALL);
                break;
            case 'phpVersion':
                // $result = version_compare(PHP_VERSION , "5.4", ">=");
                $result = true; // Debug
                break;
            case 'safeMode':
                $result = !ini_get('safe_mode');
                break;
            case 'pdoLibrary':
                $result = defined('PDO::ATTR_DRIVER_NAME');
                break;
            case 'curlLibrary':
                $result = function_exists('curl_init');
                break;
            case 'mcryptLibrary':
                $result = extension_loaded('mcrypt');
                break;
            case 'zipLibrary':
                $result = class_exists('ZipArchive');
                break;
        }

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

    protected function onGetPopularPackages()
    {
        return $this->requestServerData('package/popular');
    }

    protected function onSearchPackages()
    {
        return $this->requestServerData('package/search', array('query' => $this->post('query')));
    }

    protected function onPluginDetails()
    {
        return $this->requestServerData('plugin/detail', array('code' => $this->post('code')));
    }

    protected function onInstallStep()
    {
        $installStep = $this->post('step');
        $result = false;

        switch ($installStep) {
            case 'getMetaData':
                $plugins = $this->post('plugins', array());
                $pluginCodes = array();
                foreach ($plugins as $plugin) {
                    if (isset($plugin['code'])) $pluginCodes[] = $plugin['code'];
                }
                $result = $this->requestServerData('install/public', array(
                    'plugins' => $pluginCodes
                ));
                break;

            case 'downloadCore':
                $data = $this->requestServerData('core/get');
                $expectedHash = $this->getHashFromMeta('core');
                $result = $this->processFileResponse($data, 'core', $expectedHash);
                break;

            case 'downloadPlugin':
                $name = $this->post('name');
                if (!$name)
                    throw new Exception('Plugin download failed, missing name');

                $data = $this->requestServerData('plugin/get/public', array('name' => $name));
                $expectedHash = $this->getHashFromMeta($name, 'plugin');
                $result = $this->processFileResponse($data, $name, $expectedHash);
                break;

            case 'extractCore':
                $this->moveHtaccess(null, 'installer');

                $result = $this->unzipFile('core');
                if (!$result)
                    throw new Exception('Unable to open core archive file');

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
                $this->moveHtaccess(null, 'installer');
                $this->moveHtaccess('october', null);
                break;
        }

        return array('result' => $result);
    }

    //
    // Installation Steps
    //

    private function buildConfigFile()
    {
        $this->startFramework();

        $this->rewriter->toFile($this->dirConfig . '/app.php', array(
            'url'    => 'base_url',
            'locale' => 'en',
            'key'    => 'encryption_code',
        ));

        $this->rewriter->toFile($this->dirConfig . '/cms.php', array(
            'activeTheme' => 'demo',
            'backendUri'  => '/backend',
        ));

        $this->rewriter->toFile($this->dirConfig . '/database.php', array(
            'connections.mysql.host'     => 'db_host',
            'connections.mysql.database' => 'db_name',
            'connections.mysql.username' => 'db_user',
            'connections.mysql.password' => 'db_pass',
            'connections.mysql.prefix'   => 'db_prefix',
        ));
    }

    private function createAdminAccount()
    {
        $this->startFramework();

        /*
         * Prepare admin seed defaults
         */
        $seeder = 'Backend\Database\Seeds\SeedSetupAdmin';
        $seederObj = new $seeder;
        $seederObj->setDefaults(array(
            'email' => 'xxx',
            'login' => 'xxx',
            'password' => 'xxx',
            'firstName' => 'xxx',
            'lastName' => 'xxx',
        ));

        /*
         * Install application
         */
        $updater = call_user_func('System\Classes\UpdateManager::instance');
        $updater->install();
    }

    //
    // File Management
    //

    private function moveHtaccess($old = null, $new = null)
    {
        $oldFile = $this->dirFramework . '/.htaccess';
        if ($old) $oldFile .= '.' . $old;

        $newFile = $this->dirFramework . '/.htaccess';
        if ($new) $newFile .= '.' . $new;

        if (file_exists($oldFile))
            rename($oldFile, $newFile);
    }

    private function unzipFile($fileCode, $directory = null)
    {
        $source = $this->getFilePath($fileCode);
        $destination = $this->dirFramework;

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

    private function processFileResponse($data, $fileCode, $expectedHash)
    {
        if (!isset($data['data']))
            throw new Exception('Invalid response from server');

        try {
            $data = base64_decode($data['data']);
        }
        catch (Exception $ex) {
            throw new Exception('Invalid response from server');
        }

        $filePath = $this->putFile($fileCode, $data);
        $fileHash = md5_file($filePath);

        if ($expectedHash != $fileHash) {
            unlink($filePath); // use Cleanup instead
            // $this->cleanUp();
            throw new Exception('File from server is corrupt');
        }

        return true;
    }

    private function putFile($fileCode, $contents)
    {
        $name = md5($fileCode) . '.arc';
        $tmpDir = PATH_INSTALL . '/install_files/temp';
        if (!file_exists($tmpDir))
            mkdir($tmpDir);

        $filePath = $tmpDir . '/' . $name;
        file_put_contents($filePath, $contents);
        return $filePath;
    }

    private function getFilePath($fileCode)
    {
        $name = md5($fileCode) . '.arc';
        $tmpDir = PATH_INSTALL . '/install_files/temp';
        return $tmpDir . '/' . $name;
    }

    //
    // Helpers
    //

    private function startFramework()
    {
        require $this->dirFramework . '/bootstrap/autoload.php';
        $this->app = $app = require_once $this->dirFramework . '/bootstrap/start.php';
    }

    private function cleanUp()
    {

    }

    private function requestServerData($uri = null, $params = array())
    {
        $result = null;
        $error = null;
        try {
            $postData = http_build_query($params, '', '&');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, OCTOBER_GATEWAY.'/'.$uri);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION , true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode == 500) {
                $error = $result;
                $result = '';
            }
        }
        catch (Exception $ex) {}

        if ($error !== null)
            throw new Exception('Server responded with error: ' . $error);

        if (!$result || !strlen($result))
            throw new Exception('Server responded had no response.');

        try {
            $result = @json_decode($result, true);
        }
        catch (Exception $ex) {}

        if (!is_array($result)) {
            if ($this->debugMode)
                throw new Exception('Invalid server response: '. $result);
            else
                throw new Exception('Server returned an invalid response.');
        }

        return $result;
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
            return (isset($meta['core'])) ? $meta['core'] : null;

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
}
