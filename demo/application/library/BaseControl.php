<?php

abstract class BaseControl extends \Yaf\Controller_Abstract{

    /**
     *
     * @var \Yaf\Application
     */
    protected $yaf = null;

    /**
     *
     * @var \Yaf\Dispatcher
     */
    protected $dispatcher = null;

    public function init(){
        $this->yaf = \Yaf\Application::app();
        $this->dispatcher = $this->yaf->getDispatcher();

        //关闭默认的view输出, 只启用response
        $this->dispatcher->autoRender(false);
        if (defined('__PHPUNIT_PHAR__')){
            //phpuint 下关闭response的输出
            $this->dispatcher->returnResponse(true);
        }
    }

    public function setJson($code, $msg, array $data = []){
        $response = [
            'code' => $code,
            'msg' => $msg,
            'reqid' => G_REQID,
        ];
        if (!empty($data)){
            $response['data'] = $data;
        }

        $this->setTextJson(json_encode($response, JSON_UNESCAPED_UNICODE));
    }

    public function setTextJson($json){
        $this->getResponse()->setbody($json);
    }

}