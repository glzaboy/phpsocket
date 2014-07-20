#!/usr/bin/env php
<?php
define ( "DOOT_ROOT", __DIR__ . "/webroot" );
date_default_timezone_set ( "Asia/Chongqing" );
include 'socketmanager.php';
include 'mime.php';
include 'httpcode.php';
class Request {
	public $method = null;
	public $range = false;
	public $rangemin = 0;
	public $rangeoffset = 0;
	public $rangemax = 0;
	public $path = '';
	public $query_get = array ();
	public $query_string = '';
	public function __construct($headerdata) {
		$tmparr = explode ( "\n", $headerdata );
		foreach ( $tmparr as $val ) {
			if (preg_match ( "/^(get|head|put|post)/i", $val, $match )) {
				$this->method = $match [1];
				$urlinfo = parse_url ( trim ( preg_replace ( '/HTTP\/1\.[01]/', '', preg_replace ( "/^(get|head|put|post)/i", '', $val ) ) ) );
				$this->path = $urlinfo ['path'];
				$this->query_string = isset ( $urlinfo ['query'] ) ? $urlinfo ['query'] : '';
				if ($this->query_string) {
					parse_str ( $this->query_string, $this->query_get );
				}
				continue;
			}
			if (! trim ( $val )) {
				continue;
			}
			list ( $key, $value ) = explode ( ':', trim ( $val ) );
			if (preg_match ( "/Range/i", $key )) {
				if (preg_match ( '/bytes=(\d+)-([\d]{0,})/i', trim ( $value ), $match )) {
					$this->range = true;
					$this->rangeoffset = $this->rangemin = intval ( $match [1] );
					$this->rangemax = $match [2];
				}
				continue;
			}
			if ($key) {
				$this->$key = $value;
			}
		}
	}
}
class http {
	/**
	 *
	 * @param socket $socket        	
	 */
	function close($socket) {
		if (count ( $socket->filehandle )) {
			echo "关闭读写文件";
			foreach ( $socket->filehandle as $key => $handle ) {
				echo "句柄名:" . $key . PHP_EOL;
				fclose ( $handle );
				unset ( $socket->filehandle [$key] );
			}
		}
	}
	/**
	 *
	 * @param socket $socket        	
	 */
	function read(&$socket) {
	}
	/**
	 *
	 * @param socket $socket        	
	 */
	function write(&$socket) {
		if (! $socket->filehandle ['data']) {
			$header = $socket->readbuf ( 0 );
			if (! preg_match ( "/\r\n\r\n/", $header )) {
				return;
			}
			if (! $socket->request) {
				$header = $socket->readbuf ( 1 );
				echo "接收header头：", $header . PHP_EOL;
				$request = new Request ( $header );
				$request->path = urldecode ( $request->path );
				$socket->request = $request;
				// var_dump ( $socket );
			}
			if ($socket->bufend == 0) {
				if (! realpath ( DOOT_ROOT . $socket->request->path )) {
					echo "NOT FOUND" . DOOT_ROOT . $socket->request->path . PHP_EOL;
					$content = HTTPCODE::STATUS_404;
					$header = self::buildHeader ( HTTPCODE::STATUS_404, strlen ( $content ), "text/html" );
					$header [] = $content;
					$socket->outputbuf = implode ( "\r\n", $header );
					$socket->bufend = 1;
					return true;
				} elseif (is_dir ( DOOT_ROOT . $socket->request->path )) {
					echo "DIR" . DOOT_ROOT . $socket->request->path . PHP_EOL;
					$content = "<html><head><title>" . htmlspecialchars ( dirname ( "DIR" . $socket->request->path ) ) . "</title></head><body><table>";
					$dir = dir ( DOOT_ROOT . $socket->request->path );
					while ( ($dirname = $dir->read ()) !== false ) {
						if (in_array ( $dirname, array (
								'.' 
						) )) {
							continue;
						}
						if (is_dir ( DOOT_ROOT . $socket->request->path . '/' . $dirname )) {
							$content .= "<tr><td><a href=\"" . urlencode ( $dirname ) . "/\">{$dirname}</a></td></tr>";
						} else {
							$content .= "<tr><td><a href=\"" . urlencode ( $dirname ) . "\">{$dirname}</a></td></tr>";
						}
					}
					$content .= "</table></body></html>";
					$dir->close ();
					$header = self::buildHeader ( HTTPCODE::STATUS_200, strlen ( $content ), "text/html", 'utf-8' );
					$header [] = $content;
					$socket->outputbuf = implode ( "\r\n", $header );
					$socket->bufend = 1;
					return true;
				} else {
					echo "请求读" . DOOT_ROOT . $socket->request->path . PHP_EOL;
					if (! is_resource ( $socket->filehandle ['data'] )) {
						$socket->filehandle ['data'] = fopen ( DOOT_ROOT . $socket->request->path, "r" );
						if (! $socket->filehandle ['data']) {
							echo "打开" . DOOT_ROOT . $socket->request->path . '失败' . PHP_EOL;
							$content = HTTPCODE::STATUS_403;
							$header = self::buildHeader ( HTTPCODE::STATUS_403, strlen ( $content ), MIME_Type::getMIMEType ( $header ['path'] ) );
							$header [] = $content;
							$socket->outputbuf = implode ( "\r\n", $header );
							$socket->bufend = 1;
							return true;
						}
						echo "打开" . DOOT_ROOT . $socket->request->path . '成功' . PHP_EOL;
						$filelen = filesize ( DOOT_ROOT . $socket->request->path );
						echo "文件长度" . $filelen . PHP_EOL;
						if ($socket->request->range) {
							if ($socket->request->rangemax == '') {
								$socket->request->rangemax = $filelen - 1;
							}
							$socket->request->rangemax = min ( $socket->request->rangemax, $filelen - 1 );
							$len = $socket->request->rangemax - $socket->request->rangemin + 1;
							if ($socket->request->rangemin) {
								echo "根据Range进行文件偏移" . $socket->request->rangemin . PHP_EOL;
								fseek ( $socket->filehandle ['data'], $socket->request->rangemin );
							}
							$header = self::buildHeader ( HTTPCODE::STATUS_206, $len, MIME_Type::getMIMEType ( $socket->request->path ), null, "{$socket->request->rangemin}-{$socket->request->rangemax}/$filelen" );
						} else {
							$header = self::buildHeader ( HTTPCODE::STATUS_200, $filelen, MIME_Type::getMIMEType ( $socket->request->path ), null );
						}
						echo "回应header：" . implode ( "\r\n", $header );
						$socket->outputbuf = implode ( "\r\n", $header );
					}
				}
			}
		} else {
			if (strlen ( $socket->outputbuf ) < 10240) {
				if ($socket->request->range) {
					$maxlen = 4096000;
					// calc $len;
					$rangelen = intval ( $socket->request->rangemax ) - intval ( $socket->request->rangemin );
					$maxlen = min ( $rangelen, $maxlen );
					$data = fread ( $socket->filehandle ['data'], $maxlen );
					$readlen = strlen ( $data );
					echo "Range读从" . $socket->request->rangeoffset, "到", $socket->request->rangeoffset + $readlen, "共计{$readlen}字节。" . PHP_EOL;
					$socket->request->rangeoffset += strlen ( $data );
				} else {
					echo "直接读" . PHP_EOL;
					$data = fread ( $socket->filehandle ['data'], 4096000 );
				}
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
	public function buildHeader($httpStatus, $length, $Mime_type, $charset = "utf-8", $range = null) {
		$header = array ();
		$header [] = "HTTP/1.1 " . $httpStatus;
		$header [] = "Server:phpSocket";
		$header [] = "Content-Length:" . $length;
		if ($httpStatus == HTTPCODE::STATUS_206) {
			$header [] = "Content-Range: bytes " . $range;
		} else {
			$header [] = "Accept-Ranges:bytes";
		}
		$header [] = "Connection:close";
		if ($charset) {
			$header [] = "Content-Type:" . $Mime_type . ";charset={$charset}\r\n\r\n";
		} else {
			$header [] = "Content-Type:" . $Mime_type . "\r\n\r\n";
		}
		return $header;
	}
}
$http = new http ();
$socketmanager = new socketmanager ( 8089, '0.0.0.0', $http );
while ( $socketmanager->select () ) {
	// var_dump($socketmanager);
}