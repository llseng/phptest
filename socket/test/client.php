<?php

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/../src/TcpClient.php";

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


