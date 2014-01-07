<?php
namespace Et;
class Http_Headers {

	// 2xx codes
	const CODE_200_OK = 200;
	const CODE_201_CREATED = 201;
	const CODE_202_ACCEPTED = 202;
	const CODE_204_NO_CONTENT = 204;
	const CODE_205_RESET_CONTENT = 205;
	const CODE_206_PARTIAL_CONTENT = 206;

	// 3xx codes
	const CODE_301_MOVED_PERMANENTLY = 301;
	const CODE_302_FOUND = 302;
	const CODE_302_MOVED_TEMPORARY = 302;
	const CODE_303_SEE_OTHER = 303;
	const CODE_304_NOT_MODIFIED = 304;
	const CODE_307_TEMPORARY_REDIRECT = 307;
	const CODE_308_PERMANENT_REDIRECT = 308;

	// 4xx codes
	const CODE_400_BAD_REQUEST = 400;
	const CODE_401_UNAUTHORIZED = 401;
	const CODE_402_PAYMENT_REQUIRED = 402;
	const CODE_403_FORBIDDEN = 403;
	const CODE_404_NOT_FOUND = 404;
	const CODE_405_METHOD_NOT_ALLOWED = 405;
	const CODE_406_NOT_ACCEPTABLE = 406;
	const CODE_407_PROXY_AUTHENTICATION_REQUIRED = 407;
	const CODE_408_REQUEST_TIMEOUT = 408;
	const CODE_409_CONFLICT = 409;
	const CODE_410_GONE = 410;
	const CODE_411_LENGTH_REQUIRED = 411;
	const CODE_412_PRECONDITION_FAILED = 412;
	const CODE_413_REQUEST_ENTITY_TOO_LARGE = 413;
	const CODE_414_REQUEST_URI_TOO_LONG = 414;
	const CODE_415_UNSUPPORTED_MEDIA_TYPE = 415;
	const CODE_416_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
	const CODE_417_EXPECTATION_FAILED = 417;
	const CODE_425_UNORDERED_COLLECTION = 425;
	const CODE_426_UPGRADE_REQUIRED = 426;
	const CODE_428_PRECONDITION_REQUIRED = 428;
	const CODE_429_TOO_MANY_REQUESTS = 429;
	const CODE_431_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
	const CODE_444_NO_RESPONSE = 444;
	const CODE_451_UNAVAILABLE_FOR_LEGAL_REASONS = 451;

	// 5xx codes
	const CODE_500_INTERNAL_SERVER_ERROR = 500;
	const CODE_501_NOT_IMPLEMENTED = 501;
	const CODE_502_BAD_GATEWAY = 502;
	const CODE_503_SERVICE_UNAVAILABLE = 503;
	const CODE_504_GATEWAY_TIMEOUT = 504;
	const CODE_505_HTTP_VERSION_NOT_SUPPORTED = 505;
	const CODE_506_VARIANT_ALSO_NEGOTIATES = 506;
	const CODE_509_BANDWIDTH_LIMIT_EXCEEDED = 509;
	const CODE_510_NOT_EXTENDED = 510;
	const CODE_511_NETWORK_AUTHENTICATION_REQUIRED = 511;
	const CODE_598_NETWORK_READ_TIMEOUT_ERROR = 598;
	const CODE_599_NETWORK_CONNECT_TIMEOUT_ERROR = 599;

	/**
	 * HTTP version (1.1 or 1.0)
	 *
	 * @var string
	 */
	protected static $HTTP_version = "1.1";

	/**
	 * HTTP status codes
	 *
	 * @var array
	 */
	protected static $response_codes = array(
		//2xx
		200 => "OK",
		201 => "Created",
		202 => "Accepted",
		204 => "No Content",
		205 => "Reset Content",
		206 => "Partial Content",

		//3xx
		301 => "Moved Permanently",
		302 => "Found",
		303 => "See Other",
		304 => "Not Modified",
		307 => "Temporary Redirect",
		308 => "Permanent Redirect",

		//4xx
		400 => "Bad Request",
		401 => "Unauthorized",
		402 => "Payment Required",
		403 => "Forbidden",
		404 => "Not Found",
		405 => "Method Not Allowed",
		406 => "Not Acceptable",
		407 => "Proxy Authentication Required",
		408 => "Request Timeout",
		409 => "Conflict",
		410 => "Gone",
		411 => "Length Required",
		412 => "Precondition Failed",
		413 => "Request Entity Too Large",
		414 => "Request-URI Too Long",
		415 => "Unsupported Media Type",
		416 => "Requested Range Not Satisfiable",
		417 => "Expectation Failed",
		425 => "Unordered Collection",
		426 => "Upgrade Required",
		428 => "Precondition Required",
		429 => "Too Many Requests",
		431 => "Request Header Fields Too Large",
		444 => "No Response",
		451 => "Unavailable For Legal Reasons",

		//5xx
		500 => "Internal Server Error",
		501 => "Not Implemented",
		502 => "Bad Gateway",
		503 => "Service Unavailable",
		504 => "Gateway Timeout",
		505 => "HTTP Version Not Supported",
		506 => "Variant Also Negotiates",
		509 => "Bandwidth Limit Exceeded",
		510 => "Not Extended",
		511 => "Network Authentication Required",
		598 => "Network read timeout error",
		599 => "Network connect timeout error",
	);

