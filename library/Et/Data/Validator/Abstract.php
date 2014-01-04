<?php
namespace Et;
abstract class Data_Validator_Abstract extends Object {

	const ERR_NOT_DEFINED = "not_defined";
	const ERR_EMPTY = "empty";
	const ERR_INVALID_FORMAT = "invalid_format";
	const ERR_INVALID_TYPE = "invalid_type";
	const ERR_NOT_ALLOWED_VALUE = "not_allowed_value";
	const ERR_OTHER = "other";
	const ERR_TOO_SHORT = "too_short";
	const ERR_TOO_LONG = "too_long";


	const DEF_REQUIRED = Data_Validator::DEF_REQUIRED;
	const DEF_ALLOWED_VALUES = Data_Validator::DEF_ALLOWED_VALUES;
	const DEF_VALIDATION_PATTERN = Data_Validator::DEF_VALIDATION_PATTERN;
	const DEF_VALIDATION_CALLBACK = Data_Validator::DEF_VALIDATION_CALLBACK;
	const DEF_ERROR_MESSAGES = Data_Validator::DEF_ERROR_MESSAGES;
	const DEF_MINIMAL_VALUE = Data_Validator::DEF_MINIMAL_VALUE;
	const DEF_MAXIMAL_VALUE = Data_Validator::DEF_MAXIMAL_VALUE;
	const DEF_MINIMAL_LENGTH = "minimal_length";
	const DEF_MAXIMAL_LENGTH = "maximal_length";

	/**
	 * @var array
	 */
	protected static $common_error_messages = array(
		self::ERR_EMPTY => "Value may not be empty",
		self::ERR_INVALID_FORMAT => "Value has invalid format - {FORMAT} required",
		self::ERR_INVALID_TYPE => "Value has invalid type, {TYPE} required",
		self::ERR_NOT_ALLOWED_VALUE => "Value is not allowed. Allowed values: {ALLOWED_VALUES}",
		self::ERR_OTHER => "Value is not valid - {REASON}",
		self::ERR_TOO_SHORT => "Value is too short, must have {MINIMAL_LENGTH} characters or more",
		self::ERR_TOO_LONG => "Value is too long, must have {MAXIMAL_LENGTH} characters or less"
	);

	/**
	 * @var bool
	 */
	protected $allow_empty_value = true;

	/**
	 * @var array|callable|null
	 */
	protected $allowed_values;

	/**
	 * @var string|null
	 */
	protected $validation_pattern;

	/**
	 * @var callable|null
	 */
	protected $validation_callback;

	/**
	 * @var array
	 */
	protected $error_messages = array();


	/**
	 * @var int|null
	 */
	protected $minimal_length;

	/**
	 * @var int
	 */
	protected $maximal_length;

	/**
	 * @var Data_Validation_Result_Error
	 */
	protected $last_validation_error;

	/**
	 * @param array $validator_parameters [optional]
	 */
	function __construct(array $validator_parameters = array()){
		$this->setErrorMessages(static::$common_error_messages);
		$this->setup($validator_parameters, false);
	}

	/**
	 * @return Data_Validation_Result_Error|null
	 */
	public function getLastValidationError(){
		return $this->last_validation_error;
	}

	public function resetValidationError(){
		$this->last_validation_error = null;
	}

	/**
	 * @param array $validator_parameters
	 * @param bool $reset_validator
	 */
	function setup(array $validator_parameters, $reset_validator = true){
		if($reset_validator){
			$this->resetValidatorParameters();
		}

		foreach($validator_parameters as $parameter => $value){
			if($parameter[0] == "_" || !property_exists($this, $parameter)){
				continue;
			}
			$this->setValidatorParameter($parameter, $value);
		}
	}

	/**
	 * @param string $parameter
	 * @param mixed $value
	 */
	protected function setValidatorParameter($parameter, $value){
		switch($parameter){
			case self::DEF_ERROR_MESSAGES:
				$this->setErrorMessages($value);
				break;

			case self::DEF_REQUIRED:
				$this->setAllowEmptyValue($value);
				break;

			case self::DEF_ALLOWED_VALUES:
				$this->setAllowedValues($value);
				break;

			case self::DEF_VALIDATION_PATTERN:
				$this->setValidationPattern($value);
				break;

			case self::DEF_VALIDATION_CALLBACK:
				$this->setValidationCallback($value);
				break;
		}
	}

