<?php
namespace Et;
/**
 * Base object
 */
abstract class Object {

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
	 * Avoid setting undefined properties (probably error in code .. )
	 *
	 * @param string $property
	 * @param mixed $value
	 *
	 * @throws Object_Exception
	 */
	public function __set($property, $value){
		if(!property_exists($this, $property)){
			throw new Object_Exception(
				"Property {$this->className()}->{$property} does not exist",
				Object_Exception::CODE_UNKNOWN_PROPERTY_ACCESS
			);
		}

		throw new Object_Exception(
			"Cannot set {$this->className()}->{$property} property value - permission denied",
			Object_Exception::CODE_PROTECTED_PROPERTY_ACCESS
		);
	}

	/**
	 * Avoid getting undefined properties (probably error in code .. )
	 *
	 * @param string $property
	 *
	 * @throws Object_Exception
	 */
	public function __get($property){
		if(!property_exists($this, $property)){
			throw new Object_Exception(
				"Property {$this->className()}->{$property} does not exist",
				Object_Exception::CODE_UNKNOWN_PROPERTY_ACCESS
			);
		}

		throw new Object_Exception(
			"Cannot get {$this->className()}->{$property} property value - permission denied",
			Object_Exception::CODE_PROTECTED_PROPERTY_ACCESS
		);
	}

	/**
	 * Avoid removing undefined properties (probably error in code .. )
	 *
	 * @param string $property
	 *
	 * @throws Object_Exception
	 */
	public function __unset($property){
		if(!property_exists($this, $property)){
			throw new Object_Exception(
				"Property {$this->className()}->{$property} does not exist",
				Object_Exception::CODE_UNKNOWN_PROPERTY_ACCESS
			);
		}

		throw new Object_Exception(
			"Cannot remove {$this->className()}->{$property} property value - permission denied",
			Object_Exception::CODE_PROTECTED_PROPERTY_ACCESS
		);
	}

	/**
	 * Returns object variables names which do not begin with '_'
	 *
	 * @return array
	 */
	protected function getVisibleObjectPropertiesNames(){
		$props = array_keys(get_object_vars($this));
		$output = array();
		foreach($props as $k){
			if($k[0] == "_"){
				continue;
			}
			$output[] = $k;
		}
		return $output;
	}

	/**
	 * Returns object variables values which names do not begin with '_'
	 *
	 * @return array
	 */
	protected function getVisibleObjectPropertiesValues(){
		$props = $this->getVisibleObjectPropertiesNames();
		$output = array();
		foreach($props as $prop){
			$output[$prop] = $this->{$prop};
		}
		return $output;
	}

	/**
	 * Returns class variables names which do not begin with '_'
	 *
	 * @return array
	 */
	protected static function getVisibleClassPropertiesNames(){
		return array_keys(static::getVisibleClassPropertiesValues());
	}

	/**
	 * Returns class variables values which names do not begin with '_'
	 *
	 * @return array
	 */
	protected static function getVisibleClassPropertiesValues(){
		$properties = get_class_vars(static::class);
		$props = array_keys($properties);
		foreach($props as $k){
			if($k[0] == "_"){
				unset($properties[$k]);
			}
		}
		return $properties;
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
	 * @param string $property
	 *
	 * @return bool
	 */
	protected static function hasVisibleClassProperty($property){
		return property_exists(static::class, $property) && $property[0] != "_";
	}

	/**
	 * @param string $property
	 *
	 * @return bool
	 */
	protected function hasVisibleObjectProperty($property){
		return property_exists($this, $property) && $property[0] != "_";
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