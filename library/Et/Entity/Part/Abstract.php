<?php
namespace Et;
abstract class Entity_Part_Abstract extends Entity_Abstract {

	/**
	 * @var array
	 */
	protected static $__parts_main_entities_cache = array();

	/**
	 * @var string|\Et\Entity_Main|\Et\Entity_Part_Abstract
	 */
	protected static $_parent_entity_class;

	/**
	 * @var string
	 */
	protected static $_parent_entity_ID_property_name;

	/**
	 * @var string
	 */
	protected static $_main_entity_ID_property_name;

	/**
	 * @var \Et\Entity_Main|\Et\Entity_Part_Single|\Et\Entity_Part_Multiple
	 */
	protected $__parent_entity;

	/**
	 * @var \Et\Entity_Main
	 */
	protected $__main_entity;

	/**
	 * @return bool
	 */
	public static function isPartOfMainEntity(){
		static::checkEntity();
		return !static::$_main_entity_ID_property_name;
	}

	/**
	 * @throws Entity_Exception
	 */
	protected static function checkEntity(){

		if(isset(static::$__checked_entities[static::class])){
			return;
		}

		$class = static::class;

		if(!static::$_parent_entity_class){
			throw new Entity_Exception(
				"Missing definition of parent entity class for {$class} entity - check property {$class}::\$_parent_entity_class",
				Entity_Exception::CODE_INVALID_DEFINITION
			);
		}

		if(!is_subclass_of(static::$_parent_entity_class, "Et\\Entity_Abstract", true)){
			throw new Entity_Exception(
				"Parent entity class for {$class} entity does not exist or is not subclass of Et\\Entity_Abstract - check property {$class}::\$_parent_entity_class",
				Entity_Exception::CODE_INVALID_DEFINITION
			);
		}

		if(!static::$_parent_entity_ID_property_name){
			throw new Entity_Exception(
				"Missing definition of parent entity ID property name for {$class} entity - check property {$class}::\$_parent_entity_ID_property_name",
				Entity_Exception::CODE_INVALID_DEFINITION
			);
		}

		if(!property_exists(static::class, static::$_parent_entity_ID_property_name)){
			throw new Entity_Exception(
				"Parent entity ID property {$class}::\$" . static::$_parent_entity_ID_property_name . " not exists - check property {$class}::\$_parent_entity_ID_property_name",
				Entity_Exception::CODE_INVALID_DEFINITION
			);
		}

		if(!static::$_main_entity_ID_property_name){

			if(!is_subclass_of(static::$_parent_entity_class, "Et\\Entity_Main", true)){
				throw new Entity_Exception(
					"Parent entity class for {$class} entity must be subclass of Et\\Entity_Main if {$class}::\$_main_entity_ID_property_name is not defined - check property {$class}::\$_parent_entity_class",
					Entity_Exception::CODE_INVALID_DEFINITION
				);
			}

		} elseif(!property_exists(static::class, static::$_main_entity_ID_property_name)){

			throw new Entity_Exception(
				"Main entity ID property {$class}::\$" . static::$_main_entity_ID_property_name . " not exists - check property {$class}::\$_main_entity_ID_property_name",
				Entity_Exception::CODE_INVALID_DEFINITION
			);

		} else {

			if(is_subclass_of(static::$_parent_entity_class, "Et\\Entity_Main", true)){
				throw new Entity_Exception(
					"Parent entity class (".(string)static::$_parent_entity_class.") for {$class} may not be subclass of Et\\Entity_Main if {$class}::\$_main_entity_ID_property_name is defined - check property {$class}::\$_parent_entity_class",
					Entity_Exception::CODE_INVALID_DEFINITION
				);
			}
		}

		parent::checkEntity();
	}


	/**
	 * @return \Et\Entity_Main|string
	 */
	public static function getMainEntityClass() {
		if(isset(static::$__parts_main_entities_cache[static::class])){
			return static::$__parts_main_entities_cache[static::class];
		}

		$parent_class = static::getParentEntityClass();
		if($parent_class::isMainEntity()){
			static::$__parts_main_entities_cache[static::class] = $parent_class;
			return $parent_class;
		}

		return $parent_class::getMainEntityClass();
	}

	/**
	 * @throws Entity_Exception
	 * @return string|\Et\Entity_Main|\Et\Entity_Part_Abstract
	 */
	public static function getParentEntityClass() {
		static::checkEntity();
		return static::$_parent_entity_class;
	}

	/**
	 * @return string
	 */
	public static function getParentEntityName(){
		$parent_class = static::getParentEntityClass();
		return $parent_class::getEntityName();
	}

	/**
	 * @return string
	 */
	public static function getMainEntityName(){
		$main_class = static::getMainEntityClass();
		return $main_class::getEntityName();
	}

	/**
	 * @return Entity_Definition_Part
	 */
	public static function getEntityDefinition(){
		return parent::getEntityDefinition();
	}

	/**
	 * @throws Entity_Exception
	 * @return string
	 */
	public static function getMainEntityIDPropertyName() {
		if(static::isPartOfMainEntity()){
			return static::getParentEntityIDPropertyName();
		}
		return static::$_main_entity_ID_property_name;
	}

	/**
	 * @throws Entity_Exception
	 * @return string
	 */
	public static function getParentEntityIDPropertyName() {
		static::checkEntity();
		return static::$_parent_entity_ID_property_name;
	}


	/**
	 * @param Entity_Abstract $parent
	 * @param Entity_Main $main [optional]
	 * @param null|string $path_to_part [optional]
	 * @return bool
	 * @throws Entity_Exception
	 */
	public function save(Entity_Abstract $parent, Entity_Main $main = null, $path_to_part = null){
		$this->checkEntity();

		if(!$this->validate(true)){
			return false;
		}

		$class = static::class;
		$parent_class = $this->getParentEntityClass();
		if(!$parent instanceof $parent_class){
			throw new Entity_Exception(
				"Entity part {$class} requires instance of {$parent_class} as a parent to save",
				Entity_Exception::CODE_INVALID_ENTITY
			);
		}

		if(!$this->isPartOfMainEntity()){
			$main_class = $this->getMainEntityClass();
			if(!$main instanceof $main_class){
				throw new Entity_Exception(
					"Entity part {$class} requires instance of {$main_class} as a main class to save",
					Entity_Exception::CODE_INVALID_ENTITY
				);
			}
		}

		return $this->_save();
	}



}