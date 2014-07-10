<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * HTTP::Header
 *
 * PHP versions 5
 *
 * @category HTTP
 * @package HTTP_Header2
 * @author Wolfram Kriesing <wk@visionp.de>
 * @author Davey Shafik <davey@php.net>
 * @author Michael Wallner <mike@php.net>
 * @copyright 2003-2005 The Authors
 * @license BSD, revised
 * @version CVS: $Id$
 * @link http://pear.php.net/package/HTTP_Header2
 */
class HTTPCODE {
	const STATUS_100 = '100 Continue';
	const STATUS_101 = '101 Switching Protocols';
	const STATUS_102 = '102 Processing';
	/**
	 * #@-
	 */
	
	/**
	 * #+
	 * Success Codes
	 */
	const STATUS_200 = '200 OK';
	const STATUS_201 = '201 Created';
	const STATUS_202 = '202 Accepted';
	const STATUS_203 = '203 Non-Authoritative Information';
	const STATUS_204 = '204 No Content';
	const STATUS_205 = '205 Reset Content';
	const STATUS_206 = '206 Partial Content';
	const STATUS_207 = '207 Multi-Status';
	/**
	 * #@-
	 */
	
	/**
	 * #@+
	 * Redirection Codes
	 */
	const STATUS_300 = '300 Multiple Choices';
	const STATUS_301 = '301 Moved Permanently';
	const STATUS_302 = '302 Found';
	const STATUS_303 = '303 See Other';
	const STATUS_304 = '304 Not Modified';
	const STATUS_305 = '305 Use Proxy';
	const STATUS_306 = '306 (Unused)';
	const STATUS_307 = '307 Temporary Redirect';
	/**
	 * #@-
	 */
	
	/**
	 * #@+
	 * Error Codes
	 */
	const STATUS_400 = '400 Bad Request';
	const STATUS_401 = '401 Unauthorized';
	const STATUS_402 = '402 Payment Granted';
	const STATUS_403 = '403 Forbidden';
	const STATUS_404 = '404 File Not Found';
	const STATUS_405 = '405 Method Not Allowed';
	const STATUS_406 = '406 Not Acceptable';
	const STATUS_407 = '407 Proxy Authentication Required';
	const STATUS_408 = '408 Request Time-out';
	const STATUS_409 = '409 Conflict';
	const STATUS_410 = '410 Gone';
	const STATUS_411 = '411 Length Required';
	const STATUS_412 = '412 Precondition Failed';
	const STATUS_413 = '413 Request Entity Too Large';
	const STATUS_414 = '414 Request-URI Too Large';
	const STATUS_415 = '415 Unsupported Media Type';
	const STATUS_416 = '416 Requested range not satisfiable';
	const STATUS_417 = '417 Expectation Failed';
	const STATUS_422 = '422 Unprocessable Entity';
	const STATUS_423 = '423 Locked';
	const STATUS_424 = '424 Failed Dependency';
	/**
	 * #@-
	 */
	
	/**
	 * #@+
	 * Server Errors
	 */
	const STATUS_500 = '500 Internal Server Error';
	const STATUS_501 = '501 Not Implemented';
	const STATUS_502 = '502 Bad Gateway';
	const STATUS_503 = '503 Service Unavailable';
	const STATUS_504 = '504 Gateway Time-out';
	const STATUS_505 = '505 HTTP Version not supported';
	const STATUS_507 = '507 Insufficient Storage';
}