<?php
namespace Et;

require_once (__DIR__ . "/Object/MagicGetTrait.php");
require_once (__DIR__ . "/Object/MagicSetTrait.php");
require_once (__DIR__ . "/Object/MagicUnsetTrait.php");
require_once (__DIR__ . "/Object/MagicSleepTrait.php");
require_once (__DIR__ . "/Object/VisiblePropertiesTrait.php");

/**
 * Base object
 */
abstract class Object {

	use Object_MagicGetTrait;
	use Object_MagicSetTrait;
	use Object_MagicUnsetTrait;
	use Object_MagicSleepTrait;
	use Object_VisiblePropertiesTrait;

	/**
	 * @return string
	 */
	public static function className(){
		return static::class;
	}

	/**
	 * @return string
	 */
	public static function getClassID(){
		return strtolower(str_replace("\\", "_", static::class));
	}

	/**
	 * @return string
	 */
	public static function getClassNameWithoutNamespace(){
		$parts = explode("\\", static::class);
		return array_pop($parts);
	}

	/**
	 * Create instance of object without calling constructor
	 *
	 * @return Object|static
	 */
	public static function getInstanceWithoutConstructor(){
		$reflection = new \ReflectionClass(static::class);
		return $reflection->newInstanceWithoutConstructor();
	}

	/**
	 * @param string $property_name
	 * @return string
	 */
	function getSetterMethodName($property_name){
		$method = "set" . str_replace("_", "", $property_name);
		if(!method_exists($this, $method)){
			return false;
		}
		return $method;
	}


	/**
	 * @param bool $deep_object_clone [optional]
	 * @return static
	 */
	public function cloneInstance($deep_object_clone = true){
		if(!$deep_object_clone){
			return clone($this);
		}
		$serialized = serialize($this);
		return unserialize($serialized);
	}
}