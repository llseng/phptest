<?php
/**
 * swoole拓展并发 
 * 基于swoole协程
 */
class SwooleMany extends Many {
	//消息通道
	protected $chan;
	//主线程ID
	protected $mid = 0;
	//子线程ID列表
	protected $sid = [];

	public function __construct(int $cnum = 0) {
		if( !extension_loaded("swoole") ) {
			throw new \Exception("Please install swoole to expand, >=4.3.0", 1);
		}
		if( !version_compare(phpversion('swoole'), "4.3.0", ">=") ) {
			throw new \Exception("Swoole extended version is lower than 4.3.0", 1);
		}
		\Swoole\Runtime::enableCoroutine(true);
		parent::__construct($cnum);
	}

	public function getChan() {
		if( !$this->chan ) {
			$this->chan =new \Swoole\Coroutine\Channel( $this->getCnum() );
		}
		return $this->chan;
	}

	public function getMid(): int {
		return $this->mid;
	}

	public function getSid(): array {
		return $this->sid;
	}

	public function run() {
		$go = $this->getGo();
		//创建主协程 监听通道信息
		$this->mid = \Swoole\Coroutine::create(function($ser){
			$cnum = $ser->getCnum();
			$list = [];

			while ( $cnum-- ) {
				$list[] = $ser->getChan()->pop();
			}

			call_user_func_array($ser->getTo(), [$ser,$list]);
		},$this);
		//并发子协程
		$cnum = $this->getCnum();
		while ( $cnum-- ) {
			$this->sid[] = \Swoole\Coroutine::create(function ($ser){
				$ser->getChan()->push( call_user_func_array($ser->getGo(), $ser->getPar()) );
			},$this);
		}
	}
}