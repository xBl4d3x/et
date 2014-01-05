<?php
namespace Et;
class Data_Validator_Date extends Data_Validator_Abstract {

	const ERR_INVALID_DATE = "invalid_date";
	const ERR_DATE_TOO_LOW = "date_too_low";
	const ERR_DATE_TOO_HIGH = "date_too_high";

	const DEF_MINIMAL_DATE = Data_Validator::DEF_MINIMAL_DATE;
	const DEF_MAXIMAL_DATE = Data_Validator::DEF_MAXIMAL_DATE;

	/**
	 * @var array
	 */
	protected $error_messages = array(
		self::ERR_INVALID_DATE => "Invalid date",
		self::ERR_DATE_TOO_LOW => "Date is too low - must be greater than {DATE}",
		self::ERR_DATE_TOO_HIGH => "Date is too high - must be lower than {DATE}"
	);

	/**
	 * @var Locales_Date|null
	 */
	protected $minimal_date;

	/**
	 * @var Locales_Date|null
	 */
	protected $maximal_date;

	/**
	 * @param array $validator_parameters
	 * @param bool $reset_validator
	 */
	function setup(array $validator_parameters, $reset_validator = true){
		parent::setup($validator_parameters, $reset_validator);

		if($this->minimal_date !== null && !$this->minimal_date instanceof Locales_Date){
			$this->setValidatorParameter("minimal_date", $this->minimal_date);
		}

		if($this->maximal_date !== null && !$this->maximal_date instanceof Locales_Date){
			$this->setValidatorParameter("maximal_date", $this->maximal_date);
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
			case self::DEF_MINIMAL_DATE:
				$this->setMinimalDate($this->formatValue($value));
				return;

			case self::DEF_MAXIMAL_VALUE:
			case self::DEF_MAXIMAL_DATE:
				$this->setMaximalDate($this->formatValue($value));
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
			return true;
		}

		if($value instanceof Locales_DateTime){
			$value = Locales_Date::getInstance($value->getTimestamp());
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
			$error_code = self::ERR_INVALID_DATE;
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
	protected function validateDateRange(&$value, &$error_code = null, &$error_message = null){
		if($this->minimal_date && (!$value instanceof Locales_Date || $value->getTimestamp() < $this->minimal_date->getTimestamp())){
			$error_code = self::ERR_DATE_TOO_HIGH;
			$error_message = $this->getErrorMessage($error_code, array("DATE" => $this->minimal_date->format()));
			return false;
		}

		if($this->maximal_date && (!$value instanceof Locales_Date || $value->getTimestamp() < $this->maximal_date->getTimestamp())){
			$error_code = self::ERR_DATE_TOO_LOW;
			$error_message = $this->getErrorMessage($error_code, array("DATE" => $this->maximal_date->format()));
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
		return $this->validateDateRange($value, $error_code, $error_message);
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	function formatValue($value) {
		if($value instanceof Locales_Date){
			return $value;
		}

		if($value instanceof Locales_DateTime){
			$value = Locales_Date::getInstance($value->getTimestamp());
			return $value;
		}

		if(!trim($value)){
			return null;
		}

		return Locales_Date::getInstance($value);
	}

	/**
	 * @param \Et\Locales_Date|null $maximal_date
	 */
	public function setMaximalDate(Locales_Date $maximal_date = null) {
		$this->maximal_date = $maximal_date;
	}

	/**
	 * @return \Et\Locales_Date|null
	 */
	public function getMaximalDate() {
		return $this->maximal_date;
	}

	/**
	 * @param \Et\Locales_Date|null $minimal_date
	 */
	public function setMinimalDate(Locales_Date $minimal_date = null) {
		$this->minimal_date = $minimal_date;
	}

	/**
	 * @return \Et\Locales_Date|null
	 */
	public function getMinimalDate() {
		return $this->minimal_date;
	}


}