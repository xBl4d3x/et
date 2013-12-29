<?php
namespace Et;
class Entity_Key_String extends Entity_Key_Abstract {

	/**
	 * @var string
	 */
	protected $property_name;

	/**
	 * @var string
	 */
	protected $value;

	/**
	 * @param Entity_Abstract|string $entity_class
	 * @param string $property_name
	 * @param null|string $value
	 */
	function __construct($entity_class, $property_name, $value = null){
		parent::__construct($entity_class);
		$this->property_name = $property_name;
		if($value !== null){
			$this->setValue($value);
		}
	}

	/**
	 * @return string
	 */
	function toString() {
		return (string)$this->getValue();
	}

	/**
	 * @param mixed $value
	 * @return \Et\Entity_Key_String|static
	 */
	function setFromString($value) {
		$this->setValue($value);
		return $this;
	}

	/**
	 * @return bool
	 */
	function hasValue() {
		return $this->value !== null;
	}

	/**
	 * @return string|null
	 */
	function getValue() {
		return $this->value;
	}

	/**
	 * @param string $value
	 * @return \Et\Entity_Key_Numeric|static
	 */
	function setValue($value) {
		$this->value = (string)$value;
		return $this;
	}
}