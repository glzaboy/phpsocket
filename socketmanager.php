<?php
/**
 * socket 管理
 * @author guliuzhong
 *
 */
if (! class_exists ( 'socket', flase )) {
	include 'socket.php';
}
class socketmanager {
	private $listensocket = null;
	private $sockets = array ();
	private $resource = array ();
	private $handle = null;
	public function __construct($port = '10080', $ip = '0.0.0.0', $obj) {
		$socket = socket::createSocket ( $port, $ip );
		$this->listensocket = $socket->socket;
		$this->addclient ( $socket );
		$this->handle = $obj;
	}
	public function select() {
		$readsock = $this->sockets;
		$writesock = $this->sockets;
		$key = array_search ( $this->listensocket, $writesock );
		unset ( $writesock [$key] );
		$exceptsock = $this->sockets;
		$key = array_search ( $this->listensocket, $exceptsock );
		unset ( $exceptsock [$key] );
		
		echo date ( "H:i:s" ) . "选择" . PHP_EOL;
		if (socket_select ( $readsock, $writesock, $exceptsock, 0, 0 ) == 0) {
			return true;
		}
		echo date ( "H:i:s" ) . "处理socket" . PHP_EOL;
		// echo '链接读';
		// var_dump ( $readsock );
		// echo '链接写';
		// var_dump ( $writesock );
		// echo '链接异';
		// var_dump ( $exceptsock );
		// echo '资源';
		// var_dump ( $this->resource );
		foreach ( $exceptsock as $sock ) {
			$key = array_search ( $sock, $this->sockets );
			$socket = socket::restore ( $this->resource [$key], $sock );
			$this->removeclient ( $socket );
			$socket->close ();
			unset ( $readsock [$key], $writesock [$key] );
		}
		if (in_array ( $this->listensocket, $readsock )) {
			$key = array_search ( $this->listensocket, $this->sockets );
			// echo $key;
			// exit;
			$socket = socket::restore ( $this->resource [$key], $this->listensocket );
			$this->addclient ( $socket->acceptsocket () );
			$key = array_search ( $this->listensocket, $readsock );
			echo 'accept';
			unset ( $readsock [$key] );
		}
		foreach ( $readsock as $sock ) {
			$key = array_search ( $sock, $this->sockets );
			$socket = socket::restore ( $this->resource [$key], $sock );
			if ($socket->recvbuf () === false) {
				echo "Free socket" . PHP_EOL;
				$key = array_search ( $sock, $readsock );
				$this->removeclient ( $socket );
				$socket->close ();
				unset ( $readsock [$key] );
			} else {
				$this->triggerread ( $socket );
				$this->addclient ( $socket );
			}
		}
		foreach ( $writesock as $sock ) {
			$key = array_search ( $sock, $this->sockets );
			$socket = socket::restore ( $this->resource [$key], $sock );
			if ($socket->sentbuf () === false) {
				echo "Free socket" . PHP_EOL;
				$key = array_search ( $sock, $readsock );
				$this->removeclient ( $socket );
				$socket->close ();
				unset ( $writesock [$key] );
			} else {
				$this->triggerwrite ( $socket );
				$this->addclient ( $socket );
			}
		}
		return true;
	}
	/**
	 *
	 * @param socket $socket        	
	 */
	public function addclient($socket) {
		$key = array_search ( $socket->socket, $this->sockets );
		if ($key === false) {
			$this->sockets [] = $socket->socket;
			$key = array_search ( $socket->socket, $this->sockets );
		}
		$this->resource [$key] = serialize ( $socket );
	}
	/**
	 *
	 * @param socket $socket        	
	 */
	public function removeclient($socket) {
		$key = array_search ( $socket, $this->sockets );
		unset ( $this->sockets [$key], $this->resource [$key] );
	}
	public function triggerread($socket) {
		if ($this->handle) {
			call_user_func ( array (
					$this->handle,
					'read' 
			), $socket );
		}
	}
	public function triggerwrite($socket) {
		if ($this->handle) {
			call_user_func ( array (
					$this->handle,
					'write' 
			), $socket );
		}
	}
}