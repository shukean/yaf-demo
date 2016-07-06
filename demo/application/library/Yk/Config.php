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
    	    return null;
    	}
    	$vals = &$this->{$sp[0]};

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
}