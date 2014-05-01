<?php
function post_without_wait($url, $params)
{
    foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
    $post_string = implode('&', $post_params);

    $parts=parse_url($url);

    $fp = fsockopen($parts['host'],
        isset($parts['port'])?$parts['port']:80,
        $errno, $errstr, 30);

    $out = "POST ".$parts['path']." HTTP/1.1\r\n";
    $out.= "Host: ".$parts['host']."\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    if (isset($post_string)) $out.= $post_string;

    fwrite($fp, $out);
    $content = @fread($fp, 1024);
    @fclose($fp);

    if(!empty($content)){
    	preg_match('/true/', $content,$flag);
    	if(isset($flag[0]) && $flag[0] === 'true'){
    		return 1;
    	}else{
    		return 0;
    	}
    }
}

$post_data['body'] = '';
$post_data['device_token'] = 'xxxxxxxx';
$post_data['badge'] = 10;
define('AUTH_KEY', 'xxxxxx');
$post_data['auth_token'] = md5($post_data['device_token'].AUTH_KEY);

echo post_without_wait('http://127.0.0.1:8001/send/',$post_data); 



