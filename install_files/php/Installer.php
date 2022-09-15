<?php

/**
 * Installer
 */
class Installer
{
    use InstallerSetup;
    use InstallerHandlers;

    /**
     * @var string baseDirectory contains the application base path.
     */
    protected $baseDirectory;

    /**
     * @var string tempDirectory is a temporary working directory.
     */
    protected $tempDirectory;

    /**
     * @var string configDirectory is the expected path where configuration files can be found.
     */
    protected $configDirectory;

    /**
     * __construct installer and router
     */
    public function __construct()
    {
        // Establish directory paths
        $this->baseDirectory = PATH_INSTALL;
        $this->tempDirectory = PATH_INSTALL . '/install_files/temp';
        $this->configDirectory = $this->baseDirectory . '/config';
        $this->logFile = PATH_INSTALL . '/install_files/install.log';
        $this->logPost();

        if (!is_null($handler = $this->post('handler'))) {
            if (!strlen($handler)) {
                exit;
            }

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

    protected function octoberToFolderCode($name, $isTheme)
    {
        if ($isTheme) {
            return str_replace('.', '-', strtolower($name));
        }

        return str_replace('.', '/', strtolower($name));
    }

    //
    // Installation Steps
    //

    protected function buildConfigFile()
    {
        $this->bootFramework();

        $this->rewriter->toFile($this->configDirectory . '/app.php', array(
            'url' => $this->getBaseUrl(),
            'locale' => 'en',
            'key' => $this->post('encryption_code', 'CHANGE_ME!!!!!!!!!!!!!!!!!!!!!!!'),
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
            'defaultMask.file' => $this->post('file_mask', '644'),
            'defaultMask.folder' => $this->post('folder_mask', '755'),
        ));

        $this->rewriter->toFile($this->configDirectory . '/database.php', $this->getDatabaseConfigValues());

        // Force cache flush
        $opcache_enabled = ini_get('opcache.enable');
        $opcache_path = trim(ini_get('opcache.restrict_api'));

        if (!empty($opcache_path) && !starts_with(__FILE__, $opcache_path)) {
            $opcache_enabled = false;
        }

        if (function_exists('opcache_reset') && $opcache_enabled) {
            opcache_reset();
        }
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }
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
            mkdir($destination, 0777, true);

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
            ".=====================================================================.",
            "                                                                       ",
            "  .d8888b.   .o8888b.  db  .d8888b.  d8888b. d88888b d8888b.  .d888b.  ",
            " .8P    Y8. d8P    Y8  88 .8P    Y8. 88  `8D 88'     88  `8D .8P , Y8. ",
            " 88      88 8P     .ooo88 88      88 88oooY' 88oooo  88oobY' 88  |  88 ",
            " 88      88 8b     `~~~88 88      88 88~~~b. 88~~~~  88`8b   88  |/ 88 ",
            " `8b    d8' Y8b    d8  88 `8b    d8' 88   8D 88.     88 `88. `8b | d8' ",
            "  `Y8888P'   `Y8888P'  YP  `Y8888P'  Y8888P' Y88888P 88   YD  `Y888P'  ",
            "                                                                       ",
            "`========================== INSTALLATION LOG ========================='",
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
        if (!file_exists($autoloadFile)) {
            throw new Exception('Unable to find autoloader: ~/bootstrap/autoload.php');
        }

        require $autoloadFile;

        $appFile = $this->baseDirectory . '/bootstrap/app.php';
        if (!file_exists($appFile)) {
            throw new Exception('Unable to find app loader: ~/bootstrap/app.php');
        }

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

            $info = curl_getinfo($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($httpCode == 500) {
                $error = file_get_contents($filePath);
            }

            curl_close($curl);
            fclose($stream);

            // Repeat for redirect
            if (in_array($httpCode, [301, 302]) && isset($info['redirect_url'])) {
                $filePath = $this->getFilePath($fileCode);
                $stream = fopen($filePath, 'w');

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $info['redirect_url']);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_TIMEOUT, 3600);
                // curl_setopt($curl, CURLOPT_FOLLOWLOCATION , true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

                if (defined('OCTOBER_GATEWAY_AUTH')) {
                    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    curl_setopt($curl, CURLOPT_USERPWD, OCTOBER_GATEWAY_AUTH);
                }

                curl_setopt($curl, CURLOPT_FILE, $stream);
                curl_exec($curl);

                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                if ($httpCode == 500) {
                    $error = file_get_contents($filePath);
                }

                curl_close($curl);
                fclose($stream);
            }

        }
        catch (Exception $ex) {
            $this->log('Failed to get server delivery: ' . $ex->getMessage());
            throw new Exception('Server failed to deliver the package');
        }

        if ($error !== null)
            throw new Exception('Server responded with error: ' . $error);

        $this->log('Saving to file (%s): %s', $fileCode, $filePath);

        return true;
    }

    protected function prepareServerRequest($uri, $params = array())
    {
        $params['protocol_version'] = '1.2';
        $params['client'] = 'october';
        $params['server'] = base64_encode(json_encode([
            'php' => PHP_VERSION,
            'url' => $this->getBaseUrl()
        ]));

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
            $baseUrl = 'http://localhost';
        }

        return rtrim($baseUrl, '/');
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

        // Remove installer files
        $this->recursiveRemove('install_files');
        @unlink('install-master.zip');
        @unlink('install.php');
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

    protected function recursiveRemove($dir)
    {
        $structure = glob(rtrim($dir, '/') . '/*');

        if (is_array($structure)) {
            foreach ($structure as $file) {
                if (is_dir($file)) {
                    $this->recursiveRemove($file);
                }
                elseif (is_file($file)) {
                    @unlink($file);
                }
            }
        }

        @rmdir($dir);
    }
}
