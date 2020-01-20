<?php
// 测试僵尸进程
require_once __DIR__. "/../vendor/autoload.php";

$Logger = get_logger( basename( __FILE__ ) );

$Logger->info("--------------------START--------------------");

if( !extension_loaded("pcntl") ) {
    throw new \Exception("Please install pcntl to expand", 1);
}
if( !extension_loaded("posix") ) {
    throw new \Exception("Please install posix to expand", 1);
}

$pid = \pcntl_fork();

switch ( $pid ) {
    case -1:
        exit( "fork fail" );
        break;
    
    case 0:
        $pid = \posix_getpid();
        $Logger->info( "client id $pid" );
        $i = 20;
        while ( $i-- ) {
            $Logger->info( "$i" );
            sleep( 1 );
        }
        break;
    
    default:
        echo $pid. "\n";
        break;
}

$status = 0;
$sid = pcntl_wait( $status, WNOHANG );
echo $sid. ":". $status. "\n";