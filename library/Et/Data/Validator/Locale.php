<?php
namespace Et;
class Data_Validator_Locale extends Data_Validator_Abstract {

	const ERR_INVALID_LOCALE = "invalid_locale";


	/**
	 * @var array
	 */
	protected $error_messages = array(
		self::ERR_INVALID_LOCALE => "Invalid locale"
	);

	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	protected function validateValueType(&$value, &$error_code = null, &$error_message = null){
		if($value instanceof Locales_Locale){
			return true;
		}

		if(!parent::validateValueType($value, $error_code, $error_message)){
			return false;
		}

		if(!$value){
			return true;
		}

		if(!Locales::getLocaleExists($value)){
			$error_code = static::ERR_INVALID_LOCALE;
			$error_message = $this->getErrorMessage($error_code);
			return false;
		}

		$value = new Locales_Locale($value);

		return true;
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

	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	protected function validateAllowedValues(&$value, &$error_code = null, &$error_message = null){
		if($this->allowed_values === null){
			return true;
		}

		$allowed_values = $this->getAllowedValues();
		if(isset($allowed_values[(string)$value])){
			return true;
		}

		$error_code = static::ERR_NOT_ALLOWED_VALUE;
		$error_message = $this->getErrorMessage($error_code, array("ALLOWED_VALUES" => "'" . implode("', '", array_keys($allowed_values)) . "'"));

		return false;
	}


	/**
	 * @param mixed $value
	 * @return mixed
	 */
	function formatValue($value) {
		if($value instanceof Locales_Locale){
			return $value;
		}

		if(trim($value) === ""){
			return null;
		}

		return Locales::getLocale($value);
	}
}