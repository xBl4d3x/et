<?php
namespace Et;
class Data_Validator_Scalar extends Data_Validator_Abstract {

	const ERR_TOO_SHORT = "too_short";
	const ERR_TOO_LONG = "too_long";

	const DEF_MINIMAL_LENGTH = "minimal_length";
	const DEF_MAXIMAL_LENGTH = "maximal_length";

	/**
	 * @var int|null
	 */
	protected $minimal_length;

	/**
	 * @var int
	 */
	protected $maximal_length;

	/**
	 * @var array
	 */
	protected $error_messages = array(
		self::ERR_TOO_SHORT => "Value is too short, must have {MINIMAL_LENGTH} characters or more",
		self::ERR_TOO_LONG => "Value is too long, must have {MAXIMAL_LENGTH} characters or less"
	);

	/**
	 * @param string $parameter
	 * @param mixed $value
	 */
	protected function setValidatorParameter($parameter, $value){
		switch($parameter){
			case self::DEF_MINIMAL_LENGTH:
				$this->setMinimalLength($value);
				break;

			case self::DEF_MAXIMAL_LENGTH:
				$this->setMaximalLength($value);
				break;

			default:
				parent::setValidatorParameter($parameter, $value);
		}
	}

	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	protected function validateValueLength(&$value, &$error_code = null, &$error_message = null){
		if($this->minimal_length !== null && System::getText((string)$value)->getLength() < $this->minimal_length){
			$error_code = static::ERR_TOO_SHORT;
			$error_message = $this->getErrorMessage($error_code, array("MINIMAL_LENGTH" => $this->minimal_length));
			return false;
		}

		if($this->maximal_length !== null && System::getText((string)$value)->getLength() > $this->maximal_length){
			$error_code = static::ERR_TOO_LONG;
			$error_message = $this->getErrorMessage($error_code, array("MAXIMAL_LENGTH" => $this->maximal_length));
			return false;
		}

		return true;
	}


	/**
	 * @param mixed $value
	 * @return mixed
	 */
	function formatValue($value) {
		return $this->formatValueToScalar($value);
	}

	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	protected function validateValueType(&$value, &$error_code = null, &$error_message = null){
		$new_value = $this->formatValue($value);
		if(($new_value === null || $new_value === false) && $new_value !== $value){
			$error_code = static::ERR_INVALID_TYPE;
			$error_message = $this->getErrorMessage($error_code, array("TYPE" => "scalar value"));
			return false;
		}
		$value = $new_value;
		return parent::validateValueType($value, $error_code, $error_message);
	}

	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	protected function validateValueSpecific(&$value, &$error_code = null, &$error_message = null) {

		return true;
	}
}