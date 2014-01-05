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

	/**
	 * @var bool
	 */
	protected static $initialized = false;

	public static function initialize(){
		if(static::$initialized){
			return;
		}
		static::$initialized = true;

		et_require("System");
		$class_override_map = System::getConfig()->getSectionData(static::ENVIRONMENT_CONFIG_SECTION);
		static::setClassOverrideMap($class_override_map, false);
	}

	/**
	 * @param array $class_override_map
	 * @param bool $merge [optional]
	 */
	public static function setClassOverrideMap(array $class_override_map, $merge = true){
		if(!static::$initialized){
			static::initialize();
		}

		if(!$merge){
			static::$class_override_map = array();
		}

		foreach($class_override_map as $k => $v){
			static::$class_override_map[$k] = (string)$v;
		}

		static::$class_override_check_cache = array();
	}

	/**
	 * @return array
	 */
	public static function getClassOverrideMap(){
		if(!static::$initialized){
			static::initialize();
		}

		return static::$class_override_map;
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

		if(!static::$initialized){
			static::initialize();
		}

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
		$reflection = new \ReflectionClass($class_name);
		return $reflection->newInstanceWithoutConstructor();
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
		if(!$constructor_arguments){
			return new $class_name();
		}

		$keys = array_keys($constructor_arguments);
		$args_count = count($keys);

		switch($args_count){
			case 1:
				return new $class_name(
					$constructor_arguments[$keys[0]]
				);

			case 2:
				return new $class_name(
					$constructor_arguments[$keys[0]],
					$constructor_arguments[$keys[1]]
				);

			case 3:
				return new $class_name(
					$constructor_arguments[$keys[0]],
					$constructor_arguments[$keys[1]],
					$constructor_arguments[$keys[2]]
				);

			case 4:
				return new $class_name(
					$constructor_arguments[$keys[0]],
					$constructor_arguments[$keys[1]],
					$constructor_arguments[$keys[2]],
					$constructor_arguments[$keys[3]]
				);

			case 5:
				return new $class_name(
					$constructor_arguments[$keys[0]],
					$constructor_arguments[$keys[1]],
					$constructor_arguments[$keys[2]],
					$constructor_arguments[$keys[3]],
					$constructor_arguments[$keys[4]]
				);

			default:
				$reflection = new \ReflectionClass($class_name);
				return $reflection->newInstanceArgs($constructor_arguments);
		}

	}

}