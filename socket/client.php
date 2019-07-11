<?php
require_once __DIR__ . "/../vendor/autoload.php";

//脚本最大执行时间
set_time_limit(0);

use Monolog\Logger; 
use Monolog\Handler\StreamHandler; 

// 创建日志频道 
$log = new Logger('socketServer'); 
$log->pushHandler(new StreamHandler( __DIR__ . '/../logs/client/'.date("Ymd").'.log', Logger::DEBUG));

$ip = "127.0.0.1";
//$ip = gethostbyname("hc.com");
$port = 1997;
//$port = getservbyname("www", "tcp");

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or sock_err("socket_create");

$sockconnect = socket_connect($sock, $ip, $port) or sock_err("socket_connect");

//初次连接 发送连接消息
socket_write($sock, "GET / HTTP/1.1\r\nHost : hc.com\r\n\r\n");

fwrite(STDOUT, "str ");
while ( $str = fgets(STDIN) ) {
	var_dump(socket_write($sock, $str));
	$msg = "";
	//$msg = socket_read($sock, 1024);
	var_dump($msg = socket_read($sock, 1024));
	var_dump($msg);
	if( $msg === false ) break;

fwrite(STDOUT, "str ");
}

/*
//while (true) {

	$msg = socket_read($sock, 1024);
	//if($msg === false) break;

	var_dump($msg);
//}
*/
socket_close($sock);