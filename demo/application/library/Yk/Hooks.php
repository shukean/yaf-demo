<?php

namespace Yk;

class Hooks {

    private static $shutdown = [];

    public static function getShutdownFunctions(){
        return self::$shutdown;
    }

    public static function regShutdownFunction(callable $func){
        self::$shutdown[] = $func;
    }


}