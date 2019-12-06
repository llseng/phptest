<?php

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/../src/TcpServer.php";


GLOBAL $splQueue,$readBuffer;

/**
 * 消息队列
 * @var sqlQueue
 */
$splQueue = new SplQueue();

/**
 * 消息读取缓存
 * @var array
 */
$readBuffer = [];



//tcp服务器
$server = new TcpServer("127.0.0.1", 1997, 1024);

//$server->debug = 1;

//$server->setClientReadLen();

//日志事件
$server->on("log", function ($server ,$msg, $error){

    // echo "\r\n---------start--------\r\n";
    // echo $msg . "\r\n";
    // var_export($error);
    // echo "\r\n----------------------\r\n";
    socketLog("server", getmypid())->info($msg, $error);
});

//主事件
$server->on("select", function ($server){
    global $splQueue;

    $num = 100;

    while ( $num ) {
        
        if( !count($splQueue) )
        {
            break;
        }

        $data = json_decode( $splQueue->dequeue() , true);

        if( !$data ) continue;

        $file = isset( $data['file'] ) ? $data['file'] : '';
        $name = isset( $data['name'] ) ? $data['name'] : 'tcpServer';
        $msg = isset( $data['msg'] ) ? $data['msg'] : '';
        $context = isset( $data['context'] ) ? $data['context'] : [];

        if( socketLog($file, $name)->info($msg, $context) )
        {
            echo "Write Log OK.\r\n";
        }else{
            echo "Write Log FAIL.\r\n";
        }

        $num--;
    }

});

//客户端发送事件
$server->on("write", function ($server, $sock, $msg){
    
    global $splQueue,$readBuffer;
    /*
    //报结束位置
    $endPos = strpos($msg, "\n");

    if( $endPos === false )
    {
        $readBuffer[(int)$sock] .= $msg;

    }else{
        
        //截取包
        $data = $readBuffer[(int)$sock] . substr($msg, 0, $endPos);

        $readBuffer[(int)$sock] = substr($msg, $endPos+1);

        //入队
        $splQueue->enqueue($data);

    }*/

    if( !isset($readBuffer[(int)$sock]) ) $readBuffer[(int)$sock] = "";

    $package = $readBuffer[(int)$sock] . $msg;

    while ( ($endPos = strpos($package, "\n")) !== false ){
        //截取包
        $data = trim(substr($package, 0, $endPos));

        $package = substr($package, $endPos+1);

        //入队
        if( $data ) $splQueue->enqueue($data);
    }

    $readBuffer[(int)$sock] = $package;

    //var_dump($splQueue, $readBuffer);

});

//客户连接断开事件
$server->on("close",function ($server, $sock)
{
    var_dump($sock,"close");
});

//服务启动
$server->start();