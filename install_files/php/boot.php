<?php

/*
 * Required PHP version for October CMS
 */
define('REQUIRED_PHP_VERSION', '8.0.2');
define('WANT_OCTOBER_VERSION', '^3.0');

if (version_compare(trim(strtolower(PHP_VERSION)), REQUIRED_PHP_VERSION, '<')) {
    exit('PHP version 8.0.2 or above is required to install October CMS.');
}

/*
 * Check for JSON extension
 */
if (!function_exists('json_decode')) {
    exit('JSON PHP Extension is required to install October CMS.');
}

/*
 * PHP headers
 */
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

/*
 * Debug mode
 */
$isDebug = array_key_exists('debug', $_REQUEST);

if ($isDebug) {
    ini_set('display_errors', 1);
    error_reporting(1);
}
else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

/*
 * Constants
 */
define('PATH_INSTALL', str_replace("\\", "/", realpath(dirname(__FILE__)."/../../")));
define('OCTOBER_GATEWAY', 'https://gateway.octobercms.com/api');

/*
 * Address timeout limits
 */
@set_time_limit(3600);

/*
 * Prevent PCRE engine from crashing
 */
ini_set('pcre.recursion_limit', '524'); // 256KB stack. Win32 Apache

/*
 * Handle fatal errors with AJAX
 */
register_shutdown_function('installerShutdown');
function installerShutdown()
{
    global $installer;
    $error = error_get_last();
    if ($error && $error['type'] == 1) {
        header('HTTP/1.1 500 Internal Server Error');
        $errorMsg = htmlspecialchars_decode(strip_tags($error['message']));
        echo $errorMsg;
        if (isset($installer)) {
            $installer->log('Fatal error: %s on line %s in file %s', $errorMsg, $error['line'], $error['file']);
        }
        exit;
    }
}

/*
 * Bootstrap the installer
 */
require_once 'InstallerException.php';
require_once 'InstallerHandlers.php';
require_once 'InstallerSetup.php';
require_once 'Installer.php';

try {
    $installer = new Installer();
    $installer->cleanLog();
    $installer->log('Host: %s', php_uname());
    $installer->log('PHP version: %s', PHP_VERSION);
    $installer->log('Server software: %s', isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown');
    $installer->log('Operating system: %s', PHP_OS);
    $installer->log('Memory limit: %s', ini_get('memory_limit'));
    $installer->log('Max execution time: %s', ini_get('max_execution_time'));
}
catch (Exception $ex) {
    $fatalError = $ex->getMessage();
}
