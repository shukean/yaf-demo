<?php


class JsonView implements \Yaf\View_Interface{

    private $_tpl_vars;
    private $_force_object = 0;

    public function setJsonForceObject(){
        $this->_force_object = 1;
    }

    public function assign($name, $value = ''){
        if (is_array($name)){
            foreach ($name as $k => $v){
                $this->_tpl_vars[$k] = $v;
            }
        }else{
            $this->_tpl_vars[$name] = $value;
        }
    }

    public function display($tpl, $tpl_vars = null){
        echo $this->render($tpl, $tpl_vars);
    }

    public function render($tpl, $tpl_vars = null){
        if (!empty($tpl_vars)){
            $this->assign($tpl_vars);
        }
        return json_encode($this->_tpl_vars, JSON_UNESCAPED_UNICODE
            | ($this->_force_object ? JSON_FORCE_OBJECT : 0));
    }

    public function getScriptPath(){
        //
    }

    public function setScriptPath($template_dir){
        //
    }

    public function reSetData(){
        $this->_tpl_vars = [];
    }

    public function getData(){
        return $this->_tpl_vars;
    }
}