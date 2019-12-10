<?php
require_once __DIR__. "/../vendor/autoload.php";
require_once __DIR__. "/workerman_MQ_CLIENT.php";

//workerman队列地址
$client = stream_socket_client( "tcp://127.0.0.1:8888", $errno, $errstr );

if( !$client ) {
    exit("ERROR: $errno - $errstr");
}

try {

    $mq = new workerman_mq_client( $client );

    // for ($i=0; $i < 100; $i++) { 
    //     // sleep(2);
        var_dump( $mq->push( str_repeat( "饕餮", 1000 ) ) );
    // }

    var_dump('len', $mq->len() );

    while( $len = $mq->len() ) {
        $msg = $mq->pull();
        var_dump($len);
        var_dump($msg);
        if( $msg === false ) {
            break;
        }
    }
    
} catch (\Exception $e) {
    echo $e->getMessage();
    echo "ERROR: $errno - $errstr\n";
}

fclose( $client );