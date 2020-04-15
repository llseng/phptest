<?php
/**
策略工厂测试
**/
error_reporting( E_ALL );
//支付策略
interface PayStrategy {
	//初始化配置
	public function __construct( $config );
	//二维码支付
	public function qr( $params );
	//网页支付
	public function h5( $params );
	//应用支付
	public function app( $params );
	
}

abstract class Pay implements PayStrategy {
	
	private $config ;
	
	public function __construct( $config ) {
		$this->config = $config;
	}
	//拼接
	abstract public function daelParams( $params );
	//二维码支付
	public function qr( $params ) {
		$this->daelParams( array_merge( $params, $this->config ) );
	}
	//网页支付
	public function h5( $params ) {
		$this->daelParams( array_merge( $params, $this->config ) );
	}
	//应用支付
	public function app( $params ) {
		$this->daelParams( array_merge( $params, $this->config ) );
	}
}

//支付工厂
class PayFactory {
	//绑定列表
	private $callables = [];
	
	//绑定支付类
	public function bind( $type, $callable ) {
		if( !is_callable( $callable ) && !($callable instanceof PayStrategy) ) {
			throw new \Exception( "callable type is not Callable or PayStrategy", 1 );
		}
		
		$this->callables[ $type ] = $callable;
	}
	
	//申请支付类
	public function apply( $type ): PayStrategy {
		if( !isset( $this->callables[ $type ] ) ) {
			throw new \Exception( "callable type is empty", 1 );
		}
		
		if( is_callable( $this->callables[ $type ] ) ) {
			return call_user_func( $this->callables[ $type ] );
		}
		
		return $this->callables[ $type ];
	}
}

//支付情况
class PayContext {
	protected $pay_factory;
	
	public function __construct( PayFactory $pay_factory ) {
		$this->pay_factory = $pay_factory;
	}
	
	//支付
	public function pay( $type, $mode, $params ) {
		
		$pay = $this->pay_factory->apply( $type );
		
		if( !method_exists( $pay, $mode ) ) {
			throw new \Exception( get_class( $pay ). "->". $mode." method is empty", 1 );
		}
		//支付
		return call_user_func( [$pay,$mode], $params );
		
	}
	
}

//微信支付
class WxPay extends Pay {
	public function daelParams( $params ) {
		echo static::class. " : ". join( "-wx-", $params );
	}
}

//支付宝
class ZfbPay extends Pay {
	public function daelParams( $params ) {
		echo static::class. " : ". join( "-zfb-", $params );
	}
}

//京东支付
class JdPay extends Pay {
	public function daelParams( $params ) {
		echo static::class. " : ". join( "-jd-", $params );
	}
}

//支付类工厂
$pay_factory = new PayFactory();
$pay_factory->bind( 'wx', function(){
	return new WxPay( ["pay" => "wx"] );
});
$pay_factory->bind( 'zfb', function(){
	return new ZfbPay( ["pay" => "zfb"] );
});
$pay_factory->bind( 'jd', function(){
	return new JdPay( ["pay" => "jd"] );
});
$pay_factory->bind( 'aa', function(){
	return ["pay" => "aa"];
});
//var_dump( $pay_factory->apply('wx') );die;

$params = ["oid" => '123456789', "pid" => '123456'];
$pay_type = 'wx';
$pay_method = 'app';
try{
	$pay_context = new PayContext( $pay_factory );
	$pay_context->pay( $pay_type, $pay_method, $params );
}catch( \Exception $e ) {
	echo $e->getMessage();
}


