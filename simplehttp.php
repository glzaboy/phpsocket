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
	function close($socket) {
		if(count($socket->filehandle)){
			echo "关闭读写文件";
			foreach ($socket->filehandle as $key=>$handle){
				echo "句柄名:".$key.PHP_EOL;
				fclose($handle);
				unset($socket->filehandle[$key]);
			}
		}
		// $hader = $socket->readbuf ( 0 );
		// if (preg_match ( "/\r\n\r\n/", $hader )) {
		// $hader = $socket->readbuf ( 1 );
		// }
	}
	/**
	 *
	 * @param socket $socket        	
	 */
	function read(&$socket) {
		// $hader = $socket->readbuf ( 0 );
		// if (preg_match ( "/\r\n\r\n/", $hader )) {
		// $hader = $socket->readbuf ( 1 );
		// }
	}
	/**
	 *
	 * @param socket $socket        	
	 */
	function write(&$socket) {
		if(!$socket->filehandle['data']){
			$header = $socket->readbuf ( 0 );
			if (! preg_match ( "/\r\n\r\n/", $header )) {
				return;
			}
			$header = $this->parseHeader ( $socket->readbuf ( 1 ) );
			$header ['path'] = urldecode ( $header ['path'] );
			if ($socket->bufend == 0) {
				if (!realpath ( DOOT_ROOT . $header ['path'] )) {
					echo "NOT FOUND" . DOOT_ROOT . $header ['path'] . PHP_EOL;
					$content = HTTPCODE::STATUS_404;
					$header = self::buildHeader ( HTTPCODE::STATUS_404, strlen ( $content ), "text/html" );
					$header [] = $content;
					$socket->outputbuf = implode ( "\n", $header );
					$socket->bufend = 1;
				}else{
					if (is_dir ( DOOT_ROOT . $header ['path'] )) {
						echo "DIR" . DOOT_ROOT . $header ['path'] . PHP_EOL;
						$content = "<html><head><title>" . htmlspecialchars ( dirname ( "DIR" . $header ['path'] ) ) . "</title></head><body><table>";
						$dir = dir ( DOOT_ROOT . $header ['path'] );
						while ( ($dirname = $dir->read ()) !== false ) {
							if (in_array ( $dirname, array (
									'.'
							) )) {
								continue;
							}
							if (is_dir ( DOOT_ROOT . $header ['path'] . '/' . $dirname )) {
								$content .= "<tr><td><a href=\"" . urlencode ( $dirname ) . "/\">{$dirname}</a></td></tr>";
							} else {
								$content .= "<tr><td><a href=\"" . urlencode ( $dirname ) . "\">{$dirname}</a></td></tr>";
							}
						}
						$content .= "</table></body></html>";
						$dir->close ();
						$header = self::buildHeader ( HTTPCODE::STATUS_200, strlen ( $content ), "text/html" );
						$header [] = $content;
						$socket->outputbuf = implode ( "\n", $header );
						$socket->bufend = 1;
					}else{
						echo "请求读" . DOOT_ROOT . $header ['path'] . PHP_EOL;
						if(!is_resource($socket->filehandle['data'])){
							$socket->filehandle['data']=fopen(DOOT_ROOT . $header ['path'], "r");
							if(!$socket->filehandle['data']){
								echo "打开".DOOT_ROOT . $header ['path'] .'失败'. PHP_EOL;
								$content = HTTPCODE::STATUS_403;
								$header = self::buildHeader ( HTTPCODE::STATUS_403, strlen ( $content ), MIME_Type::getMIMEType ( $header ['path'] ) );
								$header [] = $content;
								$socket->outputbuf = implode ( "\n", $header );
								$socket->bufend = 1;
								return false;
							}
							echo "打开".DOOT_ROOT . $header ['path'] .'成功'. PHP_EOL;
							$header = self::buildHeader ( HTTPCODE::STATUS_200, filesize ( DOOT_ROOT . $header ['path'] ), MIME_Type::getMIMEType ( $header ['path'] ) );
							$header [] = $content;
							$socket->outputbuf = implode ( "\n", $header );
						}
					}
				}
			}
		}else{
			if(strlen($socket->outputbuf)<10240){
				$data = fread ( $socket->filehandle ['data'], 4096000 );
				echo "从磁盘读取文件" . strlen ( $data ) . '字节' . PHP_EOL;
				$socket->outputbuf .= $data;
				if (feof ( $socket->filehandle ['data'] )) {
					$socket->bufend = 1;
				}
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