<?php
class socket {
	const SOCK_TYPE_LISTEN = 1;
	const SOCK_TYPE_DATA = 2;
	const SOCK_TYPE_UNKNOW = 3;
	public $socket = null;
	public $readbuf = null;
	public $sendbuf = null;
	public $sockettype = null;
	/**
	 * 创建socket
	 *
	 * @param unknown $port        	
	 * @param unknown $ip        	
	 * @return socket
	 */
	static public function createSocket($port, $ip) {
		$socket = socket_create ( AF_INET, SOCK_STREAM, SOL_TCP );
		if (! is_resource ( $socket )) {
			echo 'creeate socket fail.' . socket_strerror ( socket_last_error () ) . PHP_EOL;
			exit ();
		}
		if (! socket_set_option ( $socket, SOL_SOCKET, SO_REUSEADDR, 1 )) {
			echo 'Unable to set option on socket: ' . socket_strerror ( socket_last_error () ) . PHP_EOL;
			exit ();
		}
		if (! socket_bind ( $socket, $ip, $port )) {
			echo 'Unable to bind socket: ' . socket_strerror ( socket_last_error () ) . PHP_EOL;
			exit ();
		}
		if (! socket_listen ( $socket )) {
			echo "unable to listen socket ", socket_strerror ( socket_last_error () ) . PHP_EOL;
			exit ();
		}
		$returnsocket = new socket ();
		$returnsocket->socket = $socket;
		$returnsocket->sockettype = self::SOCK_TYPE_LISTEN;
		return $returnsocket;
	}
	/**
	 * 创建与客户端连接的socket
	 *
	 * @return socket
	 */
	public function acceptsocket() {
		$returnsocket = new socket ();
		if ($this->sockettype == self::SOCK_TYPE_LISTEN) {
			$returnsocket->socket = socket_accept ( $this->socket );
			$returnsocket->sockettype = self::SOCK_TYPE_DATA;
		} else {
			$returnsocket->socket = null;
			$returnsocket->sockettype = self::SOCK_TYPE_UNKNOW;
		}
		return $returnsocket;
	}
}