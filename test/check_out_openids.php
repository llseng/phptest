<?php
define('DS', DIRECTORY_SEPARATOR);
$table_name = "openids";
//创建表
$create_table = "CREATE TABLE $table_name (`id` int UNSIGNED NOT NULL AUTO_INCREMENT ,`openid` varchar(40) NOT NULL ,PRIMARY KEY (`id`),UNIQUE INDEX `openid` (`openid`) )";

//数据连接
$Mysqli = new Mysqli("localhost", "root", "123456", "test");
if( $Mysqli->connect_errno ) {
	exit("连接数据库失败[".$Mysqli->connect_errno."]: ".$Mysqli->connect_error);
}

if( $Mysqli->query("DROP TABLE IF EXISTS $table_name") !== true ) {
	exit("删除数据表失败\n");
}

if( $Mysqli->query($create_table) !== true ) {
	exit("新建数据表失败\n");
}

//数据目录
$dir = __DIR__ . DS ."..".DS."logs".DS."date_file";
//文件列表
$file_list = [];
$scandir = scandir($dir);
//检出所有文件
foreach ($scandir as $key => $val) {
	if( $val === '.' || $val === '..' ) continue;
	$tpath = $dir . DS . $val;
	if( !is_file($tpath) ) continue;
	$file_list[] = $tpath;
}
//正则模式
$reg = "/secret:c20190eab1137dea8527b5eae193cd95\s+\[\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}\]\[info\]\sarr:(\{[^\}]*\})/";

foreach ($file_list as $key => $val) {
	$file = $val;
	//文件内容
	$file_data = file_get_contents($file);
	$matches = []; //
	//匹配成功
	if(preg_match_all($reg, $file_data, $matches)) {
		foreach ($matches[1] as $k => $v) {
			$vdata = json_decode($v, 1);
			if( !$vdata || !isset($vdata['openid']) ) continue;
			//是否存在
			$result = $Mysqli->query("select * from ".$table_name." where openid='".$vdata['openid']."'");
			if( $result->num_rows ) {
				continue;
			}
			//插入
			$result = $Mysqli->query("insert into ".$table_name."(openid) values('".$vdata['openid']."')");
			if( !$result ) {
				echo file_put_contents('fail_data.log', $vdata['openid'], FILE_APPEND)."\n";
			}
		}
	}
}
