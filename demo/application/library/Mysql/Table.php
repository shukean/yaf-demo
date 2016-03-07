<?php

/*
 * https://github.com/shukean/php-mysqli-lib
 */

namespace Mysql;

abstract class Table{

    public static function table(){
        throw new \Exception(self::class . " table method must defined");
    }

    public static function master(){
        return Gconfig::$DB_MASTER_ID;
    }

    public static function slave(){
        return Gconfig::getSlaveId();
    }

    public static function begin(){
        return Db::begin(self::master());
    }

    public static function commit(){
        return Db::commit(self::master());
    }

    public static function rollback(){
        return Db::rollback(self::master());
    }

}
