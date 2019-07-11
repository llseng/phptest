<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Monolog\Logger; 
use Monolog\Handler\StreamHandler; 
 
// 创建日志频道 
$log = new Logger('llseng'); 
$log->pushHandler(new StreamHandler('logs/test.log', Logger::DEBUG));
 
// 添加日志记录 
$log->addWarning('Foo'); 
$log->addError('Bar');
$log->addInfo("测试log功能",$_SERVER);