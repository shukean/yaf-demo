<?php

class BootStrap extends \Yaf\Bootstrap_Abstract{

    //这里可以引入全局的方法
    public static function _initLoadLibs(){

    }

    public static function _initYkLogConf(){
        \ykloger::init([
            'logFile' => get_conf_val('log.path').'/'.get_conf_val('log.ykloger.pre'),
            'logLevel' => \Yk\Config::getInstance()->env == ENV_PRODUCT ? YKLOGER_LEVEL_INFO : YKLOGER_LEVEL_DEBUG
        ]);
    }

    public static function _initRequestExtra(){
        $g_logid = val('g_logid', VERIFY_String);
        $g_reqid = val('g_reqid', VERIFY_String);
        $g_platform = val('g_platform', VERIFY_String);

        $req_extras = \Yk\ReqExtras::getInstance();
        if ($g_logid){
            $req_extras->outer_log_id = $g_logid;
        }
        if ($g_reqid){
            $req_extras->outer_req_id = $g_reqid;
        }
        if ($g_platform){
            $req_extras->outer_platform_id = $g_platform;
        }
        $req_extras->inner_req_id = \ykloger::getRequestId();

        $req_extras->inited();

        \Yk\Hooks::regShutdownFunction(function (){
            \ykloger::debug('a request end');
        });
    }

    public static function _initMysqlConf(){
        \Mysql\Db::initConfig(get_conf_val('mysql'));

        \Yk\Hooks::regShutdownFunction(function (){
            //检测mysql事物是否已经释放
            if (($num = \Mysql\Db::getMasterTransNums()) > 0){
                \ykloger::error("mysql trans not release, mysql will be auto rollback", 0, ['num' => $num]);
                \Mysql\Db::freeMasterTransNotReleaseLinks();
            }
        });
    }

    public static function _initPlugins(\Yaf\Dispatcher $dispatcher) {
        $dispatcher->registerPlugin(new OplogPlugin());
    }


}