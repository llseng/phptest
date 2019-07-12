<?php

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/../src/TcpServer.php";


GLOBAL $splQueue,$readBuffer;

/**
 * 消息队列
 * @var sqlQueue
 */
$splQueue = new SplQueue();

/**
 * 消息读取缓存
 * @var array
 */
$readBuffer = [];

$server = new TcpServer("127.0.0.1", 1997);

//$server->debug = 1;

$server->setClientReadLen(5);

//日志事件
$server->on("log", function ($server ,$msg, $error){
	/*
	echo "\r\n---------start--------\r\n";
	echo $msg . "\r\n";
	var_export($error);
	echo "\r\n----------------------\r\n";
	*/
});

//主事件
$server->on("select", function ($server){
	//sleep(1);
});

//客户端发送事件
$server->on("write", function ($server, $sock, $msg){
	
	global $splQueue,$readBuffer;
	/*
	//报结束位置
	$endPos = strpos($msg, "\n");

	if( $endPos === false )
	{
		$readBuffer[(int)$sock] .= $msg;

	}else{
		
		//截取包
		$data = $readBuffer[(int)$sock] . substr($msg, 0, $endPos);

		$readBuffer[(int)$sock] = substr($msg, $endPos+1);

		//入队
		$splQueue->enqueue($data);

	}*/

	if( !isset($readBuffer[(int)$sock]) ) $readBuffer[(int)$sock] = "";

	$package = $readBuffer[(int)$sock] . $msg;

	var_dump($$readBuffer[(int)$sock],1000000);
	var_dump($msg,2000000);
	var_dump($package,3000000);

	while ( ($endPos = strpos($package, "\n")) !== false ){
		//截取包
		$data = trim(substr($package, 0, $endPos));

		$package = substr($package, $endPos+1);

		//入队
		if( $data ) $splQueue->enqueue($data);
	}

	$readBuffer[(int)$sock] = $package;

	var_dump($readBuffer,$splQueue);

});

$server->on("close",function ($server, $sock)
{
	var_dump($sock,"close");
});

//服务启动
$server->start();