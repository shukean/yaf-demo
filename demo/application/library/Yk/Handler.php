<?php

namespace Yk;

class Handler{

    public static function trace($code, $message){

        $debugs = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 50);
        $log = [];

        $req_extras = \Yk\ReqExtras::getInstance();
        if ($req_extras->request_id){
            $log[] = "[flogid:".$req_extras->outer_log_id."] [reqid:".$req_extras->inner_req_id."]";
            $log[] = "[freqid:".$req_extras->outer_req_id."] [fplatid:".$req_extras->from_platform_id."]";
        }
        $log[] = '['.$code.']'.$message;
        $log[] = '------------------------------------------------------------';
        $log[] = sprintf("%-4s%-50s%-6s%-s", '#', 'File', 'Line', 'Method');
        $i = 1;
        $args = [];
        foreach ($debugs as $row){
            $method = (!empty($row['class']) ? $row['class'].$row['type'] : '').$row['function'];

            if ($method == 'Handler::trace') continue;

            $method .= '(';
		        foreach ($row['args'] as $k => $arg){
		            if (is_array($arg)){
		                $method .= 'array($'.$i.$k.'), ';
		                $args[$i.$k] = $arg;
		            }elseif (is_object($arg)){
		                $method .= 'object('.get_class($arg).'), ';
		            }else{
		                $method .= str_replace(APP_ROOT_PATH, '', $arg).', ';
		            }
		        }
	        $method .= 'EOF)';

            $row['file'] = isset($row['file']) ? $row['file'] : '';
            $row['line'] = isset($row['line']) ? $row['line'] : 0;

            $log[] = sprintf("%-4s%-50s%-6s%-s", $i, str_replace(APP_ROOT_PATH, '', $row['file']), $row['line'],
                $method);
            $i++;
        }
        $log[] = "\n".json_encode($args, JSON_UNESCAPED_UNICODE)."\n\n";

        error_log(implode("\n", $log), 3, get_conf_val('log.path').'/trace.'.date('YmdH'));
    }

    public static function errorHandler($error_code, $error_message, $err_file = '', $err_line = 0){

	    if (0 === error_reporting()){
	        return false;
	    }

	    self::trace($error_code, $error_message);

	    $err_code = yrandom(8, true);
	    \ykloger::error($error_message."[sc:$err_code]", $error_code);

	    if (Config::getInstance()->env != ENV_PRODUCT){
	        $data = [
	            'code' => 500,
	            'msg' => "[$error_code][sc:$err_code]".$error_message,
	            'data' => [
	                'file' => $err_file,
	                'line' => $err_line
	            ]
	        ];
	    }else{
	        $data = [
	            'code' => 500,
	            'message' => "服务异常,请重试; 多次异常,请联系我们![sc:$err_code]"
	        ];
	    }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	    exit;
    }


}