<?php
namespace Et;
abstract class Entity_Key_Abstract extends Object {

	/**
	 * @var string|Entity_Abstract
	 */
	protected $entity_class;

	/**
	 * @var string
	 */
	protected $entity_name;

	/**
	 * @param string|\Et\Entity_Abstract $entity_class
	 * @throws \Et\Entity_Exception
	 */
	function __construct($entity_class){

		$entity_class = Entity::resolveEntityClassName($entity_class);

		$this->entity_class = $entity_class;
		$this->entity_name = $entity_class::getEntityName();
	}

	/**
	 * @return \Et\Entity_Abstract|string
	 */
	public function getEntityClass() {
		return $this->entity_class;
	}

	/**
	 * @return string
	 */
	public function getEntityName() {
		return $this->entity_name;
	}



	/**
	 * @return string
	 */
	abstract function toString();

	function __toString(){
		return $this->toString();
	}

	/**
	 * @param mixed $value
	 * @return static|\Et\Entity_Key_Abstract
	 */
	abstract function setFromString($value);

	/**
	 * @return bool
	 */
	abstract function hasValue();

	/**
	 * @return int|string|array
	 */
	abstract function getValue();

	/**
	 * @param mixed $value
	 * @return static|Entity_Key_Abstract
	 */
	abstract function setValue($value);

	/**
	 * Generate unique key value if possible
	 * @return static|\Et\Entity_Key_Abstract
	 */
	abstract function generateValue();
}