<?php
namespace Et;
class Data_Validator_Email extends Data_Validator_String {

	const ERR_INVALID_EMAIL = "invalid_email";
	
	const DEF_CHECK_MX_RECORD = Data_Validator::DEF_CHECK_MX_RECORD;

	/**
	 * @var bool
	 */
	protected $check_mx_record = true;

	/**
	 * @var array
	 */
	protected $error_messages = array(
		self::ERR_TOO_SHORT => "E-mail is too short, must have {MINIMAL_LENGTH} characters or more",
		self::ERR_TOO_LONG => "E-mail is too long, must have {MAXIMAL_LENGTH} characters or less",
		self::ERR_INVALID_EMAIL => "Invalid e-mail"
	);

	/**
	 * @param string $parameter
	 * @param mixed $value
	 */
	protected function setValidatorParameter($parameter, $value){
		switch($parameter){
			case self::DEF_CHECK_MX_RECORD:
				$this->setCheckMxRecord($value);
				return;
		}

		parent::setValidatorParameter($parameter, $value);
	}

	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	protected function validateMX(&$value, &$error_code = null, &$error_message = null){

		if(!$this->check_mx_record){
			return true;
		}

		list(, $domain) = explode("@", $value);
		if(!getmxrr($domain, $mx_hosts)){
			$error_code = static::ERR_INVALID_EMAIL;
			$error_message = $this->getErrorMessage($error_code);
			return false;
		}

		return true;
	}

	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	protected function validateEmailFormat(&$value, &$error_code = null, &$error_message = null){

		if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
			$error_code = self::ERR_INVALID_FORMAT;
			$error_message = $this->getErrorMessage($error_code, array("FORMAT" => "valid e-mail format"));
			return false;
		}

		return true;
	}


	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	protected function validateValueSpecific(&$value, &$error_code = null, &$error_message = null){
		return
			parent::validateValueSpecific($value, $error_code, $error_message) &&
			$this->validateEmailFormat($value, $error_code, $error_message) &&
			$this->validateMX($value, $error_code, $error_message);
	}


	/**
	 * @return boolean
	 */
	public function getCheckMxRecord() {
		return $this->check_mx_record;
	}

	/**
	 * @param boolean $check_mx_record
	 */
	public function setCheckMxRecord($check_mx_record) {
		$this->check_mx_record = (bool)$check_mx_record;
	}



}