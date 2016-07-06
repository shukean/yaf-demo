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
    $params['CUSTOM_LINE'] = $line; $params['CUSTOM_FILE'] = $file;
    return \ykloger::warn($msg, $errno, $params);
}

function mysql_log_error($line, $file, $msg, $errno = 0, $params = []){
    $params['CUSTOM_LINE'] = $line; $params['CUSTOM_FILE'] = $file;
    return \ykloger::error($msg, $errno, $params);
}

function mysql_log_info($line, $file, $msg, $errno = 0, $params = []){
    $params['CUSTOM_LINE'] = $line; $params['CUSTOM_FILE'] = $file;
    return \ykloger::info($msg, $errno, $params);
}

function mysql_log_debug($line, $file, $msg, $errno = 0, $params = []){
    $params['CUSTOM_LINE'] = $line; $params['CUSTOM_FILE'] = $file;
    return \ykloger::debug($msg, $errno, $params);
}
//----------mysql lib --------- //

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
    return Yk\Config::getInstance()->g('config.'.$key_str);
}