	function resetValidatorParameters(){
		$class_vars = get_class_vars(get_class($this));
		$object_vars = get_object_vars($this);
		foreach($object_vars as $var => $value){
			if($var == "error_messages"){
				continue;
			}
			$this->{$var} = $class_vars[$var];
		}
	}



	/**
	 * @param array $error_messages
	 */
	function setErrorMessages(array $error_messages){
		$this->error_messages = array_merge($this->error_messages, $error_messages);
	}

	/**
	 * @return array
	 */
	function getErrorMessages(){
		return $this->error_messages;
	}

	/**
	 * @param string $error_code
	 * @param array $error_message_data [optional]
	 * @return string
	 * @throws Data_Validator_Exception
	 */
	function getErrorMessage($error_code, array $error_message_data = array()){
		$this->checkErrorCodeExists($error_code);

		if(!$error_message_data){
			return $this->error_messages[$error_code];
		}

		return System::getText($this->error_messages[$error_code])->replaceData($error_message_data);
	}

	/**
	 * @param string $error_code
	 * @return bool
	 */
	function getErrorCodeExists($error_code){
		$error_messages = $this->getErrorMessages();
		return isset($error_messages[$error_code]);
	}

	/**
	 * @param string $error_code
	 * @throws Data_Validator_Exception
	 */
	function checkErrorCodeExists($error_code){
		if(!$this->getErrorCodeExists($error_code)){
			throw new Data_Validator_Exception(
				"Invalid error code '{$error_code}' specified, only '" . implode("', '", array_keys($this->getErrorMessages())) . "' are allowed",
				Data_Validator_Exception::CODE_INVALID_ERROR_CODE
			);
		}
	}

	/**
	 * @return boolean
	 */
	public function getAllowEmptyValue() {
		return $this->allow_empty_value;
	}

	/**
	 * @throws Data_Validator_Exception
	 * @return array|null
	 */
	public function getAllowedValues() {
		if(!is_callable($this->allowed_values)){
			return $this->allowed_values;
		}

		$allowed_values = call_user_func($this->allowed_values);
		if(!is_array($allowed_values)){
			throw new Data_Validator_Exception(
				"List of allowed values must be array",
				Data_Validator_Exception::CODE_INVALID_VALIDATOR_DEFINITION
			);
		}

		return $allowed_values;
	}

	/**
	 * @throws Data_Validator_Exception
	 * @return callable|null
	 */
	public function getValidationCallback() {
		return $this->validation_callback;
	}

	/**
	 * @return null|string
	 */
	public function getValidationPattern() {
		return $this->validation_pattern;
	}

