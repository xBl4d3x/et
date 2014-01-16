<?php
namespace Et;
trait Object_Property_Trait {

	/**
	 * @var string
	 */
	protected $property_name;

	/**
	 * @var string|\Et\Object_Definable
	 */
	protected $class_name;

	/**
	 * @var string
	 */
	protected $title = "";

	/**
	 * @var string
	 */
	protected $description = "";

	/**
	 * @var bool
	 */
	protected $required = false;

	/**
	 * @var null|array|callable
	 */
	protected $allowed_values;

	/**
	 * @var null|string|callable
	 */
	protected $format;

	/**
	 * @var array
	 */
	protected $error_messages = array(
		Object_Definable::ERR_REQUIRED => "Value may not be empty",
		Object_Definable::ERR_INVALID_FORMAT => "Value has invalid format",
		Object_Definable::ERR_INVALID_TYPE => "Invalid value type",
		Object_Definable::ERR_NOT_ALLOWED_VALUE => "This value is not allowed",
		Object_Definable::ERR_TOO_SHORT => "Value is too short (less than {MINIMAL_LENGTH} characters)",
		Object_Definable::ERR_TOO_LONG => "Value is too long (more than {MAXIMAL_LENGTH} characters)",
		Object_Definable::ERR_TOO_HIGH => "Value is too high (higher than {MAXIMAL_VALUE})",
		Object_Definable::ERR_TOO_LOW => "Value is too low (lower than {MINIMAL_VALUE})",
		Object_Definable::ERR_OTHER => "Value is not valid - {REASON}",
	);

	function __construct($class_name, $property_name, array $definition){

	}

	/**
	 * @param array|callable|null $allowed_values
	 * @throws Object_Definable_Exception
	 */
	protected function setAllowedValues($allowed_values) {
		if(!is_array($allowed_values) && $allowed_values !== null && !is_callable($allowed_values)){
			throw new Object_Definable_Exception(
				"Allowed values for property {$this->getPropertyName(true)} must be array or callback returning array",
				Object_Definable_Exception::CODE_INVALID_DEFINITION
			);
		}
		$this->allowed_values = $allowed_values;
	}

	/**
	 * @throws Object_Definable_Exception
	 * @return array|callable|null
	 */
	public function getAllowedValues() {
		if($this->allowed_values === null){
			return null;
		}

		$allowed_values = $this->allowed_values;
		if(is_callable($allowed_values)){
			$allowed_values = $allowed_values($this);
		}

		if(!is_array($allowed_values)){
			throw new Object_Definable_Exception(
				"Allowed values for property {$this->getPropertyName(true)} must be array or callback returning array",
				Object_Definable_Exception::CODE_INVALID_DEFINITION,
				array(
					"allowed values" => $allowed_values
				)
			);
		}

		return $this->allowed_values;
	}

	/**
	 * @return \Et\Object_Definable|string
	 */
	public function getClassName() {
		return $this->class_name;
	}

	/**
	 * @param string $description
	 */
	protected function setDescription($description) {
		$this->description = (string)$description;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param array $error_messages
	 */
	protected function setErrorMessages(array $error_messages) {
		foreach($error_messages as $code => $message){
			$this->error_messages[$code] = (string)$message;
		}
		$this->error_messages = $error_messages;
	}

	/**
	 * @return array
	 */
	public function getErrorMessages() {
		return $this->error_messages;
	}

	/**
	 * @param callable|null|string $format
	 */
	protected function setFormat($format) {
		$this->format = $format;
	}

	/**
	 * @return callable|null|string
	 */
	public function getFormat() {
		return $this->format;
	}

	/**
	 * @param bool $full_name [optional]
	 * @return string
	 */
	public function getPropertyName($full_name = false) {
		if($full_name){
			return "{$this->class_name}::\${$this->property_name}";
		}
		return $this->property_name;
	}

	/**
	 * @param boolean $required
	 */
	protected function setRequired($required) {
		$this->required = (bool)$required;
	}

	/**
	 * @return boolean
	 */
	public function getRequired() {
		return $this->required;
	}

	/**
	 * @param string $title
	 */
	protected function setTitle($title) {
		$this->title = (string)$title;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
}