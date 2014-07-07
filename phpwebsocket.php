#!/usr/bin/env php
<?php
date_default_timezone_set("Asia/Chongqing");
include 'socketmanager.php';
$socketmanager = new socketmanager (8089,'0.0.0.0',function ($socket){
	$hader=$socket->inputbuf;
	if(preg_match("/\r\n\r\n/", $hader)){
		$buf="content-type:text/html;\r\n\r\n";
		$buf.="fsdfs";
		$socket->outputbuf=$buf;
// 		$hader=$socket->inputbuf='';
	}
});
while ( $socketmanager->select() ) {
// 	var_dump($socketmanager);
}