<?php
namespace Et;
class Http_Request_URL extends Object {

	const TYPE_ABSOLUTE = "absolute";
	const TYPE_RELATIVE = "relative";
	const TYPE_SCHEMELESS = "schemeless";

	/**
	 * @var string
	 */
	protected $scheme;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $host_name;

	/**
	 * @var int
	 */
	protected $port;

	/**
	 * @var bool
	 */
	protected $is_https = false;

	/**
	 * @var string
	 */
	protected $http_user = "";

	/**
	 * @var string
	 */
	protected $http_password = "";

	/**
	 * @var string
	 */
	protected $path = "/";

	/**
	 * @var Data_Array
	 */
	protected $query_data;

	/**
	 * @param string $URL
	 * @param Data_Array|array|null $query_data [optional]
	 */
	function __construct($URL, $query_data = null){
		if(!$query_data instanceof Data_Array){
			$query_data = new Data_Array(is_array($query_data) ? $query_data : array());
		}
		$this->query_data = $query_data;
		$this->parseURL($URL);
	}


	/**
	 * @param string $URL
	 * @throws Http_Request_URL_Exception
	 */
	protected function parseURL($URL){
		$URL = trim($URL);
		Debug_Assert::isNotEmpty($URL);

		if(preg_match('~^(http[s]?)://([^/]+)(/.*)?$~', $URL, $m)) {

			array_shift($m);
			$this->type = self::TYPE_ABSOLUTE;
			$this->scheme = array_shift($m);
			$this->is_https = $this->scheme == "https";
			$host_data = array_shift($m);
			$path = $m ? array_shift($m) : "/";

		} elseif(preg_match('~^//([^/]+)(/.*)?$~', $URL, $m)) {

			$this->type = self::TYPE_SCHEMELESS;
			$host_data = array_shift($m);
			$path = $m ? array_shift($m) : "/";

		} elseif(preg_match('~^(/.*)$~', $URL, $m)) {

			$this->type = self::TYPE_RELATIVE;
			$host_data = "";
			$path = $URL;

		} else {

			throw new Http_Request_URL_Exception(
				"URL '{$URL}' has invalid format",
				Http_Request_URL_Exception::CODE_INVALID_URL
			);
		}

		if($host_data){
			$this->parseHostData($host_data);
		}

		if($path != "/"){
			$this->parsePath($path);
		} else {
			$this->path = $path;
		}
	}

	/**
	 * @param string $host_data
	 */
	protected function parseHostData($host_data){
		if(strpos($host_data, "@") !== false){
			list($credentials, $host_data) = explode("@", $host_data, 2);
			if(strpos($credentials, ":") !== false){
				list($this->http_user, $this->http_password) = explode(":", $credentials, 2);
			} else {
				list($this->http_user) = $credentials;
			}
		}

		if(strpos($host_data, ":") !== false){
			list($this->host_name, $this->port) = explode(":", $host_data, 2);
		} else {
			$this->host_name = $host_data;
		}
	}

	/**
	 * @param string $path
	 */
	protected function parsePath($path){
		if(strpos($path, "?") !== false){
			list($path, $query) = explode("?", $path, 2);
			if($query !== ""){
				@parse_str($query, $query_data);
				if(is_array($query_data) && $query_data){
					$current_data = $this->query_data->getData();
					foreach($current_data as $k => $v){
						$query_data[$k] = $v;
					}
					$this->query_data->setData($query_data);
				}
			}
		}
		$this->path = $path;
	}

	/**
	 * @param string $URL
	 * @param Data_Array|array|null $query_data [optional]
	 *
	 * @return Http_Request_URL
	 */
	public static function getInstance($URL, $query_data = null){
		return new static((string)$URL, $query_data);
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}


	/**
	 * @param bool $include_query_string [optional]
	 * @return string
	 */
	function getURL($include_query_string = true){
		if($this->type == self::TYPE_ABSOLUTE){
			$URL = "{$this->scheme}://";
		} elseif($this->type == self::TYPE_SCHEMELESS){
			$URL = "//";
		} else {
			$URL = "";
		}

		if($this->http_user){
			$URL .= $this->http_user;
			if($this->http_password){
				$URL .= ":{$this->http_password}";
			}
			$URL .= "@";
		}

		if($this->host_name){
			$URL .= $this->host_name;
			if($this->port){
				$URL .= ":{$this->port}";
			}
		}

		if($this->path){
			$URL .= $this->path;
		}

		if($include_query_string && $this->query_data->hasData()){
			$URL .= "?" . $this->getQueryString();
		}

		return $URL;
	}


	/**
	 * @return string|null
	 */
	public function getHostName() {
		return $this->host_name;
	}

	/**
	 * @return string|null
	 */
	public function getHttpPassword() {
		return $this->http_password;
	}

	/**
	 * @return string|null
	 */
	public function getHttpUser() {
		return $this->http_user;
	}

	/**
	 * @return int|null
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * @return Data_Array
	 */
	public function getQueryData() {
		return $this->query_data;
	}

	/**
	 * @param Data_Array|array $query_data
	 * @param bool $replace_current [optional]
	 */
	public function setQueryData($query_data, $replace_current = true){
		if(!$query_data instanceof Data_Array){
			$query_data = new Data_Array($query_data);
		}
		if($replace_current){
			$this->query_data = $query_data;
		} else {
			$this->query_data->mergeData($query_data->getData());
		}
	}

	/**
	 * @param string $parameter
	 * @param mixed $value
	 */
	public function setQueryParameter($parameter, $value){
		$this->getQueryData()->setValue($parameter, $value);
	}

	/**
	 * @param string $parameter
	 * @return bool
	 */
	public function removeQueryParameter($parameter){
		return $this->getQueryData()->removeValue($parameter);
	}

	/**
	 * @param string $parameter
	 * @return bool
	 */
	public function getQueryParameterExists($parameter){
		return $this->getQueryData()->getValueExists($parameter);
	}

	/**
	 * @param string $parameter
	 * @param null|mixed $default_value [optional]
	 * @return mixed|null
	 */
	public function getQueryParameter($parameter, $default_value = null){
		return $this->getQueryData()->getRawValue($parameter, $default_value);
	}

	/**
	 * @param bool $url_encoded
	 *
	 * @return string
	 */
	public function getQueryString($url_encoded = true){

		if(!$this->query_data->hasData()){
			return "";
		}

		$query = http_build_query($this->query_data->getData());
		if($url_encoded){
			return $query;
		}

		return urldecode($query);
	}

	/**
	 * @return string
	 */
	public function getScheme() {
		return $this->scheme;
	}

	/**
	 * @return boolean
	 */
	public function getIsHttps() {
		return $this->is_https;
	}

	/**
	 * @param bool $include_query_string [optional]
	 *
	 * @return string
	 */
	public function getPath($include_query_string = true) {
		if(!$include_query_string || !$this->query_data->hasData()){
			return $this->path;
		}
		return $this->path . "?" . $this->getQueryString();
	}

	/**
	 * @return string
	 */
	function __toString(){
		return $this->getURL();
	}


}