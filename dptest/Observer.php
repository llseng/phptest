<?php
/**
观察者模式
**/

//被观察者
class Observed {
	//观察者们
	protected $observers = [];
	
	protected $status = 0;
	
	public function __construct() {
		
	}
	
	//连接观察者
	public function attach( \Observer $observer ) {
		$this->observers[] = $observer;
	}
	
	//通知所有观察者
	public function notifyAllObserver() {
		
		foreach( $this->observers as $observer ) {
			$observer->update();
		}
	}
	
	//设置状态
	public function setStatus( $status ) {
		$this->status = $status;
		$this->notifyAllObserver();
	}
	
	//获取状态
	public function getStatus() {
		return $this->status;
	}
}

//观察者
class Observer {
	
	private $observed;
	
	public function __construct( \Observed $observed ) {
		$this->observed = $observed;
		$this->observed->attach( $this );
	}
	
	public function update() {
		echo static::class. ": ". get_class( $this->observed). " status ". $this->observed->getStatus(). "\n";
	}
	
}

//o1
class O1 extends Observer {
	
}

//o1
class O2 extends Observer {
	
}

//o1
class O3 extends Observer {
	
}

$observed = new Observed( );
$observed->attach( new O1( $observed ) );
$observed->attach( new O2( $observed ) );
$observed->attach( new O3( $observed ) );

$observed->setStatus( 12 );
echo "\n";
$observed->setStatus( 13 );
echo "\n";
$observed->setStatus( 9 );
echo "\n";
$observed->setStatus( 6 );