<?php
namespace Et;
et_require("Object");
class Data_Validation_Result_Error extends Object implements \JsonSerializable {

	/**
	 * @var string
	 */
	protected $error_name;

	/**
	 * @var string
	 */
	protected $error_code;

	/**
	 * @var string
	 */
	protected $error_message = "";

	/**
	 * @param string $error_name
	 * @param string $error_code
	 * @param string $error_message
	 */
	function __construct($error_name, $error_code, $error_message){
		$this->setErrorName($error_name);
		$this->setError($error_code, $error_message);
	}

	/**
	 * @param string $error_name
	 */
	public function setErrorName($error_name) {
		$this->error_name = (string)$error_name;
	}

	/**
	 * @return string
	 */
	public function getErrorName() {
		return $this->error_name;
	}



	/**
	 * @return string
	 */
	public function getErrorCode() {
		return $this->error_code;
	}

	/**
	 * @return string
	 */
	public function getErrorMessage() {
		return $this->error_message;
	}

	/**
	 * @param string $error_code
	 * @param $error_message
	 */
	public function setError($error_code, $error_message) {
		$this->error_code = (string)$error_code;
		$this->error_message = (string)$error_message;
	}

	/**
	 * @return array
	 */
	public function getError(){
		return array($this->getErrorCode(), $this->getErrorMessage());
	}

	/**
	 * @return string
	 */
	function __toString() {
		return $this->getErrorMessage();
	}


	/**
	 * @return array
	 */
	public function jsonSerialize() {
		return array(
			"error_name" => $this->getErrorName(),
			"error_code" => $this->getErrorCode(),
			"error_message" => $this->getErrorMessage()
		);
	}
}