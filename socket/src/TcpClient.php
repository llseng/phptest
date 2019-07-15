<?php

/**
 * tcp客户端
 */
class TcpClient
{
	const READ_BUFFER_SIZE = 65535;

	private $address ;

	private $port;

	private $_socket;
	
	function __construct($address, $port)
	{
		$this->setAddress($address);
		$this->setPort($port);

		$this->_create();

	}

	public function setAddress($address)
	{
		$this->address = $address;
	}

	public function setPort($port)
	{
		$this->port = $port;
	}

	public function getAddress()
	{
		return $this->address;
	}

	public function getPort()
	{
		return $this->port;
	}

	public function write($msg)
	{
		$result = socket_write($this->_socket, $msg);

		if( $result === false )
		{

		}

		return $result;
	}

	public function read()
	{
		$msg = socket_read($this->_socket, static::READ_BUFFER_SIZE);

		if( $msg === false )
		{

		}

		return $msg;

	}

	public function close()
	{
		$this->_close();
	}

	private function _create()
	{
		$this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if( $this->_socket === false ) 
		{
			$this->_error();
		}

		$connect = socket_connect($this->_socket, $this->getAddress(), $this->getPort());
		if( $connect === false )
		{
			$this->_error();
		}
	}

	private function _close()
	{
		socket_close($this->_socket);
	}

	private function _error($msg = '')
	{
		$error = $this->getError();

		list($errcode, $errmsg) = $error;

		//关闭服务
		$this->_close();

		throw new Exception($msg . " [$errcode]: $errmsg", 1);
		
	}

	public function getError()
	{
		//最后错误
        $errcode = socket_last_error();
		//错误信息
		$errmsg = socket_strerror($errcode);

		return [$errcode, $errmsg];
	}
}