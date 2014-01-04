<?php
namespace Et;

class Entity extends Object {

	/**
	 * @var Entity_ID_Generator_Abstract
	 */
	protected static $ID_generator;

	/**
	 * @var array
	 */
	protected static $__entity_class_check_cache = array();


	/**
	 * @param string $entity
	 * @return string|Entity_Abstract (for type-hint)
	 * @throws Entity_Exception
	 */
	public static function resolveEntityClassName($entity){

		if(is_object($entity)){

			if(!$entity instanceof Entity_Abstract){
				throw new Entity_Exception(
					"Entity class must be a string or instance of Et\\Entity_Abstract",
					Entity_Exception::CODE_INVALID_ENTITY
				);
			}

			$class = get_class($entity);
			static::$__entity_class_check_cache[$class] = true;

			return $class;
		}

		$entity = (string)$entity;

		if(!isset(static::$__entity_class_check_cache[$entity])){
			static::$__entity_class_check_cache[$entity] = is_subclass_of($entity, "Et\\Entity_Abstract", true);
		}



		if(!static::$__entity_class_check_cache[$entity]){

			throw new Entity_Exception(
				"Entity class must be a string or instance of Et\\Entity_Abstract, '{$entity}' is not",
				Entity_Exception::CODE_INVALID_ENTITY
			);

		}

		return $entity;
	}

	/**
	 * @param \Et\Entity_ID_Generator_Abstract $generator
	 */
	public static function setIDGenerator(Entity_ID_Generator_Abstract $generator) {
		static::$ID_generator = $generator;
	}

	/**
	 * @return \Et\Entity_ID_Generator_Abstract
	 */
	public static function getIDGenerator() {
		if(!static::$ID_generator){
			static::$ID_generator = new Entity_ID_Generator_Default();
		}
		return static::$ID_generator;
	}

	/**
	 * Generate new numeric ID for entity
	 *
	 * @param string|Entity_Abstract $entity_class
	 * @return int
	 */
	public static function generateNumericID($entity_class){
		return static::getIDGenerator()->generateNumericID($entity_class);
	}

	/**
	 * Generate new generic string ID for entity
	 *
	 * @param string|Entity_Abstract $entity_class
	 * @return string
	 */
	public static function generateTextID($entity_class){
		return static::getIDGenerator()->generateTextID($entity_class);
	}

	/**
	 * @param string|Entity_Abstract $entity_class
	 * @param string $input_text
	 * @return string
	 * @throws Entity_ID_Generator_Exception
	 */
	public static function generateIDFromString($entity_class, $input_text){
		return static::getIDGenerator()->generateIDFromString($entity_class, $input_text);
	}

}