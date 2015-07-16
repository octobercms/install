<?php
/*
 * Simple Language Support
 */

class Lang
{
    protected static $langStrings = null;
    protected static $lang = null;

    public static function get($string) {
        if (static::$lang == null) {
            static::init();
        }
        if (static::$langStrings === null) {
            $path = PATH_INSTALL . '/install_files/lang/' . static::$lang . '/php/lang.php';
            static::$langStrings = require_once $path;
        }

        if (isset(static::$langStrings[$string])) {
            return static::$langStrings[$string];
        } else {
            return $string;
        }
    }

    protected static function init() {
        static::$lang = 'en';
        $languageList = require_once PATH_INSTALL . '/install_files/lang/list.php';
        if (isset($_GET['lang']) && isset($languageList[$_GET['lang']])) {
            static::$lang = $_GET['lang'];
            setcookie('lang', static::$lang);
        } else {
            if (isset($_COOKIE['lang']) && isset($languageList[$_COOKIE['lang']])) {
                static::$lang = $_COOKIE['lang'];
            }
        }
    }
}