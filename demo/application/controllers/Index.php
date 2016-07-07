<?php

class IndexController extends \Yk\BaseControler{

    public function cInit(){
        // ("Inited will be call before action");
    }

    public function indexAction(){

        $input = vals([
            ['k1', VERIFY_Bool, false, 'Invaild K1'],
            ['k2', VERIFY_DateStr, false],
        ]);

        $args = val('k3', VERIFY_String, false);

        $this->setJson(0, 'hello world !');
    }

    public function xsrfAction(){

        $_COOKIE['_xsrf_token'] = 'b2459blY+BhW66C6Xzg+NMEQLB';

        $this->xsrfCheck(true);

        $this->setJson(0, 'success', [], true);
    }

}
