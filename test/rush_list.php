<?php

$number = 100000; // 签数
$limit = 5; //连号限制
$stock = $number; //剩余

$file = "rush_list.log";

$fd = fopen( $file, 'w' );

$fd or die( 'file error'. "\n" );

$amounts = [];

while( $stock > 0 ) {
	$amount = mt_rand( 1, $limit ); //连号数
	if( $amount > $stock ) {
		$amount = $stock;
	}
	$stock -= $amount; //减剩余
	
	$amounts[] = $amount;
	$amount_sum = array_sum( $amounts ); //尾号
	
	$write_status = fwrite( $fd, $amount. ",". $amount_sum."\n" );
	echo $write_status. ": ". $amount. ",". $amount_sum. "\n";
}

die( 'END'. "\n" );