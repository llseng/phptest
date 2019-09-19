<?php

/**
 * 并发
 */
class many
{
	const DEFAUTL_CNUM = 4;
	
	//并发数
	protected $cnum;
	//并发函数
	protected $go;
	//收尾函数
	protected $to;
	//并发参数
	protected $par;

	//通道
	public $chan;
	//主线程ID
	public $mid;
	//子线程ID列表
	public $sids = [];

	public function __construct(int $cnum = 0) {
		if( !extension_loaded('swoole') ) {
			throw new \Exception('请开启swoole拓展', 1);
		}
		if( !version_compare(phpversion('swoole'), "4.3.0", '>=')  ) {
			throw new \Exception("swoole版本过低", 1);
		}
		$this->setCnum($cnum);
	}

	protected function setCnum(int $cnum) {
		$this->cnum = $cnum;
	}

	public function getCnum() {
		if( !$this->cnum ) {
			$this->setCnum( static::DEFAUTL_CNUM );
		}
		return $this->cnum;
	}

	//开始执行并返回队列数据
	public function start()
	{
		if( !is_callable( $this->go ) ) {
			throw new \Exception('未设置并发函数.[$ser->go(func)设置]', 1);
		}

		//并发数
		$cnum = $this->getCnum();

		//消息通道
		$this->chan = new \Swoole\Coroutine\Channel( $cnum );
		//主协程ID
		$this->mid = \Swoole\Coroutine::create(function($ser) {
			//通道数据
			$list = [];
			$cnum = $ser->getCnum();
			while ($cnum--) {
				$list[] = $ser->chan->pop();
			}
			call_user_func_array($ser->getTo(), [$ser, $list]);
		},$this);

		while ($cnum--) {
			$this->sids[] = \Swoole\Coroutine::create(function($ser) {
				$ser->chan->push( call_user_func_array($ser->getGo(), $ser->getPar()) );
			},$this);
		}

	}

	public function go(callable $func, ...$par)
	{
		$this->go = $func;
		array_unshift($par, $this);
		$this->par = $par;
	}

	public function getGo() {
		return $this->go;
	}

	public function getPar() {
		return $this->par;
	}

	public function to(callable $func) {
		$this->to = $func;
	}

	public function getTo() {
		if( !is_callable( $this->to ) ) {
			$this->to = function($ser, $list) { };
		}
		return $this->to;
	}

}

$many = new many(10);

$many->go(function ($server,$a,$b,$c){
	//var_dump($server,$a,$b,$c);
	static $num = 0;
	co::exec('sleep 1');
	return ++$num;
},1,2,3);

$many->to(function($ser, $list){
	var_dump($ser,$list);
});

$many->start();