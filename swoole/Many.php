<?php

/**
 * 并发抽象类
 */
abstract class Many {
	/**
	 * 默认并发数
	 */
	const MIN_CNUM = 1;
	const MAX_CNUM = 1000;

	//并发数
	protected $cnum;
	//并发执行结构
	protected $go;
	//收尾执行结构
	protected $to;
	//并发参数
	protected $par;

	/**
	 * @author llseng
	 * @date   2019-09-20
	 * @mail   1300904522@qq.com
	 * @link   http://www.whymust.xyz
	 * @param  int|integer            $cnum 并发数
	 * 构造函数
	 */
	public function  __construct(int $cnum = 0) {
		$this->setCnum( $cnum );
	}

	public function setCnum(int $cnum) {
		if( $cnum > static::MAX_CNUM )
		{
			$cnum = static::MAX_CNUM;
		}
		$this->cnum = $cnum;
	}

	public function getCnum(): int {
		if( !$this->cnum ) 
			$this->setCnum( static::MIN_CNUM );
		return $this->cnum;
	}

	public function go(callable $go, ...$par) {
		$this->go = $go;
		array_unshift($par, $this);
		$this->par = $par;
	}

	public function to(callable $to) {
		$this->to = $to;
	}

	public function getGo(): callable {
		if( !is_callable( $this->go ) ) {
			throw new \Exception('Concurrent executable structure not set, Many->go( callable, ...par).', 1);
		}
		return $this->go;
	}

	public function getTo(): callable {
		if( !is_callable( $this->to ) ) {
			$this->to = function ($ser, $list){};
		}
		return $this->to;
	}
	
	public function getPar(): array {
		return $this->par;
	}

	abstract public function run();
}