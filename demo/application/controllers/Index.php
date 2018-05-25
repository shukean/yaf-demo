<?php

class IndexController extends \Yk\Cntl{

    public function cInit(){
        // ("Inited will be call before action");
    }

    public function indexAction(){

        $input = vals([
            ['k1', VERIFY_Bool, false, 'Invaild K1'],
            ['k2', VERIFY_DateStr, false],
        ]);

        # get a param
        $args = val('k3', VERIFY_String, false);

        # toml
        $config = \Yk\Config::getInstance();
        $config->load('demo', 'test.toml');
        #$this->setVal('config', $config->toArray());
        $this->setVal('name', get_toml_val('demo.owner.name'));

        $this->setVal('path', [
            'APP_NAME' => APP_NAME,
            'APP_PATH' => APP_PATH,
            'APP_ROOT_PATH' => APP_ROOT_PATH,
            'APP_BASE' => APP_BASE
        ]);
        $this->setJson(0, 'hello world !');
    }

    public function xsrfAction(){

        $_COOKIE['_xsrf_token'] = 'b2459blY+BhW66C6Xzg+NMEQLB';

        $this->xsrfCheck(true);

        $this->setJson(0, 'success', [], true);
    }

}
