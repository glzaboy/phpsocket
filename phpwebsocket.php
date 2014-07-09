#!/usr/bin/env php
<?php
date_default_timezone_set ( "Asia/Chongqing" );
include 'socketmanager.php';
include 'mime.php';
class http {
	/**
	 *
	 * @param socket $socket        	
	 */
	function read($socket) {
// 		$hader = $socket->readbuf ( 0 );
// 		if (preg_match ( "/\r\n\r\n/", $hader )) {
// 			$hader = $socket->readbuf ( 1 );
// 		}
	}
	/**
	 *
	 * @param socket $socket        	
	 */
	function write($socket) {
		$header = $socket->readbuf ( 0 );
		if (!preg_match ( "/\r\n\r\n/", $header )) {
			return ;
		}
		$header=$this->parseHeader( $socket->readbuf ( 1 ));
		if($socket->bufend==0){
			$buf=array();
			$buf[]="HTTP/1.1 200 OK";
			$buf[]="Server:phpSOcket";
			$buf[]="content-length:".filesize(__DIR__.$header['path']);
			$buf[] = "Content-Type:".MIME_Type::getMIMEType($header['path']);
			$buf[] = "Accept-Ranges: bytes\n";
			$buf[] = file_get_contents(__DIR__.$header['path']);
			$socket->outputbuf = implode("\r\n", $buf);
			$socket->bufend=1;
		}
	}
	public function parseHeader($headerdata){
		$header=array();
		$tmparr=explode("\n", $headerdata);
		foreach ($tmparr as $val){
			if(preg_match("/^(HEAD|GET) /", $val)){
				$header['path']=trim(preg_replace('/HTTP\/1\.[01]/', '', preg_replace("/^(HEAD|GET) /", '', $val)));
				continue;
			}
			list($key,$value)=explode(':', trim($val));
			if($key){
				$header[$key]=$value;
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