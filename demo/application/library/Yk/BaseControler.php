<?php

namespace Yk;

abstract class BaseControler extends \Yaf\Controller_Abstract{

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

    protected $request_extras = null;

    final public function init(){
        $this->yaf = \Yaf\Application::app();
        $this->dispatcher = $this->yaf->getDispatcher();
        $this->request_extras = \Yk\RequestExtras::getInstance();

        //关闭默认的view输出, 只启用response
        $this->dispatcher->autoRender(false);
        if (defined('__PHPUNIT_PHAR__')){
            //phpuint 下关闭response的输出
            $this->dispatcher->returnResponse(true);
        }

        //替代yaf的init
        if (method_exists($this, 'cInit')){
            $this->cInit();
        }
    }

    public function setJson($code, $msg, array $data = [], $xsrf_addition_key = NULL){
        $response = [
            'code' => $code,
            'msg' => $msg,
            'reqid' => $this->request_extras->request_id,
        ];
        if (!empty($data)){
            $response['data'] = $data;
        }
        if ($xsrf_addition_key && ($xsrf_token = \Xsrf::set($xsrf_addition_key)) != null){
            $response['_xsrf_token'] = $xsrf_token;
        }

        $this->setTextJson(json_encode($response, JSON_UNESCAPED_UNICODE));
    }

    public function setTextJson($json){
        $this->getResponse()->setbody($json);
    }

    public function xsrfCheck($xsrf_addition_key = NULL){
        $token = get_cookie_val('_xsrf_token');
        if (!empty($token)){
            if (\Xsrf::checkToken($token, $xsrf_addition_key) !== false){
                return ;
            }
        }
        throw new \Exception('request is not available in this context', 700);
    }

}