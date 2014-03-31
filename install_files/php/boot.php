<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$isDebug = array_key_exists('debug', $_REQUEST);

if (!$isDebug) {
    ini_set('display_errors', 0);
    error_reporting(0);
}

define('PATH_INSTALL', str_replace("\\", "/", realpath(dirname(__FILE__)."/../../")));
define('OCTOBER_GATEWAY', 'http://octobercms.com/api');

/*
 * Address timeout and memory limits
 */
if (!ini_get('safe_mode'))
    set_time_limit(3600);

/*
 * Handle fatal errors with AJAX
 */
register_shutdown_function('installerShutdown');
function installerShutdown()
{
    $error = error_get_last();
    if ($error['type'] == 1) {
        header('HTTP/1.1 500 Internal Server Error');
        echo htmlspecialchars_decode(strip_tags($error['message']));
        exit;
    }
}

/*
 * Bootstrap the installer
 */
require_once 'InstallerException.php';
require_once 'InstallerRewrite.php';
require_once 'Installer.php';

$installer = new Installer($isDebug);
