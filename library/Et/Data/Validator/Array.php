<?php
namespace Et;
class Data_Validator_Array extends Data_Validator_Abstract {
	
	const VALIDATOR_TYPE_PARAMETER = "type";

	const ERR_TOO_LITTLE_ITEMS = "too_little_items";
	const ERR_TOO_MANY_ITEMS = "too_many_items";
	const ERR_INVALID_ARRAY_KEY = "invalid_array_key";
	const ERR_INVALID_ARRAY_VALUE = "invalid_array_value";

	const DEF_MINIMAL_ITEMS_COUNT = Data_Validator::DEF_MINIMAL_ITEMS_COUNT;
	const DEF_MAXIMAL_ITEMS_COUNT = Data_Validator::DEF_MAXIMAL_ITEMS_COUNT;
	const DEF_KEY_VALIDATOR = Data_Validator::DEF_KEY_VALIDATOR;
	const DEF_VALUE_VALIDATOR = Data_Validator::DEF_VALUE_VALIDATOR;

	/**
	 * @var int|null
	 */
	protected $minimal_items_count;

	/**
	 * @var int|null
	 */
	protected $maximal_items_count;

	/**
	 * @var Data_Validator_Abstract
	 */
	protected $key_validator = array(
		self::VALIDATOR_TYPE_PARAMETER => "String"
	);

	/**
	 * @var Data_Validator_Abstract
	 */
	protected $value_validator = array(
		self::VALIDATOR_TYPE_PARAMETER => "Scalar"
	);


	/**
	 * @var array
	 */
	protected $error_messages = array(
		self::ERR_TOO_LITTLE_ITEMS => "Value must contain {MINIMAL_ITEMS_COUNT} item(s) or more",
		self::ERR_TOO_MANY_ITEMS => "Value must contain {MAXIMAL_ITEMS_COUNT} item(s) or less",
		self::ERR_INVALID_ARRAY_KEY => "Invalid array key '{KEY}' - {ERROR}",
		self::ERR_INVALID_ARRAY_VALUE => "Invalid array value {VALUE} with key '{KEY}' - {ERROR}"
	);

