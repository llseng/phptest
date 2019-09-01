<?php
require_once __DIR__ . "/../vendor/autoload.php";

var_dump( extension_loaded("swoole") );
var_dump( phpversion("swoole") );

//队列
global $SPL_QUEUE;
$SPL_QUEUE = new SplQueue();

function test($id = 0)
{
	global $SPL_QUEUE;

	$num = 0;

	while (count($SPL_QUEUE) < 100) {

		$data = [
			"id" => $id,
			"msg" => uniqid(),
		];

		$SPL_QUEUE[] = $data;

		$num++;

		usleep(500000);

	}

	return $num;

}

function enqueue($id)
{
	global $SPL_QUEUE;

	$data = [
			"id" => $id,
			"msg" => uniqid(),
		];

	$SPL_QUEUE[] = $data;
}

if( extension_loaded("swoole") )
{
	//延时函数底层替换
	Swoole\Runtime::enableCoroutine(true);

	global $chan;
	$chan = new chan(4);

	go(function(){
		global $chan,$SPL_QUEUE;

		$result = [];

		for ($i=0; $i < 4; $i++) { 
			$result[] = $chan->pop();
		}

		var_dump($result);

		var_dump(count($SPL_QUEUE));

		foreach ($SPL_QUEUE as $key => $value) {
			var_dump($key);
			var_export($value);
			echo ">---------- \r\n";
		}

	});

	for ($i=0; $i < 4; $i++) { 
		go(function(){
			global $chan;
			$cid = \Swoole\Coroutine::getCid();
			var_dump($cid);
			$num = test($cid);
			$chan->push([$cid=>$num]);
		});
	}

}else{
	test();

	foreach ($SPL_QUEUE as $key => $value) {
		var_dump($key);
		var_export($value);
		echo ">---------- \r\n";
	}
}