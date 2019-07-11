<?php

/**

 * File name:client.php

 * 客户端代码

 * 

 * @author guisu.huang

 * @since 2012-04-11

 */

set_time_limit(0);

 

$host = "127.0.0.1";

$port = 2046;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)or die("Could not create  socket\n"); // 创建一个Socket

  

$connection = socket_connect($socket, $host, $port) or die("Could not connet server\n");    //  连接

socket_write($socket, "hello socket") or die("Write failed\n"); // 数据传送 向服务器发送消息
var_dump($socket);
//while ($buff = socket_read($socket, 1024, PHP_NORMAL_READ)) {
	$buff = socket_read($socket, 1024, PHP_NORMAL_READ);
    echo("Response was:" . $buff . "\n");
//}
sleep(1);
try{
	var_dump($buff = socket_read($socket, 1024, PHP_NORMAL_READ));
}catch(Exception $e)
{
	var_dump("已断开连接");
}

$file = fopen(__DIR__ . "/test.log", "a");
var_dump($file, (int)$file, fwrite($file, (microtime(true)*10000) . "\r\n"));
fclose($file);
socket_close($socket);