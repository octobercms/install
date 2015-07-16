<?php
/*
 * Simple Language Support
 */

class Lang
{
    protected static $langStrings = null;
    protected static $lang = null;
    protected static $languageList = null;

    public static function get($string) {
        if (static::$langStrings === null) {
            static::init();
        }

        if (isset(static::$langStrings[$string])) {
            return static::$langStrings[$string];
        } else {
            return $string;
        }
    }

    public static function getLangList() {
        if (static::$languageList === null) {
            static::init();
        }
        return static::$languageList;
    }

    public static function getLang() {
        if (static::$lang === null) {
            static::init();
        }
        return static::$lang;
    }

    protected static function init() {
        static::$lang = 'en';
        /* Load Language List */
        if (static::$languageList === null) {
            static::$languageList = require_once PATH_INSTALL . '/install_files/lang/list.php';
        }
        /* Get User Choose Language */
        if (isset($_GET['lang']) && isset(static::$languageList[$_GET['lang']])) {
            static::$lang = $_GET['lang'];
            setcookie('lang', static::$lang);
        } else {
            if (isset($_COOKIE['lang']) && isset(static::$languageList[$_COOKIE['lang']])) {
                static::$lang = $_COOKIE['lang'];
            }
        }
        /* Load Language File */
        if (static::$langStrings === null) {
            $path = PATH_INSTALL . '/install_files/lang/' . static::$lang . '/lang.php';
            static::$langStrings = require_once $path;
        }
    }
}