<?php

require __DIR__ . '/init.php';
date_default_timezone_set('Asia/Chongqing');
//redis://[user]:[password]@[host]:[port]
$config = get_redis_config();

Resque::setBackend($config['REDIS_BACKEND'],$config['REDIS_BACKEND_DB'],$config['REDIS_BACKEND_PASSWORD']);

for ($i=0; $i < 5 ; $i++) { 
	$args = array(
	'time' => date('H:i:s'),
	'body' => "test ".date('H:i:s'),
	'badge' => 1,
	'sound' => 'default',
	'device_token' => 'xxxx'
	);
	$jobId = Resque::enqueue('aiba_ios_send', 'IOS_Send_Job', $args, true);
	echo "Queued job ".$jobId."\n\n";
}