	/**
	 * Get HTTP version
	 *
	 * @return string '1.0' or '1.1'
	 */
	public static function getHTTPVersion(){
		return self::$HTTP_version;
	}

	/**
	 * Set HTTP version
	 *
	 * @param string $HTTP_version '1.0' or '1.1'
	 */
	public static function setHTTPVersion($HTTP_version){
		self::$HTTP_version = (string)$HTTP_version == "1.0" ? "1.0" : "1.1";
	}


	/**
	 * Get HTTP response codes
	 *
	 * @static
	 *
	 * @param int|null $family [optional] Set 2 for 2xx family, 3 for 3xx family, 4 for 4xx family, 5 for 5xx family. Default: NULL = all families
	 *
	 * @return array
	 */
	public static function getResponseCodes($family = null){
		$family = (int)$family;
		if(!$family){
			return self::$response_codes;
		}

		$output = array();
		foreach(self::$response_codes as $c => $m){
			if($c[0] == $family){
				$output[$c] = $m;
			}
		}
		return $output;
	}

	/**
	 * Returns TRUE if headers were already sent or FALSE if not
	 *
	 * @param string &$file [optional]
	 * @param int &$line [optional]
	 *
	 * @return bool
	 */
	public static function getHeadersSent(&$file = null, &$line = null){
		return @headers_sent($file, $line);
	}

	/**
	 * @throws Http_Headers_Exception
	 */
	public static function checkHeadersNotSent(){
		if(@headers_sent($file, $line)){
			throw new Http_Headers_Exception(
				"HTTP headers were already sent by {$file}:{$line}",
				Http_Headers_Exception::CODE_HEADERS_SENT
			);
		}
	}

	/**
	 * Get message to status code or FALSE if code is not defined
	 *
	 * @param int $code
	 *
	 * @return string|bool
	 */
	public static function getResponseMessage($code){
		return isset(self::$response_codes[$code])
			? self::$response_codes[$code]
			: false;
	}

	/**
	 * Get response status header
	 *
	 * @param int $code
	 * @param null|string $code_message [optional]
	 *
	 * @return bool|string
	 */
	public static function getResponseHeader($code, $code_message = null){
		Debug_Assert::isGreaterOrEqualThan($code, 100);
		if($code_message !== null){
			Debug_Assert::isString($code_message);
		}

		if($code_message === null && isset(self::$response_codes[$code])){
			$code_message = isset(self::$response_codes[$code]);
		}

		return $code_message
			? "HTTP/".self::$HTTP_version." {$code} {$code_message}"
			: "HTTP/".self::$HTTP_version." {$code}";
	}

	/**
	 * @param int $code
	 *
	 * @throws Http_Headers_Exception
	 */
	public static function checkResponseCode($code){
		if(!isset(self::$response_codes[$code])){
			throw new Http_Headers_Exception(
				"Response code '{$code}' not found",
				Http_Headers_Exception::CODE_INVALID_RESPONSE_CODE
			);
		}
	}

	/**
	 * Send response to output
	 *
	 * @param string $content
	 * @param array|null $headers [optional]
	 * @param bool $die [optional] Default: TRUE
	 * @param int $code [optional] Default: 200 - outputs 200 OK response
	 */
	public static function responseOK($content, array $headers = null, $die = true, $code = self::CODE_200_OK){
		self::response($code, $content, $headers, $die);
	}

