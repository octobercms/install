<?php

define('PATH_INSTALL', str_replace("\\", "/", realpath(dirname(__FILE__)."/")));
define('OCTOBER_GATEWAY', 'http://octobercms.com');

require_once 'InstallerException.php';
require_once 'Installer.php';