<?php

/**
 * 服务类
 */
class TcpServer
{
	/**
	 * 获取可读最大错误数
	 */
	const MAX_SELECT_ERR_NUM = 5;

	/**
	 * 监听地址
	 * @var string
	 */
	private $_address;

	/**
	 * 监听端口
	 * @var string|int
	 */
	private $_port;

	/**
	 * 监听最大队列
	 * @var int
	 */
	private $_backlog;

	/**
	 * 监听堵塞时间/秒
	 * @var [type]
	 */
	private $_blockSec;

	/**
	 * 服务器socket连接
	 * @var resource
	 */
	private $_socket;

	/**
	 * 所有客户端连接
	 * @var resource[]
	 */
	private $_sockets = [];

	/**
	 * 获取可读错误式
	 * @var integer
	 */
	private $selectErr = 0;

	/**
	 * 事件函数
	 * @var array
	 */
	private $_events = [];

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @param  string                 $address ip地址
	 * @param  string|int               $port    监听端口
	 * @param  int            			$backlog 监听最大队列
	 */	
	public function __construct($address, $port, $backlog = 128, $blockSec = 0)
	{
		
		$this->setAddress($address);
		$this->setPort($port);
		$this->setBacklog($backlog);
		$this->setBlockSec($blockSec);

		$this->_create();

	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @param  string                 $address 地址
	 * 设置监听地址
	 */
	public function setAddress($address)
	{
		$this->_address = $address;
	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @param  string|int                 $port 端口
	 * 设置监听端口
	 */
	public function setPort($port)
	{
		$this->_port = $port;
	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @param  int                 $backlog  监听最大队列
	 */
	public function setBacklog($backlog)
	{
		$this->_backlog = $backlog;
	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @param  int|null                 $blockSec 堵塞时间/秒
	 */
	public function setBlockSec($blockSec)
	{
		$this->_blockSec = $blockSec;
	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @return void                 
	 * 启动服务
	 */
	public function start()
	{
		//开启监听
		$this->_listen();

		//程序开始事件
		$this->_event("start");

		while ( $this->_socket ) {
			
			//获取可读链接
			$this->_select();

			//触发查询后事件
			$this->_event("select");
		}

		//程序结束事件
		$this->_event("end");

	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @param  string                 $event 事件名
	 * @param  callable                 $func  可执行结构
	 * @return bool      成/败
	 * 事件绑定
	 */
	public function on($event, $func)
	{
		//不是可执行的结构
		if( !is_callable($func) ) return false;

		$this->_events[$event] = $func;

		return true;
	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @return void
	 * 关闭服务
	 */
	public function close()
	{
		$this->_close();
	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @param  resource                 $sock
	 * @return void 
	 * 关闭客户端
	 */
	public function close_client($sock)
	{

		$this->_close_client($sock);

	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @return array           错误信息
	 * 获取错误信息
	 */
	public function getError()
	{
		//最后错误
        $errcode = socket_last_error();
		//错误信息
		$errmsg = socket_strerror($errcode);

		return [$errcode, $errmsg];
	}

    /**
     * @author llseng
     * @date   2019-07-12
     * @mail   1300904522@qq.com
     * @link   http://www.gzqidong.cn
     * @return resource[]       客户连接
     */
    public function getClients()
    {
        return $this->_sockets;
    }

    /**
     * @author llseng
     * @date   2019-07-12
     * @mail   1300904522@qq.com
     * @link   http://www.gzqidong.cn
     * @return int                 客户端连接数
     */
    public function getClientCount()
    {
        return count( $this->_sockets );
    }

    /**
     * @author llseng
     * @date   2019-07-12
     * @mail   1300904522@qq.com
     * @link   http://www.gzqidong.cn
     * @param  resource                 $sock 客户端连接
     * @param  string                 $msg  消息
     * @return int|false             发送字符数| 失败false
     */
    public function write($sock, $msg = "")
    {
        //不存在客户连接
        if( !$this->isClient($sock) )

        //发送消息
        $result = socket_write($sock, $msg);

        if( false === $result ) {
            $this->_log(__FUNCTION__ . (int)$sock . " write $msg");
        }

        return $result;

    }

    /**
     * @author llseng
     * @date   2019-07-12
     * @mail   1300904522@qq.com
     * @link   http://www.gzqidong.cn
     * @param  resource                 $sock socket连接
     * @return boolean                  是否是客户端连接
     * 检测是否是客户端连接
     */ 
    public function isClient($sock)
    {
        return isset($this->_sockets[(int)$sock]) ? $this->_sockets[(int)$sock] : false;
    }

    /**
     * @author llseng
     * @date   2019-07-12
     * @mail   1300904522@qq.com
     * @link   http://www.gzqidong.cn
     * @param  resource                 $sock socket连接
     * @return boolean                  是否
     * 检测是否是服务器链接
     */
    public function isServer($sock)
    {
        return (int)$this->_socket === (int)$sock ? $this->_socket : false;
    }

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @return void                 
	 * 监听链接
	 */
	private function _listen()
	{
		//监听连接
		if( false === socket_listen($this->_socket, $this->_backlog) )
		{
			$this->_error("socket_listen()");
		}
	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @return void
	 * 获取监听错误处理
	 */
	private function _select_err()
	{
		//获取可读错误太多
		if( $this->selectErr >= statis::MAX_SELECT_ERR_NUM )
		{
			$this->_error(__FUNCTION__);
		}

		//错误加一
		$this->selectErr++;

		$this->_log(__FUNCTION__);
	}

    /**
     * @author llseng
     * @date   2019-07-12
     * @mail   1300904522@qq.com
     * @link   http://www.gzqidong.cn
     * @return void                 
     * 获取监听可操连接
     */
	private function _select()
	{
		//所有socket链接 包括 服务器
		$sockets = array_merge($this->_sockets, [ (int)$this->_socket => $this->_socket]);
		//可读 异常
		$write = $except = [];

		//获取可读链接
		$result = socket_select($sockets, $write, $except, $this->_blockSec);
		//异常
		if( false === $result ) return $this->_select_err();

		//没有可操作连接
		if( !$result ) return ;

		foreach ($sockets as $k => $sock) {
			//如果是服务器链接
			if( $this->isServer($sock) )
			{
				//服务器可读操作
				$this->_accept();

			}else{

				//客户端可读操作
				$this->_read($sock);

			}
		}

	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @return void
	 * 处理客户端连接
	 */
	private function _accept()
	{
		//获取客户端连接信息
		$sock = socket_accept($this->_socket);
		if( false === $sock )
		{
			$error = $this->getError();
			var_dump($error);
		}

		//客户端连接处理
		$this->_client_connect($sock);
	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @param  resource                 $sock 客户端连接
	 * @return void
	 * 客户端连接处理
	 */
	private function _client_connect($sock)
	{
		$this->_log( (int)$sock . " connect");

		//保存客户端连接
		$this->_sockets[(int)$sock] = $sock;

		//触发客户连接事件
		$this->_event("connent", [$sock]);
	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @param  resource                 $sock 客户连接
	 * @return void
	 * 客户端可读操作 . 读取客户端发送信息
	 */
	private function _read($sock)
	{
		//读取客户端发送的信息
		$msg = socket_read($sock, 1024);
		//连接异常
		if( false === $msg ) 
		{
			//关闭客户端
			return $this->_close_client($sock);
		}

		//处理客户发送信息
		$this->_client_write($sock, $msg);
	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @param  resource                 $sock 客户端连接
	 * @param  string                 $msg  信息
	 * @return void
	 * 处理客户端发送消息
	 */
	private function _client_write($sock, $msg)
	{
		$this->_log( (int)$sock . $msg );

		//触发事件
		$this->_event("write", [$sock, $msg]);
	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @param  string                 $event 事件名称
	 * @param  array                  $param 参数
	 * @return void                        
	 * 触发事件
	 */
	private function _event($event, $param = [])
	{
		//
		if( !isset($this->_events[$event]) ) return ;

		if( !is_callable($this->_events[$event]) ) return ;

		//执行的参数
		array_unshift($param, $this);

		call_user_func_array($this->_events[$event], $param);
	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @param  resource                 $sock 客户端连接
	 * @return void
	 * 关闭客户端连接
	 */
	private function _close_client($sock)
	{
		//不存在客户连接
		if( !$this->isClient($sock) ) return ;

		//触发客户端关闭事件
		$this->_event("close", [$sock]);

		//关闭连接
		socket_shutdown($sock);
		//释放连接
		socket_close($sock);
		//删除客户端连接数据
		unset($this->_sockets[(int)$sock]);
	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @param  string                 $address 地址
	 * @param  string|int                 $port    端口
	 * 创建socket连接
	 */
	private function _create()
	{
		//创建连接
		$this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if( false === $this->_socket )
		{
			$this->_error("socket_create()");
		}
		//设置非堵塞
		if( false === socket_set_nonblock($this->_socket) )
		{
			$this->_error("socket_set_nonblock()");
		}
		//绑定监听
		if( false === socket_bind($this->_socket, $this->_address, $this->_port) )
		{
			$this->_error("socket_bind()");
		}
	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @param  string                 $msg 错误信息
	 * 获取socket错误 并 抛出异常
	 */
	private function _error($msg = '')
	{
		$error = $this->getError();

		list($errcode, $errmsg) = $error;

		//关闭服务
		$this->_close();

		throw new Exception($msg . " [$errcode]: $errmsg", 1);
		
	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @param  string                 $msg 信息
	 * @return void
	 * 打印日志
	 */
	private function _log($msg = '')
	{
		//错误信息
		$error = $this->getError();

		$this->_event("log",[$msg, var_export($error, true)]);

		echo $msg . " : " . var_export($error, true) . "\r\n";

	}

	/**
	 * @author llseng
	 * @date   2019-07-11
	 * @mail   1300904522@qq.com
	 * @link   http://www.gzqidong.cn
	 * @return void
	 * 关掉服务器链接
	 */
	private function _close()
	{

		//关闭所有客户端
		foreach($this->_sockets as $key => $sock) {
			$this->_close_client($sock);
		}

		//关闭自己
		socket_close($this->_socket);

		$this->_socket = null;
	}
}

