<?php
/**
 * socket 管理
 * @author guliuzhong
 *
 */
if (!class_exists ( 'socket', flase )) {
	include 'socket.php';
}
class socketmanager {
	private $listensocket = null;
	public function __construct($port = '10080', $ip = '0.0.0.0') {
		$this->listensocket = socket::createSocket ( $port, $ip );
	}
	public function switchsocket($socket) {
	}
}