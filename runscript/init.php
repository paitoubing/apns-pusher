<?php
$files = array(
  __DIR__ . '/../../../../autoload.php',
  __DIR__ . '/../vendor/autoload.php',
);

$found = false;
foreach ($files as $file) {
	if (file_exists($file)) {
		require_once $file;
		break;
	}
}

if (!class_exists('Composer\Autoload\ClassLoader', false)) {
	die(
		'You need to set up the project dependencies using the following commands:' . PHP_EOL .
		'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
		'php composer.phar install' . PHP_EOL
	);
}

function get_redis_config (){
	return array(
		'REDIS_BACKEND' => '127.0.0.1:6379',
		'REDIS_BACKEND_DB' => 1,
		'REDIS_BACKEND_PASSWORD' => 'aiba001'
	);
}