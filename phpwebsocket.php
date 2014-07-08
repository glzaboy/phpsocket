#!/usr/bin/env php
<?php
date_default_timezone_set("Asia/Chongqing");
include 'socketmanager.php';
class http{
	/**
	 * 
	 * @param socket $socket
	 */
	function read($socket){
		$hader=$socket->readbuf(0);
		if(preg_match("/\r\n\r\n/", $hader)){
			$hader=$socket->readbuf(1);
		}
		
	}
	/**
	 * 
	 * @param socket $socket
	 */
	function write($socket){
		$buf="content-type:text/html;\r\n\r\n";
		$buf.="fsdfs";
		$socket->outputbuf=$buf;
	}
}
$http=new http();
$socketmanager = new socketmanager (8089,'0.0.0.0',$http);
while ( $socketmanager->select() ) {
// 	var_dump($socketmanager);
}