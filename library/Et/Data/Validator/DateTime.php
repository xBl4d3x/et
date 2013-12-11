<?php
namespace Et;
class Data_Validator_DateTime extends Data_Validator_Abstract {

	const ERR_INVALID_DATETIME = "invalid_datetime";
	const ERR_DATETIME_TOO_LOW = "datetime_too_low";
	const ERR_DATETIME_TOO_HIGH = "datetime_too_high";
	
	const DEF_MINIMAL_DATETIME = Data_Validator::DEF_MINIMAL_DATETIME;
	const DEF_MAXIMAL_DATETIME = Data_Validator::DEF_MAXIMAL_DATETIME;

	/**
	 * @var array
	 */
	protected $error_messages = array(
		self::ERR_INVALID_DATETIME => "Invalid date and time",
		self::ERR_DATETIME_TOO_LOW => "Date and time is too low - must be greater than {DATETIME}",
		self::ERR_DATETIME_TOO_HIGH => "Date and time is too high - must be lower than {DATETIME}"
	);

	/**
	 * @var Locales_DateTime|null
	 */
	protected $minimal_datetime;

	/**
	 * @var Locales_DateTime|null
	 */
	protected $maximal_datetime;

	/**
	 * @param array $validator_parameters
	 * @param bool $reset_validator
	 */
	function setup(array $validator_parameters, $reset_validator = true){
		parent::setup($validator_parameters, $reset_validator);

		if($this->minimal_datetime !== null && !$this->minimal_datetime instanceof Locales_DateTime){
			$this->setValidatorParameter("minimal_datetime", $this->minimal_datetime);
		}

		if($this->maximal_datetime !== null && !$this->maximal_datetime instanceof Locales_DateTime){
			$this->setValidatorParameter("maximal_datetime", $this->maximal_datetime);
		}
	}

	/**
	 * @param string $parameter
	 * @param mixed $value
	 * @throws Data_Validator_Exception
	 */
	protected function setValidatorParameter($parameter, $value){
		switch($parameter){
			case self::DEF_MINIMAL_VALUE:
			case self::DEF_MINIMAL_DATETIME:
				$this->setMinimalDatetime($this->formatValue($value));
				return;

			case self::DEF_MAXIMAL_VALUE:
			case self::DEF_MAXIMAL_DATETIME:
				$this->setMaximalDatetime($this->formatValue($value));
				return;

			default:
		}

		parent::setValidatorParameter($parameter, $value);
	}


	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	protected function validateValueType(&$value, &$error_code = null, &$error_message = null){
		if($value instanceof Locales_Date){
			$value = Locales_DateTime::getInstanceFromTimestamp($value->getTimestamp());
			return true;
		}

		if($value instanceof Locales_DateTime){
			return true;
		}

		if($value === null){
			return true;
		}

		if(!parent::validateValueType($value, $error_code, $error_message)){
			return false;
		}

		$value = $this->formatValue($value);
		if($value !== null && !$value){
			$error_code = self::ERR_INVALID_DATETIME;
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
	protected function validateDateTimeRange(&$value, &$error_code = null, &$error_message = null){
		if($this->minimal_datetime && (!$value instanceof Locales_DateTime || $value->getTimestamp() < $this->minimal_datetime->getTimestamp())){
			$error_code = self::ERR_DATETIME_TOO_HIGH;
			$error_message = $this->getErrorMessage($error_code, array("DATETIME" => $this->minimal_datetime->format()));
			return false;
		}

		if($this->maximal_datetime && (!$value instanceof Locales_DateTime || $value->getTimestamp() < $this->maximal_datetime->getTimestamp())){
			$error_code = self::ERR_DATETIME_TOO_LOW;
			$error_message = $this->getErrorMessage($error_code, array("DATETIME" => $this->maximal_datetime->format()));
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
	protected function validateValueSpecific(&$value, &$error_code = null, &$error_message = null) {
		return $this->validateDateTimeRange($value, $error_code, $error_message);
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	function formatValue($value) {
		if($value instanceof Locales_Date){
			return Locales_DateTime::getInstanceFromTimestamp($value->getTimestamp());
		}

		if($value instanceof Locales_DateTime){
			return $value;
		}

		if(!trim($value)){
			return null;
		}

		if(is_numeric($value)){
			return Locales_DateTime::getInstanceFromTimestamp($value);
		} else {
			return Locales_DateTime::getInstance($value);
		}
	}

	/**
	 * @param \Et\Locales_DateTime|null $maximal_date
	 */
	public function setMaximalDatetime(Locales_DateTime $maximal_date = null) {
		$this->maximal_datetime = $maximal_date;
	}

	/**
	 * @return \Et\Locales_DateTime|null
	 */
	public function getMaximalDatetime() {
		return $this->maximal_datetime;
	}

	/**
	 * @param \Et\Locales_DateTime|null $minimal_date
	 */
	public function setMinimalDatetime(Locales_DateTime $minimal_date = null) {
		$this->minimal_datetime = $minimal_date;
	}

	/**
	 * @return \Et\Locales_DateTime|null
	 */
	public function getMinimalDatetime() {
		return $this->minimal_datetime;
	}


}