<?php
namespace Et;
abstract class Entity_Property_Abstract extends Object {

	/**
	 * @var string|\Et\Entity_Abstract
	 */
	protected $entity_class_name;

	/**
	 * @var string
	 */
	protected $entity_name;

	/**
	 * @var string
	 */
	protected $property_name;

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
	 * @var string|bool
	 */
	protected $validator_type = false;

	/**
	 * @var string|bool
	 */
	protected $form_field_type = false;

	/**
	 * @var int|null
	 */
	protected $minimal_value;

	/**
	 * @var int|null
	 */
	protected $maximal_value;

	/**
	 * @var int|null
	 */
	protected $minimal_length;

	/**
	 * @var int|null
	 */
	protected $maximal_length;

	/**
	 * @var string|callable|null
	 */
	protected $format;

	/**
	 * @var array
	 */
	protected $error_messages = array(
		Entity_Abstract::ERR_REQUIRED => "Value may not be empty",
		Entity_Abstract::ERR_NOT_FOUND => "Value not found",
		Entity_Abstract::ERR_ALREADY_EXISTS => "Value already exists",
		Entity_Abstract::ERR_INVALID_FORMAT => "Value has invalid format",
		Entity_Abstract::ERR_INVALID_TYPE => "Invalid value type",
		Entity_Abstract::ERR_INVALID_VALUE => "This value is not allowed",
		Entity_Abstract::ERR_TOO_SHORT => "Value is too short (less than {MINIMAL_LENGTH} characters)",
		Entity_Abstract::ERR_TOO_LONG => "Value is too long (more than {MAXIMAL_LENGTH} characters)",
		Entity_Abstract::ERR_TOO_HIGH => "Value is too high (higher than {MAXIMAL_VALUE})",
		Entity_Abstract::ERR_TOO_LOW => "Value is too low (lower than {MINIMAL_VALUE})",
		Entity_Abstract::ERR_OTHER => "Value is not valid - {REASON}",
	);


	/**
	 * @var null|array|callable
	 */
	protected $allowed_values = null;

	/**
	 * @param string|\Et\Entity_Abstract $entity_class_name
	 * @param string $property_name
	 * @param array $parameters [optional]
	 */
	function __construct($entity_class_name, $property_name, array $parameters = array()){
		$entity_class_name = Entity::resolveEntityClassName($entity_class_name);
		$this->entity_class_name = $entity_class_name;
		$this->entity_name = $entity_class_name::getEntityName();
		$this->property_name = $property_name;
		$this->setParameters($parameters);
	}

	function setParameters(array $parameters){

	}

	function validateValue($value){

	}

	/**
	 * @return array|callable|null
	 */
	public function getAllowedValues() {
		return $this->allowed_values;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return \Et\Entity_Abstract|string
	 */
	public function getEntityClassName() {
		return $this->entity_class_name;
	}

	/**
	 * @return string
	 */
	public function getEntityName() {
		return $this->entity_name;
	}

	/**
	 * @return array
	 */
	public function getErrorMessages() {
		return $this->error_messages;
	}

	/**
	 * @return bool|string
	 */
	public function getFormFieldType() {
		return $this->form_field_type;
	}

	/**
	 * @return callable|null|string
	 */
	public function getFormat() {
		return $this->format;
	}

	/**
	 * @return int|null
	 */
	public function getMaximalLength() {
		return $this->maximal_length;
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
	public function getMinimalLength() {
		return $this->minimal_length;
	}

	/**
	 * @return int|null
	 */
	public function getMinimalValue() {
		return $this->minimal_value;
	}

	/**
	 * @return string
	 */
	public function getPropertyName() {
		return $this->property_name;
	}

	/**
	 * @return string
	 */
	public function getPropertyFullName(){
		return "{$this->entity_class_name}::\${$this->property_name}";
	}

	/**
	 * @return string
	 */
	public function getTableColumnName(){
		return "{$this->entity_name}.{$this->property_name}";
	}

	/**
	 * @return boolean
	 */
	public function isRequired() {
		return $this->required;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return bool|string
	 */
	public function getValidatorType() {
		return $this->validator_type;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	abstract function formatValue($value);

	/**
	 * @return string
	 */
	function __toString(){
		return $this->getTableColumnName();
	}


}