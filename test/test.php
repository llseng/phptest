<?php
require_once __DIR__ . '/../vendor/autoload.php';
var_dump(range(0, 10));

function xrange($start, $limit, $step = 1)
{
	var_dump($start,$limit);
	if( $start < $limit )
	{
		if( $step <= 0 ) return; 

		for ($i=$start; $i+$step < $limit; $i+=$step) { 
			var_dump($i);
			yield $i;
		}

	}else{
		if( $step <= 0 ) return; 
		
		for ($i=$start; $i+$step > $limit; $i-=$step) { 
			var_dump($i);
			yield $i;
		}
	}

}

$list = xrange(0,10);

var_dump($list);