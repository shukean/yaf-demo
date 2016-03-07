<?php

define('APP_PATH', dirname(__DIR__));
define('APP_ROOT_PATH', dirname(APP_PATH));
define('APP_LOG_PATH', APP_ROOT_PATH.'/../logs/sms');

final class Init{

    public static function start($config_id){

        static $_inited = false;

        if ($_inited){
            return $_inited;
        }

        $config_file = APP_ROOT_PATH.'/conf/app.ini';
        $_inited = $yaf = new Yaf\Application($config_file, $config_id);

        $config = $yaf->getConfig()->toArray();
		\Yaf\Registry::set('config', $config);

		$level = $config['debug'] ? ($config['debug'] == 1 ? E_ERROR : E_ALL) : 0;
		define('MAIN_DEBUG', $config['debug']);
		if (MAIN_DEBUG){
		    ini_set('display_errors', 'On');
		}
		error_reporting($level);

		$env = $config['debug_env'];
		define('APP_ENV_DEBUG', $env ? true : false);

		$pre_env = isset($config['pre_env']) ? $config['pre_env'] : 0;
		define('APP_ENV_PRE', $pre_env);

		list($mtime, $time) = explode(' ', microtime());
		define('TIMESTAMP', $time);
		define('MICROTIME', $mtime);

		$offset = $config['timeoffset'];
		date_default_timezone_set('Etc/GMT'.($offset > 0 ? '-' : '+' ).abs($offset));

		\Yaf\Registry::set('clientip', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'N/A');

		if (!defined('API_SAPI_CLI')){
		    Yaf\Dispatcher::getInstance()->setErrorHandler(['Handler', 'errorHandler'], E_ALL);
		}

	    !is_dir(APP_LOG_PATH.'/bqlog/') && mkdir(APP_LOG_PATH.'/bqlog/', 0755, true);
	    \Yaf\Loader::import('BqLogger/BqLogger.php');
		BqLogger::init([
		    'logFile' => APP_LOG_PATH.'/bqlog/',
		    'logLevel' => APP_ENV_DEBUG || APP_ENV_PRE ? 'debug' : 'info',
		]);
		\Yaf\Loader::import('ExtraFunc.php');

		//替换原有的view
		Yaf\Dispatcher::getInstance()->setView(new \JsonView());

		register_shutdown_function(['Init', 'end']);

		return $yaf;
    }

    public static function end(){
        //检测mysql事作是否已经释放
        if (($num = \Mysql\Db::getMasterTransNums()) > 0){
            bqlog_error(__LINE__, __FILE__, "mysql trans not release, mysql will be auto rollback", 0, ['num' => $num]);
            \Mysql\Db::freeMasterTransNotReleaseLinks();
        }

        bqlog_debug(__LINE__, __FILE__, "a request close", 0);
    }

}

