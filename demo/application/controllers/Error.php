<?php

/**
 *  Copyright (c) 2013-2014 yky@yky.pw
 *  https://github.com/shukean/php-yaf-yk
 *  Error.php  下午8:40:25  UTF-8
 *
 *
 *  错误码区间
 *  0-200 表示正常
 *  201 - 699 表示框架严重异常
 *  700 - 799 表示框架一般异常
 *  800 -   表示接口异常
 *
 */

class ErrorController extends \Yk\Cntl{

    public function errorAction(\Exception $exception){

        //$debugs = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 50);
        //获取request的信息
        if ($exception->getCode() >= 700){
            \ykloger::info($exception->getLine(), $exception->getFile(), $exception->getMessage(), $exception->getCode());
        }elseif ($exception->getCode() > 200){
            $code = $exception->getCode();
            if ($code == YAF\ERR\NOTFOUND\MODULE
                || $code == YAF\ERR\NOTFOUND\CONTROLLER
                || $code == YAF\ERR\NOTFOUND\ACTION) {
                \ykloger::warn($exception->getMessage(), $exception->getCode());
            }else{
                \ykloger::error($exception->getMessage(), $exception->getCode());
            }
        }
        $this->setJson(700, str_replace(APP_ROOT_PATH, '', "[".$exception->getCode()."]".$exception->getMessage()), [
            'reqid' => \Yk\ReqExtras::getInstance()->inner_req_id,]);
    }

}
