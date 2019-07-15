<?php
require_once __DIR__ . '/../vendor/autoload.php';

var_dump(socketLog("test")->info("测试", $_SERVER));