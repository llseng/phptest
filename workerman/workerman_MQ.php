<?php
/**
 * 消息队列
 * @authors llseng (lls_woods@qq.com)
 * @date    2019-12-06 20:47:34
 */

require_once __DIR__ . "/../vendor/autoload.php";
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Workerman\Worker;
use Workerman\Lib\Timer;

global $Logger, $Queues, $now_date;
//内存限制
ini_set( 'memory_limit', '512M' );
//心跳时间
define("TIMEOUT_TIME", 20);

//日志
$Logger = new Logger( basename( __FILE__ ) );

$now_date = date('YmdH00');
$StreamHandler = new StreamHandler( __DIR__. "/logs/MQ". $now_date. ".log" );

$Logger->pushHandler( $StreamHandler );
//队列列表
$Queues = [];

//日志文件更替
function deal_handler() {
    global $Logger, $Queues, $now_date;
    //整点时间
    $tmp_date = date('YmdH00');
    if( $tmp_date != $now_date ) {
        //关闭当前日志文件句柄 ) 不然删除不了日志文件,提示文件被程序打开
        $Logger->close();
        //更新日志文件句柄
        $StreamHandler = new StreamHandler( __DIR__. "/logs/MQ". $tmp_date. ".log" );
        $Logger->setHandlers( [$StreamHandler] );
        $now_date = $tmp_date;
    }
    //时间区间
    $gap_time = 60*60;
    //距离下个整点秒数
    $diff_time = (strtotime( $tmp_date ) + $gap_time) - time();
    //重复
    Timer::add( $diff_time, "deal_handler", [], false);
}

Worker::$logFile = __DIR__. "/". "MQ.log";
$worker = new Worker( "text://127.0.0.1:8888" );
$worker->name = "MQ";
//进程启动
$worker->onWorkerStart = function( $worker ) {
    global $Logger, $Queues;

    $Logger->info( "WorkerMan Start ". $worker->name. "-". $worker->id );
    //心跳检测
    Timer::add( 10, function()use($worker){
        global $Logger, $Queues;
        $Logger->info( "Timer", [count( $worker->connections ), array_keys( $worker->connections )] );
        $now_time = time();
        foreach ($worker->connections as $id => $connection) {
            $diff_time = $now_time - $connection->last_send_time;
            if( $diff_time > TIMEOUT_TIME ) {
                $connection->close();
            }
        }
    });
    //日志文件更替
    deal_handler();
};

$worker->onConnect = function ( $link ) {
    global $Logger, $Queues;

    $Logger->info( "Link ". $link->id. "-". $link->getRemoteIp() . " Connect" );
    //最后消息时间
    $link->last_send_time = time();

    // $link->send( "success" );
};

$worker->onMessage = function ( $link, $data ) {
    global $Logger, $Queues;
    //最后消息时间
    $link->last_send_time = time();

    $Logger->info( "Link ". $link->id. " send", [$data] );
    //队列操命令
    $MQREG = "/^MQ_(\w+):(\w+)=([\s\S]+)$/";
    if( preg_match($MQREG, $data, $match) ) {
        if( !isset( $Queues[ $match[2] ] ) || ! $Queues[ $match[2] ] instanceof \SplQueue ) {
            $Queues[ $match[2] ] = new \SplQueue();
        }
        $Queue = $Queues[ $match[2] ];
        switch ( strtolower( $match[1] ) ) {
            case 'push':
                $Queue->enqueue( $match[3] );
                break;
            case 'pull':
                //有队列才获取数据
                if( count( $Queue ) ) {
                    $massage = $Queue->dequeue();
                    $link->send( "MQ_MSG:".$match[2]."=".$massage );
                    return ;
                }else{
                    $link->send( "fail" );
                    return ;
                }
                break;
            case 'len':
                $len = count( $Queue );
                $link->send( "MQ_MSG:".$match[2]."=".$len );
                return ;
                break;
            default:
                $link->send( "fail" );
                break;
        }
    }

    $link->send( "success" );
};

$worker->onClose = function ( $link ) {
    global $Logger, $Queues;

    $Logger->info( "Link ". $link->id ." close");
    $link->send( "success" );
};

$worker->onError = function ( $link, $code, $msg ) {
    global $Logger, $Queues;

    $Logger->info( "Link ". $link->id ." error", [$code, $msg]);
};

Worker::runAll();