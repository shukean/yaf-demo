<?php

namespace Yk;

abstract class Logger{

    private static $logger;

    public static function init($logger){
        self::$logger = $logger;
    }

    public static function debug($message,  $errno = 0,  $params = []){}
    public static function info($message,  $errno = 0 ,  $params = []){} // public static function trace 是info的别名
    public static function warn($message,  $errno = 0,  $params = []){}
    public static function error($message,  $errno = 0,  $params = []){}
    public static function fatal($message,  $errno = 0,  $params = []){}
}
