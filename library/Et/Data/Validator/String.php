<?php
namespace Et;
class Data_Validator_String extends Data_Validator_Scalar {

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
		$value = (string)$value;
		return true;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	function formatValue($value) {
		return (string)$value;
	}

	/**
	 * @param string $parameter
	 * @param mixed $value
	 */
	protected function setValidatorParameter($parameter, $value){
		switch($parameter){
			case self::DEF_MINIMAL_VALUE:
				$this->setMinimalLength($value);
				break;

			case self::DEF_MAXIMAL_VALUE:
				$this->setMaximalLength($value);
				break;

			default:
				parent::setValidatorParameter($parameter, $value);
		}
	}

}