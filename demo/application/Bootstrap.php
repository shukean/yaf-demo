<?php

class BootStrap extends \Yaf\Bootstrap_Abstract{

    public static function _initYkLogConf(){
        \ykloger::init([
            'logFile' => getConfVal('log.path').'/'.getConfVal('log.ykloger.pre'),
            'logLevel' => \Yk\Config::getInstance()->env == ENV_PRODUCT ? YKLOGER_LEVEL_INFO : YKLOGER_LEVEL_DEBUG
        ]);

    }

    public static function _initRequestExtra(){
        $g_logid = val('g_logid', VERIFY_String);
        $g_reqid = val('g_reqid', VERIFY_String);
        $g_platform = val('g_platform', VERIFY_String);

        $req_extra = \Yk\RequestExtras::getInstance();
        if ($g_logid){
            $req_extra->from_logid_id = $g_logid;
        }
        if ($g_reqid){
            $req_extra->from_reqid_id = $g_reqid;
        }
        if ($g_platform){
            $req_extra->from_platform_id = $g_platform;
        }
        $req_extra->request_id = \ykloger::getRequestId();

        $req_extra->inited();

        \Yk\Hooks::regShutdownFunction(function (){
            \ykloger::debug('a request end');
        });
    }

    public static function _initMysqlConf(){
        \Mysql\Db::initConfig(getConfVal('mysql'));

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