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
	private $filehandle = array ();
	private $handle = null;
	private $request = array ();
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
		
		// echo date ( "H:i:s" ) . "选择" . PHP_EOL;
		// sleep(1);
		// echo '链接读';
// 		var_dump ( $this->sockets );
		if (socket_select ( $readsock, $writesock, $exceptsock, 2, 500 ) == 0) {
			return true;
		}
		// echo date ( "H:i:s" ) . "处理socket" . PHP_EOL;
		// echo '链接读';
		// var_dump ( $readsock );
		// echo '链接写';
		// var_dump ( $writesock );
		// echo '链接异';
		// var_dump ( $exceptsock );
		// echo '资源';
		// var_dump ( $this->resource );
		// sleep(1);
		foreach ( $exceptsock as $sock ) {
			$key = array_search ( $sock, $this->sockets );
			$socket = socket::restore ( $this->resource [$key], $sock );
			$this->removeclient ( $socket );
			$socket->close ();
			unset ( $readsock [$key] );
			unset ( $writesock [$key] );
		}
		// sleep(1);
		if (in_array ( $this->listensocket, $readsock )) {
			echo date ( "H:i:s" ) . 'accept' . PHP_EOL;
			$key = array_search ( $this->listensocket, $this->sockets );
			$socket = socket::restore ( $this->resource [$key], $this->listensocket );
			$socketaccetp = $socket->acceptsocket ();
			if ($socketaccetp) {
				$client = $this->addclient ( $socketaccetp );
				echo date ( "H:i:s" ) . 'add client' . $client . PHP_EOL;
			}
			$key = array_search ( $this->listensocket, $readsock );
			unset ( $readsock [$key] );
		}
		// sleep(1);
		foreach ( $readsock as $sock ) {
			$key = array_search ( $sock, $this->sockets );
			$socket = socket::restore ( $this->resource [$key], $sock, $this->filehandle [$key] );
			if ($socket->recvbuf () === false) {
				echo date ( "H:i:s" ) . "Free read socket" . $key . PHP_EOL;
				$key = array_search ( $sock, $readsock );
				$this->removeclient ( $socket );
				$socket->close ();
				unset ( $readsock [$key], $writesock [$key] );
			} else {
				$this->triggerread ( $socket );
				$this->addclient ( $socket );
			}
		}
		// sleep(1);
		foreach ( $writesock as $sock ) {
			$key = array_search ( $sock, $this->sockets );
			$socket = socket::restore ( $this->resource [$key], $sock, $this->filehandle [$key], $this->request [$key] );
// 			var_dump($socket->request);
			$this->triggerwrite ( $socket );
			if ($socket->sentbuf () === false) {
				echo date ( "H:i:s" ) . "Free write socket" . $key . PHP_EOL;
				$key = array_search ( $sock, $writesock );
				$this->removeclient ( $socket );
				$socket->close ();
				unset ( $readsock [$key], $writesock [$key] );
			} else {
// 				echo "更新socket缓存".PHP_EOL;
				$this->addclient ( $socket );
			}
		}
		return true;
	}
	/**
	 *
	 * @param socket $socket        	
	 */
	public function addclient(&$socket) {
		$key = array_search ( $socket->socket, $this->sockets );
		if ($key === false) {
			$this->sockets [] = $socket->socket;
			$key = array_search ( $socket->socket, $this->sockets );
		}
		$this->resource [$key] = serialize ( $socket );
		$this->filehandle [$key] = $socket->filehandle;
		$this->request [$key] = $socket->request;
		return $key;
	}
	/**
	 *
	 * @param socket $socket        	
	 */
	public function removeclient(&$socket) {
		$this->triggerclose ( $socket );
		$key = array_search ( $socket->socket, $this->sockets );
		unset ( $this->sockets [$key], $this->resource [$key], $this->filehandle [$key], $this->request [$key] );
	}
	public function triggerread(&$socket) {
		if ($this->handle) {
			call_user_func ( array (
					$this->handle,
					'read' 
			), $socket );
		}
	}
	public function triggerwrite(&$socket) {
		if ($this->handle) {
			call_user_func ( array (
					$this->handle,
					'write' 
			), $socket );
		}
	}
	public function triggerclose(&$socket) {
		if ($this->handle) {
			call_user_func ( array (
					$this->handle,
					'close' 
			), $socket );
		}
	}
}