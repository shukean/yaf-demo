<?php

/*
 * https://github.com/shukean/php-mysqli-lib
 */

namespace Mysql;

class Gconfig {

    public static $DB_MASTER_ID;
    public static $DB_SLAVES_IDS;

    public static function getSlaveId($ignore_slave_id = 0){
        static $last_id = null, $ignore_ids = [];

        if ($ignore_slave_id && !defined('API_SAPI_CLI') && !in_array($ignore_slave_id, $ignore_ids)){
            $ignore_ids = [$ignore_slave_id];
            self::$DB_SLAVES_IDS = array_diff(self::$DB_SLAVES_IDS, $ignore_ids);
            if (empty(self::$DB_SLAVES_IDS)){
                mysql_log_error(__LINE__, __FILE__, "get mysql slave db fail", 0);
                throw new \Exception('get mysql slave id fail [DB]');
            }
        }

        if (is_null($last_id) || $last_id == $ignore_slave_id){
            shuffle(self::$DB_SLAVES_IDS);
            $last_id = self::$DB_SLAVES_IDS[array_rand(self::$DB_SLAVES_IDS, 1)];
        }

        return $last_id;
    }

    public static function ckIdIsSlave($id){
        return in_array($id, self::$DB_SLAVES_IDS);
    }
}
