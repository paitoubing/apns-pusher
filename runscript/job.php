<?php
#QUEUE=aiba_ios_sendmessage,aiba_ios_send
define('SENDTIME', '::time::');
define('AUTH_KEY', 'xxxx');
define('APNS_SERVER', 'http://127.0.0.1:8888/send/');

function replace_send_time($body){
  $time = date('H:i:s');
  return str_replace(SENDTIME, $time, $body);
}

function post_without_wait($url, $params){
    foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
    $post_string = implode('&', $post_params);

    $parts=parse_url($url);

    $fp = @fsockopen($parts['host'],
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


class IOS_Send_Job{

	public function perform(){
    if(isset($this->args['body']) && !empty($this->args['body'])){
      $post_data['body'] = replace_send_time(trim($this->args['body']));
    }

    if(isset($this->args['sound']) && !empty($this->args['sound'])){
      $post_data['sound'] = $this->args['sound'];
    }
    
    $post_data['device_token'] = $this->args['device_token'];
    $post_data['badge'] = $this->args['badge'];

    $post_data['auth_token'] = md5($post_data['device_token'].AUTH_KEY);
    if(post_without_wait(APNS_SERVER,$post_data) == 0){
      
      #push over 提醒代码
      
      $body = isset($post_data['body']) ? trim($post_data['body']): '';
      $device_token = isset($post_data['device_token']) ? $post_data['device_token']: '';
      $badge = isset($post_data['badge']) ? $post_data['badge'] : 0;

      
      curl_setopt_array($ch = curl_init(), array(
      CURLOPT_URL => "https://api.pushover.net/1/messages.json",
      CURLOPT_POSTFIELDS => array(
        "token" => "xxxxx",
        "user" => "xxxx",
        "message" => "{$device_token}:{$body}:{$badge}",
      )));
      curl_exec($ch);
      curl_close($ch);

      throw new Exception("Send Error");
    }else{
      #发送完成,更新状态
    }
	}
}

class IOS_SendMessage_Job extends IOS_Send_Job {

}