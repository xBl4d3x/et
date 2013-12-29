<?php
namespace Et;

class Entity extends Object {

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

}