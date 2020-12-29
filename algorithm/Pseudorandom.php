<?php

/**
 * @Author: llseng
 * @Email:  1300904522@qq.com
 * @Date:   2020-10-06 18:23:55
 * @Last Modified by:   llseng
 * @Last Modified time: 2020-10-07 11:55:09
 */

// 伪随机算法
function pseudorandom( $seed, $max, $min = 0 ) {
    $seed = ( $seed * 9301 + 49297 ) % 233280;
    $rand = $seed / 233280;
    return round( $min + $rand * ($max - $min) );
}

$list = [];
$list2 = [];

for ($i=1; $i <= 100; $i++) { 
    $list[] = pseudorandom( $i + 38894699, 5 );
    $list2[] = ($i + 38894699) % 5;
}

echo json_encode( $list ). "\n";
echo json_encode( $list2 ). "\n";