	/**
	 * @return array
	 */
	public static function getCommonErrorMessages() {
		return static::$common_error_messages;
	}


	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	protected function validateEmptyValue(&$value, &$error_code = null, &$error_message = null){
		if(!$this->isEmptyValue($value) || $this->getAllowEmptyValue()){
			return true;
		}

		$error_code = static::ERR_EMPTY;
		$error_message = $this->getErrorMessage($error_code);

		return false;
	}

	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	protected function validateValueType(&$value, &$error_code = null, &$error_message = null){
		if(is_scalar($value) || $value === null){
			return true;
		}

		$error_code = static::ERR_INVALID_TYPE;
		$error_message = $this->getErrorMessage($error_code, array("TYPE" => "scalar value"));

		return false;
	}

	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	protected function validatePattern(&$value, &$error_code = null, &$error_message = null){
		if($this->validation_pattern === null){
			return true;
		}

		if(preg_match("~{$this->validation_pattern}~", (string)$value)){
			return true;
		}

		$error_code = static::ERR_INVALID_FORMAT;
		$error_message = $this->getErrorMessage($error_code, array("FORMAT" => "matching '{$this->validation_pattern}' pattern"));

		return false;
	}

	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	protected function validateUsingCallback(&$value, &$error_code = null, &$error_message = null){
		if(!$this->validation_callback){
			return true;
		}

		$callback = $this->validation_callback;
		if($callback($value, $error_code, $error_message, $this) === false){
			if(!$error_code){
				$error_code = static::ERR_OTHER;
				$error_message = $this->getErrorMessage($error_code, array("REASON" => "validation using callback failed"));
			}
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
	protected function validateAllowedValues(&$value, &$error_code = null, &$error_message = null){
		if($this->allowed_values === null){
			return true;
		}

		$allowed_values = $this->getAllowedValues();
		if(isset($allowed_values[$value])){
			return true;
		}

		$error_code = static::ERR_NOT_ALLOWED_VALUE;
		$error_message = $this->getErrorMessage($error_code, array("ALLOWED_VALUES" => "'" . implode("', '", array_keys($allowed_values)) . "'"));

		return false;
	}

	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	public function validateValue($value, &$error_code = null, &$error_message = null){
		if(!$this->validateEmptyValue($value, $error_code, $error_message)){
			return false;
		}
		$is_empty = $this->isEmptyValue($value);


		if(!$this->validateValueType($value, $error_code, $error_message)){
			return false;
		}

		if($is_empty){
			return true;
		}

		return
			$this->validateAllowedValues($value, $error_code, $error_message) &&
			$this->validatePattern($value, $error_code, $error_message) &&
			$this->validateUsingCallback($value, $error_code, $error_message) &&
			$this->validateValueLength($value, $error_code, $error_message) &&
			$this->validateValueSpecific($value, $error_code, $error_message);
	}

	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	abstract protected function validateValueSpecific(&$value, &$error_code = null, &$error_message = null);


	/**
	 * @param mixed $value
	 * @return bool
	 */
	protected function isEmptyValue(&$value){
		if(is_object($value)){
			return false;
		}
		return is_array($value) ? !$value : (trim($value) === "");
	}

	/**
	 * @param boolean $allow_empty_value
	 */
	public function setAllowEmptyValue($allow_empty_value) {
		$this->allow_empty_value = (bool)$allow_empty_value;
	}

	/**
	 * @param array|callable|null $allowed_values
	 * @throws Data_Validator_Exception
	 */
	public function setAllowedValues($allowed_values) {
		if($allowed_values !== null){
			if(!is_array($allowed_values) && !is_callable($allowed_values)){
				throw new Data_Validator_Exception(
					"Allowed values must be array or callback, not " . gettype($allowed_values),
					Data_Validator_Exception::CODE_INVALID_VALIDATOR_DEFINITION
				);
			}
		}
		$this->allowed_values = $allowed_values;
	}


	/**
	 * @param callable|null $validation_callback
	 */
	public function setValidationCallback(callable $validation_callback = null) {
		$this->validation_callback = $validation_callback;
	}

	/**
	 * @param null|string $validation_pattern
	 */
	public function setValidationPattern($validation_pattern) {
		if($validation_pattern !== null){
			$validation_pattern = (string)$validation_pattern;
		}
		$this->validation_pattern = $validation_pattern;
	}

	/**
	 * @param mixed $value
	 * @param null|string $error_code [optional][reference]
	 * @param null|string $error_message [optional][reference]
	 * @return bool
	 */
	protected function validateValueLength(&$value, &$error_code = null, &$error_message = null){

		if(is_array($value) || (is_object($value) && !method_exists($value, "__toString"))){
			return true;
		}

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
	 * @return int
	 */
	public function getMaximalLength() {
		return $this->maximal_length;
	}

	/**
	 * @return int|null
	 */
	public function getMinimalLength() {
		return $this->minimal_length;
	}

	/**
	 * @param int|null $maximal_length
	 */
	public function setMaximalLength($maximal_length) {
		if($maximal_length !== null){
			$maximal_length = max(0, (int)$maximal_length);
		}
		$this->maximal_length = $maximal_length;
	}

	/**
	 * @param int|null $minimal_length
	 */
	public function setMinimalLength($minimal_length) {
		if($minimal_length !== null){
			$minimal_length = max(0, (int)$minimal_length);
		}
		$this->minimal_length = $minimal_length;
	}


	/**
	 * @param mixed $value
	 * @return mixed
	 */
	abstract function formatValue($value);

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	function formatValueToScalar($value) {
		if(!is_scalar($value) && $value !== null){
			if($value instanceof Locales_Locale){
				return (string)$value;
			}

			if($value instanceof Locales_DateTime){
				return $value->getTimestamp();
			}

			if(is_object($value) && method_exists($value, "__toString")){
				return (string)$value;
			}

			if(is_object($value)){
				return null;
			}

			return false;
		}
		return $value;
	}
}