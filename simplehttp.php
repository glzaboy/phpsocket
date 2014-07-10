#!/usr/bin/env php
<?php
define ( "DOOT_ROOT", __DIR__ . "/webroot" );
date_default_timezone_set ( "Asia/Chongqing" );
include 'socketmanager.php';
include 'mime.php';
include 'httpcode.php';
class http {
	/**
	 *
	 * @param socket $socket        	
	 */
	function read($socket) {
		// $hader = $socket->readbuf ( 0 );
		// if (preg_match ( "/\r\n\r\n/", $hader )) {
		// $hader = $socket->readbuf ( 1 );
		// }
	}
	/**
	 *
	 * @param socket $socket        	
	 */
	function write($socket) {
		$header = $socket->readbuf ( 0 );
		if (! preg_match ( "/\r\n\r\n/", $header )) {
			return;
		}
		$header = $this->parseHeader ( $socket->readbuf ( 1 ) );
		if ($socket->bufend == 0) {
			if (realpath ( DOOT_ROOT . $header ['path'] )) {
				if (is_dir ( DOOT_ROOT . $header ['path'] )) {
					echo "DIR".DOOT_ROOT . $header ['path'].PHP_EOL;
					$content = "";
					$dir = dir ( DOOT_ROOT . $header ['path'] );
					while ( ($dirname = $dir->read ()) !== false ) {
						$content .= $dirname;
					}
					$dir->close ();
					$header = self::buildHeader ( HTTPCODE::STATUS_200, strlen ( $content ), "text/html" );
					$header [] = $content;
					$socket->outputbuf = implode ( "\n", $header );
					$socket->bufend = 1;
				} else {
					echo "FILE".DOOT_ROOT . $header ['path'].PHP_EOL;
					$content = file_get_contents ( DOOT_ROOT . $header ['path'] );
					if ($content) {
						$header = self::buildHeader ( HTTPCODE::STATUS_200, filesize ( DOOT_ROOT . $header ['path'] ), MIME_Type::getMIMEType ( $header ['path'] ) );
					} else {
						$content = HTTPCODE::STATUS_403;
						$header = self::buildHeader ( HTTPCODE::STATUS_403, strlen ( $content ), MIME_Type::getMIMEType ( $header ['path'] ) );
					}
					$header [] = $content;
					$socket->outputbuf = implode ( "\n", $header );
					$socket->bufend = 1;
				}
			} else {
				$content = HTTPCODE::STATUS_404;
				$header = self::buildHeader ( HTTPCODE::STATUS_404, strlen ( $content ), "text/html" );
				$header [] = $content;
				$socket->outputbuf = implode ( "\n", $header );
				$socket->bufend = 1;
			}
		}
	}
	/**
	 *
	 * @param unknown $httpStatus        	
	 * @param unknown $length        	
	 * @param unknown $Mime_type        	
	 * @return Array
	 */
	public function buildHeader($httpStatus, $length, $Mime_type) {
		$header = array ();
		$header [] = "HTTP/1.1 " . $httpStatus;
		$header [] = "Server:phpSOcket";
		$header [] = "Content-Length:" . $length;
		$header [] = "Content-Type:" . $Mime_type;
		$header [] = "Accept-Ranges:bytes\n";
		return $header;
	}
	public function parseHeader($headerdata) {
		$header = array ();
		$tmparr = explode ( "\n", $headerdata );
		foreach ( $tmparr as $val ) {
			if (preg_match ( "/^(HEAD|GET) /", $val )) {
				$header ['path'] = trim ( preg_replace ( '/HTTP\/1\.[01]/', '', preg_replace ( "/^(HEAD|GET) /", '', $val ) ) );
				continue;
			}
			list ( $key, $value ) = explode ( ':', trim ( $val ) );
			if ($key) {
				$header [$key] = $value;
			}
		}
		return $header;
	}
}
$http = new http ();
$socketmanager = new socketmanager ( 8089, '0.0.0.0', $http );
while ( $socketmanager->select () ) {
	// var_dump($socketmanager);
}