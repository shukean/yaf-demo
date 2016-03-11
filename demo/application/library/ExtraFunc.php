<?php

if (defined('APP_LOADED_EXTRA_FUNC')){
    return;
}

define('APP_LOADED_EXTRA_FUNC', 1);

define('ARG_INPUT_GET', 1);
define('ARG_INPUT_POST', 2);
define('ARG_INPUT_COOKIE', 4);
define('ARG_INPUT_YAF_PARAMS', 8);
define('ARG_INPUT_REQUEST', 3);

define('VERIFY_TinyInt', 'vfTinyInt');
define('VERIFY_SmallInt', 'vfSmallInt');
define('VERIFY_MediumInt', 'vfMediumInt');
define('VERIFY_Int', 'vfInt');
define('VERIFY_Id', 'vfId');
define('VERIFY_Numeric', 'vfNumeric');
define('VERIFY_DateStr', 'vfDateStr');
define('VERIFY_Timestamp', 'vfTimestamp');
define('VERIFY_String', 'vfString');
define('VERIFY_Location', 'vfLocation');
define('VERIFY_Mobile', 'vfMobile');
define('VERIFY_Phone', 'vfPhone');
define('VERIFY_RMB', 'vfRMB');
define('VERIFY_Url', 'vfUrl');
define('VERIFY_Email', 'vfEmail');
define('VERIFY_Empty', 'vfEmpty');
define('VERIFY_NoEmpty', 'vfNoEmpty');
define('VERIFY_Json', 'vfJson');
define('VERIFY_Array', 'vfArray');
define('VERIFY_Set', 'vfSet');
define('VERIFY_Bool', 'vfBool');

//----------mysql lib --------- //
function mysql_log_warn($line, $file, $msg, $errno = 0, $params = []){
    return bqlog_warn($file, $line, $msg, $errno, $params);
}

function mysql_log_error($line, $file, $msg, $errno = 0, $params = []){
    return bqlog_error($file, $line, $msg, $errno, $params);
}

function mysql_log_info($line, $file, $msg, $errno = 0, $params = []){
    return bqlog_info($file, $line, $msg, $errno, $params);
}

function mysql_log_debug($line, $file, $msg, $errno = 0, $params = []){
    return bqlog_debug($file, $line, $msg, $errno, $params);
}
//----------mysql lib --------- //


//----------bqlog lib --------- //
function bqlog($type, $line, $file, $msg, $code=0, array $params=[]){
    $params['CUSTOM_LINE'] = $line;
    $params['CUSTOM_FILE'] = $file;
    $params['G_REQID'] = defined('G_REQID') ? G_REQID : 'undef';
    $params['G_LOGID'] = defined('G_LOGID') ? G_LOGID : 'undef';
    $params['G_PlATFROM'] = defined('G_PlATFROM') ? G_PlATFROM : 'undef';

    return BqLogger::$type($msg, $code, $params);
}

function bqlog_debug($line, $file, $msg, $code=0, array $params=[]){
    return bqlog('debug', $line, $file, $msg, $code, $params);
}

function bqlog_info($line, $file, $msg, $code=0, array $params=[]){
    return bqlog('info', $line, $file, $msg, $code, $params);
}

function bqlog_warn($line, $file, $msg, $code=0, array $params=[]){
    return bqlog('warn', $line, $file, $msg, $code, $params);
}

function bqlog_error($line, $file, $msg, $code=0, array $params=[]){
    return bqlog('error', $line, $file, $msg, $code, $params);
}
//----------bqlog lib --------- //


//----------verify lib --------- //
function val($key, $vf_func, $need=false, $invalid_msg=null, array $args=[], $type=INPUT_REQUEST){
    return Verify\Get::value($key, $vf_func, $need, $invalid_msg, $args=[], $type);
}

function vals(array $keys){
    return Verify\Get::values($keys);
}
//----------verify lib --------- //


function yrandom($len, $isnumeric = false){
    $seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $isnumeric ? 10 : 35);
    if (!$isnumeric) {
        $seed .= 'zZ'.strtoupper($seed);
    }else{
        $seed .= '0123456789';
    }

    $max = strlen($seed) - 1;
    $hash = '';
    while ($len-- > 0){
        $hash .= $seed[mt_rand(0, $max)];
    }
    return $hash;
}

function getConfVal($key_str){
    $config = \Yaf\Registry::get('config');
    foreach (explode('.', $key_str) as $k){
        if (array_key_exists($k, $config)){
            $config = $config[$k];
        }
    }
    return $config;
}

function uri_oplog($message){
    error_log($message."\n", 3, APP_LOG_PATH.'/oplog/'.date('YmdH'));
}





