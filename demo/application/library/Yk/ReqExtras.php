<?php

namespace Yk;

class ReqExtras{

    private $inited = false;

    private $inner_req_id = '';
    private $outer_platform_id = '';
    private $outer_log_id = '';
    private $outer_req_id = '';

    private function __construct(){}

    private function __clone(){}

    public static function getInstance(){
        static $instance = null;
        if ($instance == null){
            $instance = new self();
        }
        return $instance;
    }

    public function __get($key){
        return !isset($this->$key) ? 'N/A' : $this->$key;
    }

    public function __set($key, $val){
        if ($this->inited){
            throw new \Exception('Config inited, update fail');
        }
        if (!isset($this->{$key})){
            throw new \Exception('Config not found variable name :'. $key);
        }
        $this->{$key} = $val;
    }

    public function inited(){
        $this->inited = true;
    }

}