<?php
namespace Et;
class Http_Request extends Object {

	/**
	 * @var Http_Request_Data_GET
	 */
	protected static $GET;

	/**
	 * @var Http_Request_Data_POST
	 */
	protected static $POST;

	/**
	 * @var Http_Request_Data_SERVER
	 */
	protected static $SERVER;

	/**
	 * @var string
	 */
	protected static $raw_request_data;

	/**
	 * @var bool
	 */
	protected static $initialized = false;


	/**
	 * @var Http_Request_URL
	 */
	protected $URL;

	/**
	 * @var string
	 */
	protected $request_method;


	/**
	 * @param bool $override_super_global_variables [optional]
	 */
	public static function initialize($override_super_global_variables = true){

		if(static::$initialized){
			return;
		}

		if(!$_GET instanceof Http_Request_Data_GET){
			static::$GET = new Http_Request_Data_GET($_GET);
		}

		if(!$_POST instanceof Http_Request_Data_POST){
			static::$POST = new Http_Request_Data_POST($_POST);
		}

		if(!$_SERVER instanceof Http_Request_Data_SERVER){
			static::$SERVER = new Http_Request_Data_SERVER($_SERVER);
		}

		if($override_super_global_variables){
			$_GET = static::$GET;
			$_POST = static::$POST;
			$_SERVER = static::$SERVER;
		}

		static::$initialized = true;
	}

	/**
	 * @param Http_Request_URL|string $URL
	 * @param null|string $request_method
	 */
	function __construct($URL, $request_method){
		Debug_Assert::isStringMatching($request_method, '^[A-Z]+$', "Invalid request method");
		if(!$URL instanceof Http_Request_URL){
			$URL = new Http_Request_URL($URL);
		}
		$this->URL = $URL;
		$this->request_method = $request_method;
	}

	/**
	 * @param bool $include_query_string [optional]
	 *
	 * @return string
	 */
	function getURL($include_query_string = true){
		return $this->URL->getURL($include_query_string);
	}


	/**
	 * @param bool $include_query_string [optional]
	 *
	 * @return string
	 */
	function getURI($include_query_string = true){
		return $this->URL->getPath($include_query_string);
	}

	/**
	 * @return boolean
	 */
	public function getIsHttpsRequest() {
		return $this->URL->getIsHttps();
	}

	/**
	 * @return Http_Request_Data
	 */
	public function getQueryData() {
		return $this->URL->getQueryData();
	}

	/**
	 * @return string
	 */
	public function getQueryString() {
		return $this->URL->getQueryString();
	}

	/**
	 * @return string
	 */
	public function getRequestMethod() {
		return $this->request_method;
	}

	/**
	 * @return Http_Request_URL
	 */
	public function getURLInstance() {
		return $this->URL;
	}


	/**
	 * @param null|string $path [optional]
	 * @param null|mixed $default_value [optional]
	 * @return Http_Request_Data_GET|mixed
	 */
	public static function GET($path = null, $default_value = null){
		if(self::$GET === null){
			self::$GET = new Http_Request_Data_GET();
		}
		if($path === null){
			return self::$GET;
		}
		return self::$GET->getScalar($path, $default_value);
	}

	/**
	 * @param null|string $path [optional]
	 * @param null|mixed $default_value [optional]
	 * @return Http_Request_Data_POST|mixed
	 */
	public static function POST($path = null, $default_value = null){
		if(self::$POST === null){
			self::$POST = new Http_Request_Data_POST();
		}
		if($path === null){
			return self::$POST;
		}
		return self::$POST->getScalar($path, $default_value);
	}

	/**
	 * @param null|string $path [optional]
	 * @param null|mixed $default_value [optional]
	 * @return Http_Request_Data_SERVER|mixed
	 */
	public static function SERVER($path = null, $default_value = null){
		if(self::$SERVER === null){
			self::$SERVER = new Http_Request_Data_SERVER();
		}
		if($path === null){
			return self::$SERVER;
		}
		return self::$SERVER->getScalar($path, $default_value);
	}

	/**
	 * @param string $default_value [optional]
	 *
	 * @return string
	 */
	public static function getReferer($default_value = ""){
		return ET_REQUEST_REFERER === "" ? $default_value : ET_REQUEST_REFERER;
	}

	/**
	 * @param string $default_value [optional]
	 *
	 * @return null|string
	 */
	public static function getRemoteIP($default_value = "unknown"){
		return ET_REQUEST_IP != "unknown"
				? ET_REQUEST_IP
				: (string)$default_value;
	}

	/**
	 * @param string $default_value [optional]
	 *
	 * @return string
	 */
	public static function getUserAgent($default_value = "unknown"){
		return ET_REQUEST_USER_AGENT != "unknown"
			? ET_REQUEST_USER_AGENT
			: (string)$default_value;
	}

	/**
	 * @return string
	 */
	public static function getRawRequestData(){
		if(static::$raw_request_data === null){
			static::$raw_request_data = file_get_contents("php://input");
		}
		return static::$raw_request_data;
	}
}