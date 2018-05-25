<?php

namespace Yk;

class Config {

    private $inited = false;

    private $config = [];
    private $env = 0;
    private $err_debug = 0;

    private $timestamp = 0;
    private $microtime = 0;

    private $clientip = 'N/A';

    private $data = [];

    private function __construct(){}

    private function __clone(){}

    public static function getInstance(){
        static $instance = null;
        if ($instance == null){
            $instance = new self();
        }
        return $instance;
    }

    public function inited(){
        $this->inited = true;
    }

    public function load($top, $toml_file_name){
        $this->data[$top] = toml_parse_file(APP_BASE."/conf/toml/".$toml_file_name);
    }

    public function __get($key){
        return !isset($this->$key) ? $this->g($key) : $this->$key;
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

    public function g($key){
    	$sp = explode('.', $key);
    	if (!isset($this->{$sp[0]})){
            if(!isset($this->data[$sp[0]])){
                return null;
            }else{
                $vals = &$this->data[$sp[0]];
            }
    	}else{
            $vals = &$this->{$sp[0]};
        }

		$i = 1;
		while (isset($sp[$i])){
			$pos = $sp[$i];
			if (!isset($vals[$pos])){
				return null;
			}
			$vals = &$vals[$pos];
			$i++;
		}
		return $vals;
    }

    public function toArray(){
        return [
            'time' => [$this->timestamp, $this->mircotime],
            'setting' => ['env' => $this->env, 'clientip' => $this->clientip, 'err_debug' => $this->err_debug],
            'config' => $this->config,
            'data' => $this->data
        ];
    }

    public function __debuginfo(){
        return $this->toArray();
    }

    public function __set_state($arr){
        $obj = &\Yk\Config::getInstance();
        return $obj;
    }

    public function __tostring(){
        return serialize($this->toArray());
    }
}
