<?php
require_once __DIR__ . '/../vendor/autoload.php';
if( !extension_loaded('swoole') ) {
	throw new Exception("请安装swoole", 1);
}
if( !version_compare(phpversion('swoole'), '4.3.0', '>=') ) {
	throw new Exception("版本过低 >=4.3.0", 1);
}

$host = '0.0.0.0';
$port = 88;

$serv =new \Swoole\WebSocket\Server($host, $port);

$serv->on('start', function($ser) {
	
});

$serv->on('open', function($ser, $req) {
	var_dump($req);
});

$serv->on('message', function($ser, $frame) {
	var_dump($frame);
	foreach ($ser->connections  as $key => $value) {
		var_dump($key,$value);
	}

	$ser->push($frame->fd, "pong");
});

$serv->start();
