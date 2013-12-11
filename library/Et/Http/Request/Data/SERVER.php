<?php
namespace Et;
/**
 * Representation of $_SERVER
 */
class Http_Request_Data_SERVER extends Http_Request_Data {

	/**
	 * @param array|null $data [optional]
	 */
	function __construct(array $data = null){
		if($data === null){
			$data = $_SERVER;
			if($data instanceof Data_Array){
				$data = $data->getData();
			}
		}
		parent::__construct($data);
	}

	/**
	 * @param string $header
	 * @param null|mixed $default_value [optional]
	 * @return string|null
	 */
	function getHttpHeader($header, $default_value = null){
		$header = "HTTP_" . strtoupper(preg_replace('~[^a-z0-9]~i', "_", $header));
		return $this->getRawScalar($header, $default_value);
	}

	/**
	 * @return string|null
	 */
	function getHost(){
		return $this->getRawScalar("HTTP_HOST");
	}

	/**
	 * @return string|null
	 */
	function getReferer(){
		return $this->getRawScalar("HTTP_REFERER");
	}

	/**
	 * @return string|null
	 */
	function getUserAgent(){
		return $this->getRawScalar("HTTP_USER_AGENT");
	}



}