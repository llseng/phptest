<?php

$file = "rush_list.log";

$amounts = file( $file );
$number = array_sum( $amounts );
$cont = count( $amounts );
// 中奖签
$winning = mt_rand( 1, $number );
//$winning = 79706;

$start_key = 0;
$end_key = $cont;
$now_key = 0;

$li = '';
$winning_li = '';
$temp_c = 0;

do{
	
	$li = trim( $amounts[ $now_key ] );
	$lis = explode( ',', $li );
	
	if( $lis[1] < $winning ) {
		//中奖签比当前签大 当前签设置为起始
		$start_key = $now_key;
	}else{
		//中奖签比当前签之中
		if( $lis[0] > ($lis[1] - $winning) ) {
			$winning_li = $li;
			break;
		}
		//中奖签比当前签小 当前签设置为结束
		$end_key = $now_key;
	}
	
	$diff_cont = $end_key - $start_key;
	$now_key = $end_key - round( $diff_cont / 2 );
	
	$temp_c++;
	//if( $temp_c > $cont ) { 
	if( $temp_c > 32 ) { //2的32次方 
		break;
	}
}while( empty( $winning_li ) );

var_dump( $temp_c );
var_dump( $cont, $winning, $lis, $winning_li );