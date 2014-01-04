<?php
namespace Et;
abstract class Entity_Definition_Abstract extends System_Components_Component implements \Iterator,\ArrayAccess,\Countable {

	/**
	 * @var string|\Et\Entity_Abstract
	 */
	protected $entity_class_name;

	/**
	 * @var string
	 */
	protected $entity_name;

	/**
	 * @var Entity_Property_Abstract[]
	 */
	protected $properties;

	/**
	 * @var bool
	 */
	protected $has_parts = false;

	/**
	 * @var bool
	 */
	protected $has_relations = false;


	/**
	 * @param string $entity_class_name
	 * @param array $properties
	 */
	function __construct($entity_class_name, array $properties){
		$entity_class_name = Entity::resolveEntityClassName($entity_class_name);
		$this->entity_class_name = $entity_class_name;
		$this->entity_name = $entity_class_name::getEntityName();
		$this->assert()->isArrayOfInstances($properties, "Et\\Entity_Property_Abstract");
		$this->properties = $properties;

		parent::__construct(
			$entity_class_name::getClassID(),
			$entity_class_name::getEntityTitle(),
			$entity_class_name::getEntityDescription()
		);
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
	 * @return \Et\Entity_Property_Abstract[]
	 */
	public function getProperties() {
		return $this->properties;
	}

	/**
	 * @return array
	 */
	public function getPropertiesNames(){
		return array_keys($this->properties);
	}

	/**
	 * @param string $property_name
	 * @return Entity_Property_Abstract
	 * @throws Entity_Exception
	 */
	public function getProperty($property_name){

		if(!isset($this->properties[$property_name])){
			throw new Entity_Exception(
				"Entity {$this->getEntityClassName()}::\${$property_name} does not exist",
				Entity_Exception::CODE_INVALID_PROPERTY
			);
		}

		return $this->properties[$property_name];
	}

	/**
	 * @param string $property_name
	 * @return bool
	 */
	public function hasProperty($property_name){
		return isset($this->properties[$property_name]);
	}


	function getDBTableDefinition(){

	}

	/**
	 * @return \Et\Entity_Property_Abstract|bool
	 */
	public function current() {
		return current($this->properties);
	}

	public function next() {
		next($this->properties);
	}

	/**
	 * @return string|null
	 */
	public function key() {
		return key($this->properties);
	}


	/**
	 * @return bool
	 */
	public function valid() {
		return key($this->properties) !== null;
	}


	public function rewind() {
		reset($this->properties);
	}

	/**
	 * @param string $property_name
	 * @return bool
	 */
	public function offsetExists($property_name) {
		return $this->hasProperty($property_name);
	}

	/**
	 * @param string $property_name
	 * @return Entity_Property_Abstract|mixed
	 */
	public function offsetGet($property_name) {
		return $this->getProperty($property_name);
	}


	public function offsetSet($offset, $value) {
	}

	public function offsetUnset($offset) {
	}

	/**
	 * @return int
	 */
	public function count() {
		return count($this->properties);
	}

	/**
	 * @return string
	 */
	function __toString(){
		return $this->getEntityName();
	}
}