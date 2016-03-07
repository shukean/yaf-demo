<?php

class BootStrap extends \Yaf\Bootstrap_Abstract{

    public static function _initConf(\Yaf\Dispatcher $dispatcher) {
        $g_logid = val('g_logid', VERIFY_String);
        $g_reqid = val('g_reqid', VERIFY_String);
        $g_platfrom = val('g_platfrom', VERIFY_String);

        define('G_LOGID', empty($g_logid) ? 'Undef'.yrandom(32) : $g_logid);
        define('G_REQID', empty($g_reqid) ? yrandom(32) : $g_reqid);
        define('G_PlATFROM', empty($g_platfrom) ? 'Undef' : $g_platfrom);

        \Mysql\Db::initConfig(getConfVal('mysql'));

        $dispatcher->registerPlugin(new OplogPlugin());

    }


}