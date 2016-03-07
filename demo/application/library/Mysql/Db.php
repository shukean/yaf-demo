<?php

/*
 * https://github.com/shukean/php-mysqli-lib
 */

namespace Mysql;

final class Db{

    private static $_config = [];

    private static $_links = [];

    private static $_transactiones = [];

    private static $_transactiones_num = [];

    public static function initConfig(array $conf){
        self::$_config = $conf;
        $mysql_ids = array_filter(array_keys($conf), function($v){
            return is_numeric($v);
        });
        Gconfig::$DB_MASTER_ID = array_shift($mysql_ids);
        Gconfig::$DB_SLAVES_IDS = $mysql_ids;
    }

    protected static function _connect(&$id, $keep_slave_id = false, $retry = 0){
        static $config = null;
        if (is_null($config)){
            $config = self::$_config;
        }

        if (!isset(self::$_links[$id])){
            if (!isset($config[$id])){
                throw new \Exception("mysql config id $id not found !", 600);
            }
            $cf = $config[$id];
            $link = \mysqli_init();
            if (isset($cf['connection_timeout'])){
                \mysqli_options($link, MYSQLI_OPT_CONNECT_TIMEOUT, $cf['connection_timeout']);
            }
            $cf['socket'] = isset($cf['socket']) ? $cf['socket'] : null;
            $cf['flags'] = isset($cf['flags']) ? $cf['flags'] : null;
            $conn_ret = @$link->real_connect($cf['host'], $cf['user'], $cf['password'], $cf['dbname'], $cf['port'], $cf['socket'], $cf['flags']);
            if (!$conn_ret){
                mysql_log_warn(__LINE__, __FILE__, "mysql connect error : [{$link->connect_errno}]{$link->connect_error}, but try again!", $link->connect_errno, ['retry' => $retry, 'mysql_id' => $id, 'keep_slave_id' => $keep_slave_id]);
                if ($retry < 4){
                    usleep(10000 * $retry);

                    if ($retry > 0 && !$keep_slave_id && Gconfig::ckIdIsSlave($id)){
                        $cur_id = $id;
                        $id = Gconfig::getSlaveId($id);
                        mysql_log_info(__LINE__, __FILE__, "mysql Change slave id", 0, ['new_mysql_id' => $id, 'mysql_id' => $cur_id]);
                    }
                    return self::_connect($id, $keep_slave_id, ++$retry);
                }else{
                    mysql_log_error(__LINE__, __FILE__, "mysql id $id connect error, stop service !!!", $link->connect_errno, ['retry' => $retry, 'mysql_id' => $id]);
                    throw new \Exception("mysql id $id connect error", 600);
                }
            }
            $link->set_charset($cf['charset']);
            self::$_links[$id] = $link;
        }
        return self::$_links[$id];
    }

    public static function getTransNums($id){
        return isset(self::$_transactiones_num[$id]) ? self::$_transactiones_num[$id] : 0;
    }

    public static function getMasterTransNums(){
        return self::getTransNums(Gconfig::$DB_MASTER_ID);
    }

    public static function freeMasterTransNotReleaseLinks(){
        return self::rollback(Gconfig::$DB_MASTER_ID);
    }

    public static function closeLink($id){
        if (isset(self::$_links[$id])){
            self::$_links[$id]->close();
            unset(self::$_links[$id]);
        }
    }

    protected static function changeSlaveReConn(&$id){
        self::_connect($id, false);
    }

    protected static function _getLink(&$id){
        if (!isset(self::$_links[$id])){
            self::_connect($id);
        }
        return self::$_links[$id];
    }

    public static function linkReConn($id = false){
        if ($id === false) {
            foreach (self::$_links as $link){
                self::closeLink($id);
            }
        } else {
            self::closeLink($id);
            self::_connect($id, true);
        }
    }

    public static function getLink($id){
        if (isset(self::$_links[$id])){
            return self::$_links[$id];
        }else{
            throw new \Exception("mysql link $id had not be connected", 600);
        }
    }

