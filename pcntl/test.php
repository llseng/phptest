<?php

require_once __DIR__. "/../vendor/autoload.php";

$Logger = get_logger( basename( __FILE__ ) );

$Logger->info("--------------------START--------------------");

if( !extension_loaded("pcntl") ) {
    throw new \Exception("Please install pcntl to expand", 1);
}
if( !extension_loaded("posix") ) {
    throw new \Exception("Please install posix to expand", 1);
}

$wnum = 10000000;

$cnum = 10;

$ids = [];

$mpid = posix_getpid();

$pid;

$i = 0;
while ( $cnum- $i++ ) {
    
    $pid = \pcntl_fork();

    switch ( $pid ) {
        case -1:
            exit( "fork error" );
            break;
        case 0:
            $pid = posix_getpid();
            echo "client id ". $pid. "\n";
            $tmp_num = $wnum/$cnum;
            $log = get_logger( 'client', '_'.$pid );
            while ( $tmp_num ) {
                $log->info( $pid, [$tmp_num--]);
            }
            exit( 0 );
            break;

        default:
            $ids[] = $pid;
            break;
    }

}

echo "mpid:". $mpid. "\n";

foreach ($ids as $key => $val) {
    $s = pcntl_waitpid( $val, $status );
    echo $val. ":". $status. "\n";
    echo $s. "\n";
}