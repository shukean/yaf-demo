<?php

/**
 * XSRF 的防止
 *
 * config的配置
 *
 * xsrf.enable = 1          #0不开启
 * xsrf.key = ''            #用于加密的key
 * xsrf.expire = 3600       #token有效期
 *
 * 使用方式
 *
 *
 */

class Xsrf{

    public static function set($addition_key = NULL){
        $conf = get_conf_val('xsrf');
        if (empty($conf) || !isset($conf['enable']) || !$conf['enable']){
            return null;
        }
        $key = isset($conf['key']) ? $conf['key'] : null;
        $expire = isset($conf['expire']) ? $conf['expire'] : null;
        if (empty($key) || is_null($expire) || $expire < 1){
            return null;
        }
        $str = sprintf("%s%s", yrandom(4), $addition_key ? $addition_key : '');
        return SecretRc4::encode($str, $key, $expire);
    }

    public static function checkToken($token, $addition_key = NULL){
        $conf = get_conf_val('xsrf');
        if (empty($conf) || !isset($conf['enable']) || !$conf['enable']){
            return null;
        }
        $key = isset($conf['key']) ? $conf['key'] : null;
        if (empty($key)){
            return null;
        }
        $str = SecretRc4::decode($token, $key);
        return $str && substr($str, 4) == ($addition_key ? $addition_key : '');
    }

}