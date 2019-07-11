<?php
/**
 * socket服务器
 */
require_once __DIR__ . "/../vendor/autoload.php";
//脚本最大执行时间
set_time_limit(0);

use Monolog\Logger; 
use Monolog\Handler\StreamHandler; 

// 创建日志频道 
$log = new Logger('socketServer'); 
$log->pushHandler(new StreamHandler( __DIR__ . '/../logs/server/'.date("Ymd").'.log', Logger::DEBUG));

$ip = "127.0.0.1";
$port = "1997";

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or sock_err("socket_create");

socket_set_nonblock($sock) or sock_err("socket_set_nonblock") ;

socket_bind($sock, $ip, $port) or sock_err("socket_bind");

socket_listen($sock, 4) or sock_err("socket_listen");

echo "server start ... \r\n";

$socks = [];
$socks[(int)$sock] = $sock;

$errnum = 5;

while ($errnum) {
	
	$read = $socks;
	$write = $except = [];

	$num = socket_select($read, $write, $except, 3);

	$log->addInfo("监听数据",[$read, $write, $except]);

	if( $num === false )
	{
		$log->addError("监听错误",[socket_strerror(socket_last_error())] );
		$errnum--;
		continue;
	}

	if( $num > 0 )
	{
		//遍历所有可读连接
		foreach ($read as $v) {
			//是服务器 连接
			if( $sock === $v )
			{
				//获取客户端连接
				$subSock = socket_accept($sock);
				$socks[(int)$subSock] = $subSock;

			}else{
				$start_time = microtime(true) * 1000;
				$log->addDebug("read start " . (int)$v);
				//$msg = socket_read($v, 1024, PHP_NORMAL_READ);
				$msg = socket_read($v, 1024);
				$end_time = microtime(true) * 1000;
				$log->addDebug("read end " . (int)$v,[$start_time, $end_time, round($end_time-$start_time,4)]);

				//返回数据表示收到
				$w = socket_write($v, 'OK');
				if( $w === false )
				{
					$log->addError( (int)$v . "发送错误",[socket_strerror(socket_last_error())] );
				}

				if( $msg === false )
				{
					$log->addError( (int)$v . "读取错误,关闭连接",[socket_strerror(socket_last_error())] );
					socket_close($v);
					unset($socks[(int)$v]);
				}
				$log->addInfo("客户端发送信息", [(int)$v , $msg]);
				if( $msg )
				{
					echo (int)$v . " : " . $msg . "\r\n";
				}
			}
		}

	}

	continue;

}

socket_close($sock);