	/**
	 * Send status header
	 *
	 * @param int $code
	 * @param string $content [optional]
	 * @param array|null $headers [optional]
	 * @param bool $die [optional] Default: TRUE
	 * @param null|string $code_message [optional]
	 *
	 * @throws Http_Headers_Exception
	 */
	public static function response($code, $content = null, array $headers = null, $die = true, $code_message = null){

		if(PHP_SAPI == "cli"){
			if($content){
				echo $content;
			}

			if($die){
				System::shutdown();
			}

			return;
		}

		self::checkHeadersNotSent();
		$header = self::getResponseHeader($code, $code_message);

		try {
			header($header, true, $code);
		} catch(Debug_PHPError $e){
			throw new Http_Headers_Exception(
				"Failed to send response header '{$header}' for HTTP code {$code} - {$e->getMessage()}",
				Http_Headers_Exception::CODE_FAILED_TO_SEND_HEADER
			);
		}

		if($headers){
			self::sendHeaders($headers);
		}

		if($content !== null){
			echo $content;
		}

		if($die){
			System::shutdown();
		}
	}

	/**
	 * @param string $header
	 * @param null|string|int $value [optional]
	 * @param null|bool $replace [optional]
	 */
	public static function sendHeader($header, $value = null, $replace = null){
		self::checkHeadersNotSent();
		if($value !== null){
			$header = "{$header}: {$value}";
		}
		header((string)$header, $replace);
	}

	/**
	 * @param $headers
	 * @param null|bool $replace [optional]
	 *
	 * @throws Http_Headers_Exception
	 */
	public static function sendHeaders(array $headers, $replace = null){
		self::checkHeadersNotSent();

		$header = null;
		try {

			foreach($headers as $header => $value){
				if(is_numeric($header)){
					$header = $value;
				} else {
					$header = "{$header}: {$value}";
				}
				header((string)$header, $replace);
			}

		} catch(Debug_PHPError $e){
			throw new Http_Headers_Exception(
				"Failed to send header '{$header}' - {$e->getMessage()}",
				Http_Headers_Exception::CODE_FAILED_TO_SEND_HEADER
			);
		}
	}


	/**
	 * Redirection - 301 or 302
	 *
	 * @param string $target_URL
	 * @param bool $permanently [optional] Default: FALSE
	 * @param bool $die [optional] Default: TRUE
	 */
	public static function redirect($target_URL, $permanently = false, $die = true){
		self::response(
			$permanently
				? self::CODE_301_MOVED_PERMANENTLY
				: self::CODE_302_FOUND,
			null,
			["Location" => $target_URL],
			$die
		);
	}

	/**
	 * Permanent redirection - 301
	 *
	 * @param string $target_URL
	 * @param bool $die [optional] Default: TRUE
	 */
	public static function movedPermanently($target_URL, $die = true){
		self::redirect($target_URL, true, $die);
	}

	/**
	 * Temporary redirection - 302
	 *
	 * @param string $target_URL
	 * @param bool $die [optional] Default: TRUE
	 */
	public static function movedTemporary($target_URL, $die = true){
		self::redirect($target_URL, false, $die);
	}

	/**
	 * See other - 303
	 *
	 * @param string $target_URL
	 * @param bool $die [optional] Default: TRUE
	 */
	public static function seeOther($target_URL, $die = true){
		self::response(
			self::CODE_303_SEE_OTHER,
			null,
			["Location" => $target_URL],
			$die
		);
	}


	/**
	 * Page not found - 404
	 *
	 * @param string $message [optional]
	 * @param bool $die [optional] Default: TRUE
	 */
	public static function notFound($message = "", $die = true){
		self::response(
			self::CODE_404_NOT_FOUND,
			$message,
			null,
			$die
		);
	}

	/**
	 * Not modified - 304
	 *
	 * @param array|null $headers [optional]
	 * @param string|null $message [optional]
	 * @param bool $die [optional] Default: TRUE
	 */
	public static function notModified(array $headers = null, $message = null, $die = true){
		self::response(
			self::CODE_304_NOT_MODIFIED,
			$message,
			$headers,
			$die
		);
	}

	/**
	 * Refresh current page
	 *
	 * @param bool $die [optional] Default: TRUE
	 */
	public static function refresh($die = true) {
		self::sendHeader("Location", "?#");
		if($die){
			System::shutdown();
		}
	}

	/**
	 * Unauthorized - 401
	 *
	 * @param string $message [optional]
	 * @param bool $die [optional] Default: TRUE
	 */
	public static function unauthorized($message = "", $die = true){
		self::response(
			self::CODE_401_UNAUTHORIZED,
			$message,
			null,
			$die
		);
	}

	/**
	 * Bad request - 400
	 *
	 * @param string $message [optional]
	 * @param bool $die [optional] Default: TRUE
	 */
	public static function badRequest($message = "", $die = true){
		self::response(
			self::CODE_400_BAD_REQUEST,
			$message,
			null,
			$die
		);
	}

