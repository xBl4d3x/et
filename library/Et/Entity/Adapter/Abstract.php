<?php
namespace Et;
abstract class Entity_Backend_Abstract extends Object {


	/**
	 * @param string $entity_class
	 *
	 * @param bool $refresh [optional]
	 *
	 * @return bool
	 */
	abstract function getEntityIsInstalled($entity_class, $refresh = false);


	abstract function fetchProperties(DB_Query)


	function installEntity(Entity_Definition_Abstract $entity_definition){

	}

}