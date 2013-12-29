<?php
namespace Et;

class Entity_Key extends Object {

	/**
	 * @var Entity_Key_Generator_Abstract
	 */
	protected static $generator;

	/**
	 * @param \Et\Entity_Key_Generator_Abstract $generator
	 */
	public static function setGenerator(Entity_Key_Generator_Abstract $generator) {
		static::$generator = $generator;
	}

	/**
	 * @return \Et\Entity_Key_Generator_Abstract
	 */
	public static function getGenerator() {
		if(!static::$generator){
			static::$generator = new Entity_Key_Generator_Default();
		}
		return static::$generator;
	}

	/**
	 * Generate new numeric ID for entity
	 *
	 * @param string|Entity_Abstract $entity_class
	 * @param callable $check_if_exists_callback
	 * @param array $additional_check_arguments
	 * @return int
	 */
	public static function generateNumericID($entity_class, callable $check_if_exists_callback = null, array $additional_check_arguments = array()){
		return static::getGenerator()->generateNumericID($entity_class, $check_if_exists_callback, $additional_check_arguments);
	}

	/**
	 * Generate new string ID for entity
	 *
	 * @param string|Entity_Abstract $entity_class
	 * @param bool $short [optional] Generate 32 characters ID instead of 40?
	 * @param callable $check_if_exists_callback
	 * @param array $additional_check_arguments
	 * @return string
	 */
	public static function generateTextID($entity_class, $short = false, callable $check_if_exists_callback = null, array $additional_check_arguments = array()){
		return static::getGenerator()->generateTextID($entity_class, $short, $check_if_exists_callback, $additional_check_arguments);
	}

}