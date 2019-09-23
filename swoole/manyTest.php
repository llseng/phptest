<?php
require_once 'Many.php';
require_once 'SwooleMany.php';

$many = new SwooleMany(5);
$queue = new SplQueue();

$a = 0;
$i = 10;
while ( $i-- ) {
	if( $i === 5 ) 
		$queue->enqueue( "https://carttadmin.yqwyx.xyz/Interface/test/test1.php" );
	else
		$queue->enqueue( "http://carttadmin.yqwyx.xyz/Interface/test/test1.php" );
}

$many->go(function($ser,$que){
	//var_dump( $ser->getMid() );
	$list = [];
	$n = 0;
	//var_dump(count( $que ));
	while ( count($que) ) {
		//$list[] = $que->dequeue();
		$n++;
		try{
			var_dump( file_get_contents( $que->dequeue() ) );
		}catch(\Exception $e) {
			var_dump($e);
		}
		/*$client = new \Swoole\Coroutine\Http\Client('carttadmin.yqwyx.xyz',80);
		$client->get('/Interface/test/test1.php');
		var_dump($client->body);
		var_dump($client->statusCode);
		$client->close();*/
	}
	return $n;
},$queue);

$many->to(function($ser, $list){
	var_dump( $ser->getSid() );
	var_dump( $list );
});

$many->run();