<?php
namespace Et;
class Data_Validator_Int extends Data_Validator_Scalar {

	const ERR_TOO_LOW = "too_low";
	const ERR_TOO_HIGH = "too_high";

	/**
	 * @var int|null
	 */
	protected $minimal_value;

	/**
	 * @var int|null
	 */
	protected $maximal_value;

	/**
	 * @var array
	 */
	protected $error_messages = array(
		self::ERR_TOO_LOW => "Value is too low, must be {MINIMAL_VALUE} or greater",
		self::ERR_TOO_HIGH => "Value is too high, must be {MAXIMAL_VALUE} or lower",
		self::ERR_TOO_SHORT => "Number is too short, must have {MINIMAL_LENGTH} characters or more",
		self::ERR_TOO_LONG => "Number is too long, must have {MAXIMAL_LENGTH} characters or less",
	);

	/**
	 * @param string $parameter
	 * @param mixed $value
	 */
	protected function setValidatorParameter($parameter, $value){
		switch($parameter){
			case self::DEF_MINIMAL_VALUE:
				$this->setMinimalValue($value);
				return;

			case self::DEF_MAXIMAL_VALUE:
				$this->setMaximalValue($value);
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
		if(!parent::validateValueType($value, $error_code, $error_message)){
			return false;
		}
		$value = (int)$value;
		return true;
	}

	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	protected function validateValueSize(&$value, &$error_code = null, &$error_message = null){

		if($this->minimal_value !== null && $value < $this->minimal_value){
			$error_code = static::ERR_TOO_LOW;
			$error_message = $this->getErrorMessage($error_code, array("MINIMAL_VALUE" => $this->minimal_value));
			return false;
		}

		if($this->maximal_value !== null && $value > $this->maximal_value){
			$error_code = static::ERR_TOO_HIGH;
			$error_message = $this->getErrorMessage($error_code, array("MAXIMAL_VALUE" => $this->maximal_value));
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
		return parent::validateValueSpecific($value, $error_code, $error_message) &&
				$this->validateValueSize($value, $error_code, $error_message);
	}

	/**
	 * @return int|null
	 */
	public function getMaximalValue() {
		return $this->maximal_value;
	}

	/**
	 * @return int|null
	 */
	public function getMinimalValue() {
		return $this->minimal_value;
	}

	/**
	 * @param int|null $maximal_value
	 */
	public function setMaximalValue($maximal_value) {
		if($maximal_value !== null){
			$maximal_value = (int)$maximal_value;
		}
		$this->maximal_value = $maximal_value;
	}

	/**
	 * @param int|null $minimal_value
	 */
	public function setMinimalValue($minimal_value) {
		if($minimal_value !== null){
			$minimal_value = (int)$minimal_value;
		}
		$this->minimal_value = $minimal_value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	function formatValue($value) {
		return (int)$value;
	}
}