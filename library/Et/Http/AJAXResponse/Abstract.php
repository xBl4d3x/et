<?php
namespace Et;
class Http_AJAXResponse_Abstract extends Object implements \JsonSerializable {

	const STATUS_OK = "OK";
	const STATUS_ERROR = "ERROR";

	/**
	 * @var string
	 */
	protected $status = self::STATUS_OK;

	/**
	 * @var array
	 */
	protected $errors = array();

	function __construct(){

	}

	/**
	 * @param string $error_code
	 * @param string $error_message
	 * @return static|\Et\Http_AJAXResponse_Abstract
	 */
	function setError($error_code, $error_message){
		$this->errors[$error_code] = (string)$error_message;
		$this->status = self::STATUS_ERROR;
		return $this;
	}

	/**
	 * @param array $errors
	 * @return static|\Et\Http_AJAXResponse_Abstract
	 */
	public function setErrors(array $errors) {
		$this->errors = $errors;
		$this->status = $errors ? self::STATUS_ERROR : self::STATUS_OK;
		return $this;
	}


	/**
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return bool
	 */
	public function isOK(){
		return $this->status == self::STATUS_OK;
	}


	/**
	 * @param int $http_response_code [optional]
	 */
	public function sendResponse($http_response_code = Http_Headers::CODE_200_OK){
		Http_Headers::responseJSON(
			$this,
			true,
			"utf-8",
			$http_response_code
		);
	}


	/**
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->getVisiblePropertiesValues();
	}
}