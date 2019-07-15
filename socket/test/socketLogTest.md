socketLogTest.md
=====

### socket日志与file日志性能测试
+ socket日志(站点与socket交互,由socket服务来记录日志)
+ file日志(站点直接打开日志文件记录日志)

### 源码
#### [TcpServer.php](../src/TcpServer.php)
+ 在php socket函数库基础上封装
+ 设置事件回调
#### [TcpClient.php](../src/TcpClient.php)
+ 在php socket函数库基础上封装
#### [server.php](./server.php)
+ 监听客户段连接
+ 记录客户端交互数据,保存至队列
+ 取出队列信息,记录至文件
```
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



//tcp服务器
$server = new TcpServer("127.0.0.1", 1997, 1024);

//
//$server->debug = 1;

//日志事件
$server->on("log", function ($server ,$msg, $error){

});

//主事件
$server->on("select", function ($server){
	
});

//客户端发送事件
$server->on("write", function ($server, $sock, $msg){
	
});

//客户连接断开事件
$server->on("close",function ($server, $sock)
{

});

//服务启动
$server->start();
```
#### [client.php](./client.php)
+ 发送socket日志
+ 本地file日志
```
<?php

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/../src/TcpClient.php";

//启用socket日志
$testS = 1;

$data = [];

$file = ['test1',"test2","test3","test4"];
$pid = getmypid();

$data['file'] = $file[rand(0, count($file)-1)];
$data['name'] = $pid;
$data['msg'] = microtime(true);
$data['context'] = $_SERVER;

if( $testS )
{
	//
	$client = new TcpClient("127.0.0.1", 1997);

	$readBuffer = '';
	/*
	$msg = $client->read();
	if( $msg === false )
	{
		var_dump($client->getError());
		$client->close();
		exit;
	}*/
	$client->write(json_encode($data) . "\n");

	$client->close();

}else{


	if( socketLog($data['file'], $data['name'])->info($data['msg'], $data['context']) )
	{
		echo "Write Log OK.\r\n";
	}else{
		echo "Write Log FAIL.\r\n";
	}


}
```
### 测试数据
#### 测试工具
+ Apache2.4
+ ab.exe
+ ab.exe -n 1000 -c 100 http://localhost/suv/socket/test/client.php
#### 测试结果
+ socket日志
> 每秒请求数据再 180 ~ 200 左右浮动
```
Server Software:        Apache/2.4.23
Server Hostname:        localhost
Server Port:            80

Document Path:          /suv/socket/test/client.php
Document Length:        0 bytes

Concurrency Level:      100
Time taken for tests:   5.199 seconds
Complete requests:      1000
Failed requests:        0
Total transferred:      222000 bytes
HTML transferred:       0 bytes
Requests per second:    192.35 [#/sec] (mean)
Time per request:       519.876 [ms] (mean)
Time per request:       5.199 [ms] (mean, across all concurrent requests)
Transfer rate:          41.70 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   1.6      0      16
Processing:     0  500 1417.5     16    5183
Waiting:        0  500 1417.6     16    5183
Total:          0  501 1417.4     16    5183

Percentage of the requests served within a certain time (ms)
  50%     16
  66%     16
  75%     31
  80%     47
  90%   1014
  95%   5136
  98%   5152
  99%   5168
 100%   5183 (longest request)
```
+ file日志
> 每秒请求数据再 300 ~ 400 左右浮动
```
Server Software:        Apache/2.4.23
Server Hostname:        localhost
Server Port:            80

Document Path:          /suv/socket/test/client.php
Document Length:        15 bytes

Concurrency Level:      100
Time taken for tests:   2.488 seconds
Complete requests:      1000
Failed requests:        0
Total transferred:      218000 bytes
HTML transferred:       15000 bytes
Requests per second:    401.90 [#/sec] (mean)
Time per request:       248.815 [ms] (mean)
Time per request:       2.488 [ms] (mean, across all concurrent requests)
Transfer rate:          85.56 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    1   2.9      0      16
Processing:     0  193 124.9    188    1124
Waiting:        0  116 141.3     94    1108
Total:          0  194 124.8    188    1124

Percentage of the requests served within a certain time (ms)
  50%    188
  66%    188
  75%    188
  80%    188
  90%    203
  95%    203
  98%   1020
  99%   1020
 100%   1124 (longest request)
```
### 总结
+ 直接使用日志文件的效率 比 转发至socket服务处理的效率要高
+ 虽然直接使用日志文件的文件资源句柄多,但使用socket连接的消耗更大