	/**
	 * @param array $validator_parameters
	 * @param bool $reset_validator
	 */
	function setup(array $validator_parameters, $reset_validator = true){
		parent::setup($validator_parameters, $reset_validator);
		if(is_array($this->key_validator)){
			$this->setValidatorParameter("key_validator", $this->key_validator);
		}
		if(is_array($this->value_validator)){
			$this->setValidatorParameter("value_validator", $this->value_validator);
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
			case self::DEF_MINIMAL_ITEMS_COUNT:
				$this->setMinimalItemsCount($value);
				return;

			case self::DEF_MAXIMAL_VALUE:
			case self::DEF_MAXIMAL_ITEMS_COUNT:
				$this->setMaximalItemsCount($value);
				return;

			case self::DEF_KEY_VALIDATOR:
				if($value instanceof Data_Validator_Abstract){
					$this->setKeyValidator($value);
					return;
				}

				if(!is_array($value)){
					$value = array(self::VALIDATOR_TYPE_PARAMETER => $value);
				}

				if(!isset($value["type"])){
					throw new Data_Validator_Exception(
						"Missing key validator type in definition",
						Data_Validator_Exception::CODE_INVALID_VALIDATOR_TYPE
					);
				}

				$value = Data_Validator::getValidatorInstance($value["type"], $value);
				$this->setKeyValidator($value);

				return;

			case self::DEF_VALUE_VALIDATOR:
				if($value instanceof Data_Validator_Abstract){
					$this->setValueValidator($value);
					return;
				}

				if(!is_array($value)){
					$value = array(self::VALIDATOR_TYPE_PARAMETER => $value);
				}

				if(!isset($value["type"])){
					throw new Data_Validator_Exception(
						"Missing value validator type in definition",
						Data_Validator_Exception::CODE_INVALID_VALIDATOR_TYPE
					);
				}

				$value = Data_Validator::getValidatorInstance($value["type"], $value);
				$this->setValueValidator($value);
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
		if(!is_array($value)){
			$error_code = static::ERR_INVALID_TYPE;
			$error_message = $this->getErrorMessage($error_code, array("TYPE" => "array value"));
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
	protected function validateItemsCount(&$value, &$error_code = null, &$error_message = null){

		if($this->minimal_items_count !== null && count($value) < $this->minimal_items_count){
			$error_code = static::ERR_TOO_LITTLE_ITEMS;
			$error_message = $this->getErrorMessage($error_code, array("MINIMAL_ITEMS_COUNT" => $this->minimal_items_count));
			return false;
		}

		if($this->maximal_items_count !== null && count($value) > $this->maximal_items_count){
			$error_code = static::ERR_TOO_MANY_ITEMS;
			$error_message = $this->getErrorMessage($error_code, array("MAXIMAL_ITEMS_COUNT" => $this->maximal_items_count));
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
	protected function validateKeysAndValues(&$value, &$error_code = null, &$error_message = null){

		foreach($value as $k => $v){
			if(!$this->key_validator->validateValue($k, $e, $m)){
				$error_code = static::ERR_INVALID_ARRAY_KEY;
				$error_message = $this->getErrorMessage($error_code, array("KEY" => $k, "ERROR" => $m));
				return false;
			}

			if(!$this->value_validator->validateValue($v, $e, $m)){
				$error_code = static::ERR_INVALID_ARRAY_VALUE;
				$error_message = $this->getErrorMessage($error_code, array("VALUE" => print_r($v, true), "KEY" => $k, "ERROR" => $m));
				return false;
			}
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
		return $this->validateItemsCount($value, $error_code, $error_message) &&
				$this->validateKeysAndValues($value, $error_code, $error_message);
	}

	/**
	 * @param mixed $value
	 * @return bool
	 */
	protected function isEmptyValue(&$value){
		return !$value;
	}

	/**
	 * @param Data_Validator_Abstract $key_validator
	 * @throws Data_Validator_Exception
	 */
	public function setKeyValidator(Data_Validator_Abstract $key_validator) {
		if( !$key_validator instanceof Data_Validator_String &&
			!$key_validator instanceof Data_Validator_Int &&
			!$key_validator instanceof Data_Validator_Date &&
			!$key_validator instanceof Data_Validator_DateTime &&
			!$key_validator instanceof Data_Validator_Locale
		){
			throw new Data_Validator_Exception(
				"Key validator must be String, Int, Locale, Date or DateTime",
				Data_Validator_Exception::CODE_INVALID_VALIDATOR_DEFINITION
			);
		}

		$this->key_validator = $key_validator;
	}

	/**
	 * @param int|null $maximal_items_count
	 */
	public function setMaximalItemsCount($maximal_items_count) {
		if($maximal_items_count !== null){
			$maximal_items_count = max(0, (int)$maximal_items_count);
		}
		$this->maximal_items_count = $maximal_items_count;
	}

	/**
	 * @param int|null $minimal_items_count
	 */
	public function setMinimalItemsCount($minimal_items_count) {
		if($minimal_items_count !== null){
			$minimal_items_count = max(0, (int)$minimal_items_count);
		}
		$this->minimal_items_count = $minimal_items_count;
	}

	/**
	 * @param Data_Validator_Abstract $value_validator
	 * @throws Data_Validator_Exception
	 */
	public function setValueValidator(Data_Validator_Abstract $value_validator) {

		if( !$value_validator instanceof Data_Validator_Scalar &&
			!$value_validator instanceof Data_Validator_Locale &&
			!$value_validator instanceof Data_Validator_Date &&
			!$value_validator instanceof Data_Validator_DateTime
		){
			throw new Data_Validator_Exception(
				"Value type must be scalar, Locale, Date or DateTime",
				Data_Validator_Exception::CODE_INVALID_VALIDATOR_DEFINITION
			);
		}

		$this->value_validator = $value_validator;
	}



	/**
	 * @param mixed $value
	 * @return mixed
	 */
	function formatValue($value) {
		$this->assert()->isArray($value);

		foreach($value as &$v){
			$v = $this->value_validator->formatValue($v);
		}

		return $value;
	}

	/**
	 * @return \Et\Data_Validator_Abstract
	 */
	public function getKeyValidator() {
		return $this->key_validator;
	}

	/**
	 * @return int|null
	 */
	public function getMaximalItemsCount() {
		return $this->maximal_items_count;
	}

	/**
	 * @return int|null
	 */
	public function getMinimalItemsCount() {
		return $this->minimal_items_count;
	}

	/**
	 * @return \Et\Data_Validator_Abstract
	 */
	public function getValueValidator() {
		return $this->value_validator;
	}


}