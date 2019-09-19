<?php
require_once __DIR__ . "/../vendor/autoload.php";

/**
 * 并发测试类
 */
class cce
{
	
	//并发数
	public $num = 1;

	//数据数组
	public $data_list = [];

	//构造函数
	function __construct($num = 1)
	{
		$this->num = $num;
	}

	public function run($func, $args = [])
	{
		$num = $this->num;

		$chan = new chan($num);

		go(function($cce)use($chan,$num){
			while( $num > 0 )
			{
				$cce->data_list += $chan->pop();
				$num--;
			}

			var_dump($cce->data_list);
		},$this);

		while( $num > 0 )
		{
			go(function ()use($chan,$func, $args){
				$chan->push( call_user_func($func,$args) );
			});

			$num--;
		}
	}

}

$cce = new cce(5);

$cce->run(function(){
	static $num = 0;
	$num++;

	$data = [];

	for ($i=0; $i < 10; $i++) { 
		$data[] = rand(100,999);
	}

	return [$num=>$data];

});