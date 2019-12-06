<?php

use Monolog\Logger; 
use Monolog\Handler\StreamHandler; 

//去除空字符串
function trims($str)
{

    $search = [" ", "  ", "\r", "\n", "\t", "\0"];

    $replace = "";
    //
    return str_replace($search, $replace, $str);

}

//文件日志
function socketLog($file = "", $name = "socket")
{
    //去除空字符
    $file = trims($file);

    //替换相对目录
    $file = str_replace(["../", "./", ".log"], "", $file);

    //无文件名
    if(empty($file))
        $file = "log";

    $path = LOGS_PATH . DS . "socket";

    $file = $path . DS . date("Ymd") . DS . $file . ".log";

    if( !is_writable( dirname($file) ) && !mkdir( dirname($file), 0777, true) )
    {
        $file = $path . DS . date("Ymd") . "log";
    }
    
    //静态变量
    static $Logger = [];

    //不在单例列表
    if( empty( $Logger[$file] ) )
    {
        // 创建日志频道 
        $log = new Logger($name); 
        $log->pushHandler(new StreamHandler($file));
        //保存至单例列表
        $Logger[$file] = $log;
    }

    return $Logger[$file];
}