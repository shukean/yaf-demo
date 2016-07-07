<?php

class OplogPlugin extends \Yaf\Plugin_Abstract{

    private static $tmp;

    private function _gettime(){
        list($mtime, $time) = explode(' ', microtime());
        return date('Y-m-d H:i:s', $time).'.'.substr($mtime, 2);
    }

    private function uri_oplog($message){
        error_log($message."\n", 3, get_conf_val('log.path').'/oplog.'.date('YmdH'));
    }

    public function routerStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response){

        self::$tmp = yrandom(6);
        $req = \Yk\RequestExtras::getInstance();

        $content = sprintf("[logid:%s] [reqid:%s] [reqtime:%s] [reqip:%s] [io:%s] [req_key:%s] [uri:%s] [post:%s] [get:%s]",
            $req->from_log_id, $req->request_id, $this->_gettime(), \Yaf\Registry::get('clientip'),
            'in', self::$tmp, $request->getRequestUri(),
            json_encode($_POST, JSON_UNESCAPED_UNICODE), json_encode($_GET, JSON_UNESCAPED_UNICODE));

        $this->uri_oplog($content);
    }

    public function dispatchLoopShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response){

        $req = \Yk\RequestExtras::getInstance();
        $content = sprintf("[logid:%s] [reqid:%s] [reqtime:%s] [reqip:%s] [io:%s] [req_key:%s] [uri:%s] [response:%s]",
            $req->from_log_id, $req->request_id, $this->_gettime(), \Yaf\Registry::get('clientip'),
            'out', self::$tmp, $request->getRequestUri(),
            $response->getBody());

        $this->uri_oplog($content);
    }

}
