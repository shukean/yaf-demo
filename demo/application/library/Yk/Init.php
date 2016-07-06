<?php

namespace Yk;

define('APP_PATH', dirname(dirname(__DIR__)));
define('APP_ROOT_PATH', dirname(APP_PATH));
define('APP_NAME', basename(APP_ROOT_PATH));

define('ENV_DEBUG', 2);
define('ENV_PREVIEW', 1);
define('ENV_PRODUCT', 0);

define('ERR_DEBUG_ALL', 2);
define('ERR_DEBUG_ERROR', 1);
define('ERR_DEBUG_OFF', 0);

final class Init{

    public static function start($environ = null){

        static $_inited = false;

        if ($_inited){
            return $_inited;
        }

        $config_file = APP_ROOT_PATH.'/conf/app.ini';
        $_inited = $yaf = new \Yaf\Application($config_file, $environ);
        $app_config = Config::getInstance();

        $app_config->config = $config = $yaf->getConfig()->toArray();

        $app_config->err_debug = $config['debug'];
        if ($app_config->err_debug == ERR_DEBUG_ALL){
            ini_set('display_errors', 'On');
            error_reporting(E_ALL);
        }elseif ($app_config->err_debug == ERR_DEBUG_ERROR){
            error_reporting(E_ERROR);
        }else{
            error_reporting(0);
        }

        $app_config->env = $config['env_mode'];

		$offset = $config['timeoffset'];
		date_default_timezone_set('Etc/GMT'.($offset > 0 ? '-' : '+' ).abs($offset));

		list($mtime, $time) = explode(' ', microtime());
		$app_config->timestamp = $time;
		$app_config->microtime = $mtime;
		$app_config->clientip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'N/A';

		if (!defined('API_SAPI_CLI')){
		    \Yaf\Dispatcher::getInstance()->setErrorHandler(['Yk\Handler', 'errorHandler'], E_ALL);
		}

		\Yaf\Loader::import('Yk/ExtraFunc.php');
		register_shutdown_function(['Yk\Init', 'end']);

		$app_config->inited();

		return $yaf;
    }

    public static function end(){
        foreach (\Yk\Hooks::getShutdownFunctions() as $func){
            if (is_array($func)){
                if (count($func) == 2){
                    list($c, $f) = $func;
                    $c::$f();
                }
            }else{
                $func();
            }
        }
    }

}

