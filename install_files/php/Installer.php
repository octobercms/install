<?php

class Installer
{

    /**
     * Constructor/Router
     */
    public function __construct()
    {
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
                $result = ($this->requestServerData() !== null);
                break;
            case 'writePermission':
                $result = is_writable(PATH_INSTALL);
                break;
            case 'phpVersion':
                $result = version_compare(PHP_VERSION , "5.2", ">="); // Debug
                // $result = version_compare(PHP_VERSION , "5.4", ">=");
                break;
            case 'safeMode':
                $result = !ini_get('safe_mode');
                break;
            case 'curlLibrary':
                $result = function_exists('curl_init');
                break;
            case 'mcryptLibrary':
                $result = function_exists('mcrypt_encrypt');
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
            throw new InstallerException('Please specify a first name', 'admin_first_name');
    }

    protected function onValidateAdvancedConfig()
    {
        
    }

    protected function onSearchPackages()
    {
        return json_decode(file_get_contents('test.json'));
    }

    protected function onInstallStep()
    {
        $installStep = $this->post('step');
        $result = false;

        switch ($installStep) {
            case 'getMetaData':
                // Download file hashes
                break;
            case 'downloadCore':
                // Download the core archive
                break;

            case 'downloadPlugin':
                // Download each plugin
                break;

            case 'extractCore':
                // Extract core
                break;

            case 'extractPlugin':
                // Extract each plugin
                break;

            case 'setupConfig':
                // Set up config files
                break;

            case 'createAdmin':
                // Create admin account
                break;
        }

        return array('result' => $result);
    }

    //
    // Helpers
    //

    private function requestServerData($uri = null, $params = array())
    {
        $result = null;
        try {
            $postData = http_build_query($params, '', '&');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, OCTOBER_GATEWAY.$uri);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION , true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            $result = curl_exec($ch);
        } 
        catch (Exception $ex) {}

        if (!$result || !strlen($result))
            throw new Exception('Unable to make an outgoing connection to the update server.');

        return $result;
    }

    private function post($var, $default = null)
    {
        if (array_key_exists($var, $_REQUEST))
            return trim($_REQUEST[$var]);

        return $default;
    }
}

$installer = new Installer;