    public static function query($id, $sql, array $args = [], $silent = false, $keep_slave_id = false, $retry = 3){
        if (!empty($args)) {
            $newsql = '';
            $i = 0;
            $pos = 0;
            $count = count($args);
            for ($i = 0, $len = strlen($sql); $i < $len; $i++) {
                if ($sql[$i] != '%') {
                    $newsql .= $sql[$i];
                } else {
                    switch ($sql[$i + 1]) {
                        case 't':
                            $newsql .= '`' . $args[$pos] . '`';
                            break;
                        case 'd':
                            $newsql .= intval($args[$pos]);
                            break;
                        case 's':
                            $newsql .= self::quote(is_array($args[$pos]) ? serialize($args[$pos]) : $args[$pos]);
                            break;
                        case 'f':
                            $newsql .= sprintf('%f', $args[$pos]);
                            break;
                        case 'i':
                            $newsql .= $args[$pos];
                            break;
                        case 'n':
                            $newsql .= is_array($args[$pos]) ? implode(',', self::quote($args[$pos])) : self::quote($args[$pos]);
                            break;
                        default:
                            $newsql .= $sql[$i] . $sql[$i + 1];
                            $pos--;
                            break;
                    }
                    $pos++;
                    $i++;
                }
            }
            $sql = $newsql;
        } else {
            $newsql = $sql;
        }

        $had_trans = empty(self::$_transactiones[$id]) ? 0 : 1;
        // echo $newsql, PHP_EOL;
        mysql_log_debug(__LINE__, __FILE__, $newsql);
        $result = $had_trans ? self::getLink($id)->query($newsql) : self::_getLink($id)->query($newsql);
        if (!$result && !$silent) {

            $errno = self::getLink($id)->errno;
            $error = self::getLink($id)->error;

            if ($retry > 0 && in_array($errno, [2006, 2013]) && !$had_trans){
                mysql_log_warn(__LINE__, __FILE__, $error, $errno, [
                    'sql' => $sql,
                    'retry' => $retry,
                    'mysql_id' => $id,
                    'had_trans' => $had_trans,
                    'keep_slave' => $keep_slave_id
                ]);

                self::closeLink($id);

                if (Gconfig::ckIdIsSlave($id) && !$keep_slave_id){
                    self::changeSlaveReConn($id);
                }

                return self::query($id, $sql, [], $silent, --$retry);
            }

            if ($silent) {
                mysql_log_error(__LINE__, __FILE__, $error, $errno, [
                    'sql' => $sql,
                    'retry' => $retry,
                    'silent' => 1,
                    'had_trans' => $had_trans,
                    'mysql_id' => $id
                ]);
                return false;
            }

            // free trans
            if ($had_trans) {
                self::rollback($id);
            }

            mysql_log_error(__LINE__, __FILE__, $error, $errno, [
                'sql' => $sql,
                'retry' => $retry,
                'had_trans' => $had_trans,
                'mysql_id' => $id
            ]);
            throw new \Exception("<$errno>" . $error);
        }
        return $result;
    }

    public static function getError($id){
        return self::getLink($id)->error;
    }

    public static function getErrno($id){
        return self::getLink($id)->errno;
    }

    public static function getLastInsertId($id){
        return self::getLink($id)->insert_id;
    }

    public static function fetchResult(\mysqli_result $result){
        return $result->fetch_assoc();
    }

    public static function freeResult(\mysqli_result $result){
        return $result->free();
    }

    public static function affectedRows($id){
        return self::getLink($id)->affected_rows;
    }

    // SQL_CALC_FOUND_ROWS
    public static function foundRows($id){
        return self::result($id, "SELECT FOUND_ROWS()", [], 0, false , true);
    }

    public static function insert($id, $table, array $data, $return_last_id = false, $silent = false){
        $sql = "insert into `$table` set " . self::implode($data, ',');
        $result = self::query($id, $sql, [], $silent);
        if ($result && $return_last_id) {
            return self::getLastInsertId($id);
        } else {
            return $result;
        }
    }

    public static function replace($id, $table, array $data, $silent = false){
        $sql = "replace into `$table` set " . self::implode($data, ',');
        return self::query($id, $sql, [], $silent);
    }

    public static function duplicate($id, $table, array $key, array $data, $silent = false){
        $com = self::implode($data);
        $sql = "insert into `$table` set " . self::implode($key) . ", $com on duplicate key update $com";
        return self::query($id, $sql, [], $silent);
    }

    public static function update($id, $table, array $data, array $condition, $return_affected_rows = false, $silent = false){
        $sql = "update `$table` set " . self::implode($data) . ' where ' . self::implode($condition, 'AND');
        $result = self::query($id, $sql, [], false);
        if ($result && $return_affected_rows) {
            return self::affectedRows($id);
        } else {
            return $result;
        }
    }

    public static function delete($id, $table, array $condition, $return_affected_rows = false, $silent = false){
        $sql = "delete from `$table` where " . self::implode($condition, 'AND');
        $result = self::query($id, $sql, [], $silent);
        if ($result && $return_affected_rows) {
            return self::affectedRows($id);
        } else {
            return $result;
        }
    }

