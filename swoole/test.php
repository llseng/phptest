<?php
require_once  __DIR__ . "/../vendor/autoload.php";

use Llseng\Tool\Many;
use Llseng\Tool\Queue;

$many = new Many\SwooleMany(4);
$queue = new Queue\PhpQueue(10);

$a = 0;
while ( !$queue->isFull() ) {
	$queue->enQueue( ++$a );
}
var_dump($queue);
$many->go(function($ser,$que){
	var_dump( $ser->getMid() );
	$list = [];
	while ( count($que) ) {
		$list[] = $que->deQueue();
	}
	return $list;
	
},$queue);

$many->to(function($ser, $list){
	var_dump( $ser->getSid() );
	var_dump( $list );
});

$many->run();