<?php
class socket {
	const SOCK_TYPE_LISTEN = 1;
	const SOCK_TYPE_DATA = 2;
	const SOCK_TYPE_UNKNOW = 3;
	public $socket = null;
	public $inputbuf = '';
	public $outputbuf = '';
	public $sockettype = 0;
	public $filehandle = null;
	public $bufend = 0;
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
		socket_set_nonblock ( $socket );
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
		if ($this->sockettype == self::SOCK_TYPE_LISTEN) {
			$returnsocket = new socket ();
			$returnsocket->socket = socket_accept ( $this->socket );
			socket_set_nonblock ( $returnsocket->socket );
			$returnsocket->sockettype = self::SOCK_TYPE_DATA;
			return $returnsocket;
		} else {
			return false;
		}
	}
	public function __sleep() {
		return array (
				'inputbuf',
				'outputbuf',
				'sockettype',
				'bufend' 
		);
	}
	/**
	 *
	 * @param unknown $socket        	
	 * @param unknown $data        	
	 * @return socket
	 */
	static public function restore($serializedata, $socket,$filehandle=null) {
		$mysocket = unserialize ( $serializedata );
		$mysocket->socket = $socket;
		if(is_array($filehandle)){
			$mysocket->filehandle=$filehandle;
		}
		return $mysocket;
	}
	/**
	 * 关闭socket
	 */
	public function close() {
		return socket_close ( $this->socket );
	}
	public function sentbuf() {
		if ($this->sockettype == self::SOCK_TYPE_DATA) {
			$datalen = strlen ( $this->outputbuf );
			$len = socket_send ( $this->socket, $this->outputbuf, 2048000, 0 );
			if ($len == $datalen) {
				$this->outputbuf = '';
				if ($this->bufend == 1) {
					return false;
				}
			} elseif ($len == 0) {
				return false;
			} elseif($len>0) {
				$this->outputbuf = substr ( $this->outputbuf, $len  );
				return true;
			}
		}else{
			return true;
		}
	}
	public function recvbuf() {
		if ($this->sockettype == self::SOCK_TYPE_DATA) {
			$buf = '';
			$len = socket_recv ( $this->socket, $buf, 4096, 0 );
			if ($len === 0) {
				return false;
			} elseif ($len > 0) {
				$this->inputbuf .= $buf;
				return true;
			} elseif ($len == SOCKET_EINTR) {
				return true;
			} elseif ($len == SOCKET_EWOULDBLOCK) {
				return true;
			}
		} else {
			return false;
		}
	}
	/**
	 * 取buf
	 *
	 * @param number $clearbuf        	
	 * @return string
	 */
	public function readbuf($clearbuf = 0) {
		if ($this->sockettype == self::SOCK_TYPE_DATA) {
			$buf = $this->inputbuf;
			if ($clearbuf) {
				$this->inputbuf = '';
			}
			return $buf;
		} else {
			return '';
		}
	}
}