	/**
	 * Forbidden - 403
	 *
	 * @param string $message [optional]
	 * @param bool $die [optional] Default: TRUE
	 */
	public static function forbidden($message = "", $die = true){
		self::response(
			self::CODE_403_FORBIDDEN,
			$message,
			null,
			$die
		);
	}

	/**
	 * Internal server error - 500
	 *
	 * @param string $message [optional]
	 * @param bool $die [optional] Default: TRUE
	 */
	public static function internalServerError($message = "", $die = true){
		self::response(
			self::CODE_500_INTERNAL_SERVER_ERROR,
			$message,
			null,
			$die
		);
	}

	/**
	 * Send JSON response
	 *
	 * @param mixed $json_data
	 * @param bool $die [optional] Default: TRUE
	 * @param string $encoding [optional] Default: "utf-8"
	 * @param int $response_code [optional] Default: 200
	 * @param array|null $extra_headers [optional]
	 * @param null|string $response_code_message [optional]
	 */
	public static function responseJSON($json_data, $die = true, $encoding = "utf-8", $response_code = self::CODE_200_OK, array $extra_headers = null, $response_code_message = null){
		$data = json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

		if(!$extra_headers){
			$extra_headers = array();
		}

		$extra_headers["Content-Type"] = "text/javascript;charset={$encoding}";
		$extra_headers["Content-Length"] = strlen($data);

		self::response(
			$response_code,
			$data,
			$extra_headers,
			$die,
			$response_code_message
		);
	}

	/**
	 * Send HTML response
	 *
	 * @param string $html
	 * @param bool $die [optional] Default: TRUE
	 * @param string $encoding [optional] Default: "utf-8"
	 * @param int $response_code [optional] Default: 200
	 * @param array|null $extra_headers [optional]
	 * @param null|string $response_code_message [optional]
	 */
	public static function responseHTML($html, $die = true, $encoding = "utf-8", $response_code = self::CODE_200_OK, array $extra_headers = null, $response_code_message = null){

		if(!$extra_headers){
			$extra_headers = array();
		}

		$extra_headers["Content-Type"] = "text/html;charset={$encoding}";
		$extra_headers["Content-Length"] = strlen($html);

		self::response(
			$response_code,
			$html,
			$extra_headers,
			$die,
			$response_code_message
		);
	}

	/**
	 * Send XML response
	 *
	 * @param string|\DOMDocument|\SimpleXMLElement $xml
	 * @param bool $die [optional] Default: TRUE
	 * @param string $encoding [optional] Default: "utf-8"
	 * @param string $type [optional] Default: text/xml
	 * @param int $response_code [optional] Default: 200
	 * @param array|null $extra_headers [optional]
	 * @param null|string $response_code_message [optional]
	 */
	public static function responseXML($xml, $die = true, $encoding = "utf-8", $type = "text/xml", $response_code = self::CODE_200_OK, array $extra_headers = null, $response_code_message = null){

		if($xml instanceof \DOMDocument){
			$xml->formatOutput = true;
			$xml_string = $xml->saveXML();
		} elseif($xml instanceof \SimpleXMLElement){
			$xml_string = $xml->asXML();
		} else {
			$xml_string = (string)$xml;
		}

		if(!$type){
			$type = "text/xml";
		}

		if(!$extra_headers){
			$extra_headers = array();
		}

		$extra_headers["Content-Type"] = "{$type};charset={$encoding}";
		$extra_headers["Content-Length"] = strlen($xml_string);

		self::response(
			$response_code,
			$xml_string,
			$extra_headers,
			$die,
			$response_code_message
		);
	}

	/**
	 * Send text response
	 *
	 * @param string $text
	 * @param bool $die [optional] Default: TRUE
	 * @param string $encoding [optional] Default: "utf-8"
	 * @param int $response_code [optional] Default: 200
	 * @param array|null $extra_headers [optional]
	 * @param null|string $response_code_message [optional]
	 */
	public static function responseText($text, $die = true, $encoding = "utf-8", $response_code = self::CODE_200_OK, array $extra_headers = null, $response_code_message = null){

		if(!$extra_headers){
			$extra_headers = array();
		}

		$text = (string)$text;
		$extra_headers["Content-Type"] = "text/plain;charset={$encoding}";
		$extra_headers["Content-Length"] = strlen($text);


		self::response(
			$response_code,
			$text,
			$extra_headers,
			$die,
			$response_code_message
		);
	}

}