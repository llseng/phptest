<?php
/**
 * 
 * @authors llseng (lls_woods@qq.com)
 * @date    2019-12-06 12:02:46
 */

require_once __DIR__. "/../vendor/autoload.php";
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Workerman\Worker;
use Workerman\Lib\Timer;

//全局变量
global $Logger, $Queue;

//日志
$Logger = new Logger( basename( __FILE__ ) );
$StreamHandler = new StreamHandler( __DIR__. "/logs/". date('Ymd'). ".log" );
$Logger->pushHandler( $StreamHandler );

//队列
$Queue = new SplQueue();

//队列处理
function deal_queue() {
    //全局变量
    global $Logger, $Queue;
    
    //日志
    $log = new Logger( "deal_queue" );
    $handler = new StreamHandler( __DIR__. "/logs/deal_queue". date('Ymd'). ".log" );
    $log->pushHandler( $handler );

    $log->info( '----------START----------' );

    for( $i = 0; $i < 1000; $i++ ) {
        if( !count( $Queue ) ) break;
        // sleep(2);
        // file_get_contents( "http://127.0.0.1/test/sleep.php" );
        /**
         * 定时器内有堵塞时  整个程序将进入堵塞状态 
         */
        $log->info( 'data', $Queue->dequeue() );
    }

    $log->info( '-----------END-----------' );

    $timer_id = Timer::add( 60, "deal_queue", [], false );
    $Logger->info( 'set timer deal_queue' ,(array)$timer_id);
}

//服务
$worker = new Worker("text://127.0.0.1:8080");
$worker->count = 1;
$worker->name = "wkqueue_server";
$worker->onWorkerStart = function( $worker ) {
    //全局变量
    global $Logger, $Queue;
    $Logger->info( 'worker start '. $worker->name. $worker->id );

    $timer_id = Timer::add( 60, "deal_queue", [], false );
    $Logger->info( 'set timer deal_queue' ,(array)$timer_id);
};

//连接处理
$worker->onConnect = function( $link ) {
    //全局变量
    global $Logger, $Queue;
    
    $Logger->info( 'link connect' , (array)$link->id );
    $Logger->info( 'link connect worker' , (array)$link->worker );
    $link->send( "connect success" );
};
$worker->onMessage = function( $link, $data ) {
    //全局变量
    global $Logger, $Queue;
    
    $Logger->info( 'link send', (array)$link->id );
    $Logger->info( 'link send data', (array)$data );
    $link->send( "send success" );
    
    if( $data === "show" ) {
        $link->send( print_r( $Queue, true ) );
        return ;
    }
    
    if( $data === "get" ) {
        $link->send( print_r( $Queue->dequeue(), true ) );
        return ;
    }
    
    $json = json_decode( $data, true );
    $Logger->info( 'json decode', (array)$json );
    if( $json && isset( $json['id'] ) ) {
        $Logger->info( 'Queue en' );
        $Queue->enqueue( $json );
    }
    
};
$worker->onClose = function( $link ) {
    //全局变量
    global $Logger, $Queue;
    
    $Logger->info( 'link close', (array)$link->id );
    $link->send( "close success" );
};

Worker::runAll();