    public static function one($id, $sql, array $args, $forupdate = false, $silent = false, $keep_slave_id = false){
        $result = self::query($id, $sql . " limit 1" . ($forupdate ? " for update" : ''), $args, $silent, $keep_slave_id);
        if ($result) {
            $row = $result->fetch_assoc();
            self::freeResult($result);
            return $row;
        } else {
            return [];
        }
    }

    public static function more($id, $sql, array $args, $array_key_index = null, $forupdate = false, $silent = false, $keep_slave_id = false){
        $result = self::query($id, $sql . ($forupdate ? " for update" : ''), $args, $silent, $keep_slave_id);
        $return = [];
        if ($array_key_index) {
            while (null !== ($row = $result->fetch_assoc())) {
                $return[$row[$array_key_index]] = $row;
            }
        } else {
            while (null !== ($row = $result->fetch_assoc())) {
                $return[] = $row;
            }
        }
        self::freeResult($result);
        return $return;
    }

    public static function rowColumn($id, $sql, array $args, $column_pos = 0, $silent = false, $keep_slave_id = false){
        $result = self::query($id, $sql . " limit 1", $args, $silent, $keep_slave_id);
        $row = $result->fetch_row();
        self::freeResult($result);
        return isset($row[$column_pos]) ? $row[$column_pos] : null;
    }

    public static function lockTable($id, $table){
        return self::query($id, "lock $table write");
    }

    public static function unlockTable($id, $table){
        return self::query($id, "unlock $table write");
    }

    public static function begin($id, $flags = null, $point_name = null){
        if (!isset(self::$_transactiones[$id])) {
            if ($flags && $point_name){
                $ret = self::_getLink($id)->begin_transaction($flags, $point_name);
            }elseif ($flags){
                $ret = self::_getLink($id)->begin_transaction($flags);
            }else{
                $ret = self::_getLink($id)->begin_transaction();
            }
            if (!$ret){
                throw new \Exception("mysql $id begin transaction fail");
            }
            self::$_transactiones[$id] = 1;
            self::$_transactiones_num[$id] = 1;
        } else {
            self::$_transactiones_num[$id]++;
        }
        return self::$_transactiones_num[$id] > 0;
    }

    public static function commit($id){
        if (isset(self::$_transactiones[$id]) && self::$_transactiones[$id]) {
            if (self::$_transactiones_num[$id] < 2) {
                $ret = self::getLink($id)->commit();
                if (!$ret){
                    throw new \Exception("mysql $id commit fail");
                }
                unset(self::$_transactiones[$id]);
                unset(self::$_transactiones_num[$id]);
            } else {
                self::$_transactiones_num[$id]--;
            }
        }
        return !isset(self::$_transactiones_num[$id]) || self::$_transactiones_num[$id] < 1;
    }

    public static function rollback($id){
        if (isset(self::$_transactiones[$id]) && self::$_transactiones[$id]) {
            $ret = self::getLink($id)->rollback();
            if (!$ret){
                throw new \Exception("mysql $id rollback fail");
            }
            unset(self::$_transactiones[$id]);
            unset(self::$_transactiones_num[$id]);
        }
        return !isset(self::$_transactiones_num[$id]) || self::$_transactiones_num[$id] < 1;
    }

    public static function implode($data, $glue = ','){
        $glue = ' ' . trim($glue) . ' ';
        $sql = $comma = '';
        foreach ($data as $k => $v) {
            $sql .= $comma . self::quoteFields($k) . '=' . self::quote($v);
            $comma = $glue;
        }
        return $sql;
    }

    public static function quote($str, $noarray = false){
        if (is_string($str)) {
            return '\'' . addcslashes($str, "\n\r\\'\"\032") . '\'';
        } elseif (is_int($str) || is_float($str)) {
            return '\'' . $str . '\'';
        } elseif (is_bool($str)) {
            return $str ? '1' : '0';
        } elseif (is_array($str)) {
            if ($noarray === false) {
                foreach ($str as &$v) {
                    $v = self::quote($v, true);
                }
                return $str;
            } else {
                return '\'\'';
            }
        } else {
            return '\'\'';
        }
    }

    public static function quoteFields($fields){
        if (strpos($fields, '.') === false) {
            return "`$fields`";
        } else {
            list ($t, $name) = explode('.', $fields);
            return "$t.`$name`";
        }
    }
}
