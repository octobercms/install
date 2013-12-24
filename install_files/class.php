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

    protected function onSearchPackages()
    {
        return json_decode(file_get_contents('test.json'));
    }

    protected function onCheckRequirement()
    {
        $checkCode = $this->post('code');
        $result = false;
        switch ($checkCode) {
            case 'liveConnection':
                $result = ($this->requestServerData(OCTOBER_GATEWAY) !== null);
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

    //
    // Helpers
    //

    private function requestServerData($url, $params = array())
    {
        $result = null;
        try {
            $postData = http_build_query($params, '', '&');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
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
            return $_REQUEST[$var];

        return $default;
    }

}

define('PATH_INSTALL', str_replace("\\", "/", realpath(dirname(__FILE__)."/")));
define('OCTOBER_GATEWAY', 'http://octobercms.com');
$installer = new Installer;