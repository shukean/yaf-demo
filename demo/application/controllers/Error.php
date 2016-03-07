<?php

/**
 *  Copyright (c) 2013-2014 yky@yky.pw
 *  https://github.com/shukean/php-yaf-yk
 *  Error.php  下午8:40:25  UTF-8
 */

class ErrorController extends BaseControl{

    public function errorAction(\Exception $exception){

        //$debugs = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 50);
        //获取request的信息
        if ($exception->getCode() >= 700){
            bqlog_info($exception->getLine(), $exception->getFile(), $exception->getMessage(), $exception->getCode());
        }elseif ($exception->getCode() > 200){
            $code = $exception->getCode();
            if ($code == YAF\ERR\NOTFOUND\MODULE
                || $code == YAF\ERR\NOTFOUND\CONTROLLER
                || $code == YAF\ERR\NOTFOUND\ACTION) {
                bqlog_warn($exception->getLine(), $exception->getFile(), $exception->getMessage(), $exception->getCode());
            }else{
                bqlog_error($exception->getLine(), $exception->getFile(), $exception->getMessage(), $exception->getCode());
            }
        }
        $
        $this->setJson(700, str_replace(APP_ROOT_PATH, '', "[".$exception->getCode()."]".$exception->getMessage()), [
            'reqid' => G_REQID,]);
    }

}
