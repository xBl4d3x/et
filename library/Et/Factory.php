<?php
namespace Et;
class Factory {

	const ENVIRONMENT_CONFIG_SECTION = "overloaded_classes";

	/**
	 * array(
	 *  'Original\Class_Name' => 'Real\Class_Name') ...
	 * )
	 * @var array[]
	 */
	protected static $class_override_map = array();

	/**
	 * @var array
	 */
	protected static $class_override_check_cache = array();

	public static function initialize(){
		et_require("Application");
		$class_override_map = Application::getEnvironment()->getSectionData(static::ENVIRONMENT_CONFIG_SECTION);
		foreach($class_override_map as $k => $v){
			if(!isset(static::$class_override_map[$k])){
				static::$class_override_map[$k] = (string)$v;
			}
		}
	}

	/**
	 * @param array $class_override_map
	 * @param bool $merge [optional]
	 */
	public static function setClassOverrideMap(array $class_override_map, $merge = true){
		if(!$merge || !static::$class_override_map){
			static::$class_override_map = $class_override_map;
		} else {
			static::$class_override_map = array_merge(static::$class_override_map, $class_override_map);
		}
		static::clearClassOverrideCheckCache();
	}

	/**
	 * @return array
	 */
	public static function getClassOverrideMap(){
		return static::$class_override_map;
	}

	/**
	 * @param string $original_class_name
	 *
	 * @return bool
	 */
	public static function removeClassNameOverride($original_class_name){
		if(!isset(static::$class_override_map[$original_class_name])){
			return false;
		}
		unset(static::$class_override_map[$original_class_name]);
		static::clearClassOverrideCheckCache();
		return true;
	}

	/**
	 * @param string $original_class_name
	 * @param string $override_by_class_name
	 */
	public static function setClassNameOverride($original_class_name, $override_by_class_name){
		static::$class_override_map[$original_class_name] = $override_by_class_name;
		static::clearClassOverrideCheckCache();
	}


	public static function clearClassOverrideCheckCache(){
		static::$class_override_check_cache = array();
	}


	/**
	 * @param string $original_class_name
	 * @param null|string $required_parent_class_name [optional]
	 * @param bool $check_if_exists [optional]
	 *
	 * @throws Factory_Exception
	 * @return string
	 */
	public static function getClassName($original_class_name, $required_parent_class_name = null, $check_if_exists = true){

		$real_class_name = isset(static::$class_override_map[$original_class_name])
			? static::$class_override_map[$original_class_name]
			: $original_class_name;


		if($check_if_exists && !class_exists($real_class_name)){
			et_require('Factory_Exception');
			throw new Factory_Exception(
				"Class '{$real_class_name}' (original class '{$original_class_name}') not exists",
				Factory_Exception::CODE_CLASS_NOT_EXISTS
			);
		}

		if($required_parent_class_name){
			if(!is_array($required_parent_class_name)){
				$required_parent_class_name = array($required_parent_class_name);
			}

			foreach($required_parent_class_name as $required_class){
				if(is_a($real_class_name, $required_class, true)){
					continue;
				}

				et_require('Factory_Exception');
				throw new Factory_Exception(
					"Class '{$real_class_name}' (original class '{$original_class_name}') is not '{$required_class}' neither its subclass",
					Factory_Exception::CODE_WRONG_CLASS_PARENT
				);
			}
		}

		return $original_class_name;

	}

	/**
	 * @param string $original_class_name
	 * @param null|string $required_parent_class_name [optional]
	 * @param bool $check_if_exists [optional]
	 * @return object
	 */
	public static function getClassInstanceWithoutConstructor($original_class_name, $required_parent_class_name = null, $check_if_exists = true){
		$class_name = static::getClassName($original_class_name,$required_parent_class_name, $check_if_exists);
		return Debug::createObjectInstanceWithoutConstructor($class_name);
	}

	/**
	 * @param string $original_class_name
	 * @param null|string $required_parent_class_name [optional]
	 * @param bool $check_if_exists [optional]
	 * @param array $constructor_arguments [optional]
	 * @return object
	 */
	public static function getClassInstance($original_class_name, array $constructor_arguments = array(), $required_parent_class_name = null, $check_if_exists = true){
		$class_name = static::getClassName($original_class_name,$required_parent_class_name, $check_if_exists);
		return Debug::createObjectInstance($class_name, $constructor_arguments);
	}

}