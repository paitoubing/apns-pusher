<?php
# QUEUE = aiba_android
date_default_timezone_set('Asia/Chongqing');
require 'init.php';
require dirname(__FILE__).'/../lib/baiduSDK/Channel.class.php';
require dirname(__FILE__).'/../bin/resque';


function androidSend($user_id = '',$send_array = array(),$app_key = '',$app_secret = '' ) {

	$channel = new Channel($app_key, $app_secret);

	# 推送消息到某个user，设置push_type = 1; 
	# 推送消息到一个tag中的全部user，设置push_type = 2;
	# 推送消息到该app中的全部user，设置push_type = 3;

	# 推送单播消息
	$push_type = 1;

	# 如果推送单播消息，需要指定user
	$optional[Channel::USER_ID] = $user_id;

	# 如果推送tag消息，需要指定tag_name
	# optional[Channel::TAG_NAME] = "xxxx";
		
	# 指定发到android设备
	$optional[Channel::DEVICE_TYPE] = 3;

	# 指定消息类型为通知 1-通知 0-透传
	$optional[Channel::MESSAGE_TYPE] = $send_array['message_type'];

	# 通知类型的内容必须按指定内容发送，示例如下：
	if(!empty($send_array['avatar'])) {
		$message = '{ 
			"title": "爱吧",
			"description": "'.$send_array['description'].'",
			"uid": "'.$send_array['uid'].'",
			"nickname": "'.$send_array['nickname'].'",
			"avatar": "'. $send_array['avatar'].'"
			}';
	} else {
		$message = '{ 
			"title": "爱吧",
			"description": "'.$send_array['description'].'"
			}';
	}
		
	$message_key = "msg_key";
	$ret = $channel->pushMessage ( $push_type, $message, $message_key, $optional ) ;
	if (false === $ret ) {
		return false;		
	} else {
		return true;
	}
}

class Android_Send_Job {

	CONST BAIDU_APP_KEY = 'xxxxx';
	CONST BAIDU_APP_SECRET = 'xxxx';

	public function perform(){
		$user_id = $this->args['user_id'];
		if( androidSend($user_id,$this->args,self::BAIDU_APP_KEY,self::BAIDU_APP_SECRET) == false){
			#push over 提醒代码
		    curl_setopt_array($ch = curl_init(), array(
		    CURLOPT_URL => "https://api.pushover.net/1/messages.json",
		    CURLOPT_POSTFIELDS => array(
		        "token" => "xxxx",
		        "user" => "xxx",
		        "message" => "{$user_id}:".date('Y-m-d H:i:s'),
		    )));
		    curl_exec($ch);
		    curl_close($ch);
      		throw new Exception("Send Error");
		}
	}
}