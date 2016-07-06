<?php

define('YKLOGER_LEVEL_DEBUG', 0);
define('YKLOGER_LEVEL_INFO', 0);
define('YKLOGER_LEVEL_WARN', 0);
define('YKLOGER_LEVEL_ERROR', 0);
define('YKLOGER_LEVEL_FATAL', 0);

class ykloger{

    public static function debug($message,  $errno = 0,  $params = []);
    public static function info($message,  $errno = 0 ,  $params = []); // public static function trace 是info的别名
    public static function warn($message,  $errno = 0,  $params = []);
    public static function error($message,  $errno = 0,  $params = []);
    public static function fatal($message,  $errno = 0,  $params = []);
}