<?php

require_once __DIR__ . "/../../vendor/autoload.php";

require_once __DIR__ . "/../src/TcpServer.php";

$server = new TcpServer("127.0.0.1", 1997);

$server->on("write", function ($server, $sock, $msg){

	socket_write($sock, "ok");
	var_dump($msg);

	$msg = trim($msg);

	if( $msg === "quit" )
		$server->close_client($sock);

});

$server->start();