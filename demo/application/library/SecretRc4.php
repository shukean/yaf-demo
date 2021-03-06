<?php

class SecretRc4{

	private static $_encode = 1;
	private static $_decode = 0;

	public static function encode($str, $key = null, $ttl = 0){
		if ($key == null) {
			$key = get_conf_val('secret.rc4.key');
		}
		return self::rc4($str, self::$_encode, $key, $ttl);
	}

	public static function decode($str, $key = null){
		if ($key == null) {
			$key = get_conf_val('secret.rc4.key');
		}
		return self::rc4($str, self::$_decode, $key);
	}

	//RC4
	private static function rc4($str, $type, $key, $ttl = 0){
		$rndkeylen = 6;
		$key = md5($key);
		if ($type == self::$_decode) {
			$rndkey = substr($str, 0, 6);
			$str = base64_decode(substr($str, 6));
		}else{
			$rndkey = substr(md5(microtime()), 6, 6);
			//加上有效期
			$str = sprintf("%010d", $ttl ? (intval($ttl) + time()) : 0).$str;
		}

		$md5rndkey = md5(substr($key, 0, 16)).md5(substr($key, 16, 16)).md5($rndkey);
		$len = strlen($md5rndkey);

		$box = [];
		$rndbox = [];
		for ($i=0; $i<256; $i++){
			$box[$i] = $i;
			$rndbox[$i] = ord($md5rndkey[$i % $len]);
		}

		for($j = $i=0; $i<256; $i++){
			$j = ($j + $box[$i] + $rndbox[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		$result = '';
		$strlen = strlen($str);
		for ($j = $i = 0; $i<$strlen; $i++){
		    $ti = $i % 256;
			$j = ($j + $box[$ti] + $key[$i%32]) % 256;
			$tmp = $box[$ti];
			$box[$ti] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($str[$i]) ^ (($box[$ti] + $box[$j]) % 256));
		}

		if ($type == self::$_encode) {
			$result = $rndkey.str_replace('=', '', base64_encode($result));
		}else{
			$expiry = substr($result, 0, 10);
			if ($expiry > 0 && $expiry < time()) {
				$result = null;
			}else{
				$result = substr($result, 10);
			}
		}

		return $result;
	}
}
