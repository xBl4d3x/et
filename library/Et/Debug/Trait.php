<?php
namespace Et;
trait Debug_Trait {

	/**
	 * @return \ReflectionClass
	 */
	public function ___debug___getObjectReflection(){
		return new \ReflectionClass($this);
	}

	/**
	 * @return \ReflectionClass
	 */
	public static function ___debug___getClassReflection(){
		return new \ReflectionClass(get_called_class());
	}

	/**
	 * @param string $message
	 * @param array $message_data [optional]
	 * @return string
	 */
	protected static function ___debug___formatMessage($message, array $message_data = array()){
		foreach($message_data as $k => $v){
			$v = (string)$v;
			$message = str_replace("{{$k}}", $v, $message);
		}
		return $message;
	}

	/**
	 * @param string $property_name
	 * @return \ReflectionProperty
	 * @throws Debug_Exception
	 */
	public function ___debug___getObjectPropertyReflection($property_name){
		try {
			$reflection = new \ReflectionProperty($this, $property_name);
			$reflection->setAccessible(true);
			return $reflection;
		} catch(\ReflectionException $e){
			et_require('Debug_Exception');
			throw new Debug_Exception(
				self::___debug___formatMessage(
					"Failed to get '{CLASS}->{PROPERTY}' reflection - {ERROR}",
					array(
						"CLASS" => get_class($this),
						"PROPERTY" => $property_name,
						"ERROR" => $e->getMessage()
					)
				),
				Debug_Exception::CODE_INVALID_PROPERTY,
				null,
				$e
			);	
		}
	}

	/**
	 * @param string $property_name
	 * @return \ReflectionProperty
	 * @throws Debug_Exception
	 */
	public static function ___debug___getClassPropertyReflection($property_name){
		try {

			$reflection = new \ReflectionProperty(get_called_class(), $property_name);
			$reflection->setAccessible(true);
			return $reflection;

		} catch(\ReflectionException $e){

			et_require('Debug_Exception');
			throw new Debug_Exception(
				self::___debug___formatMessage(
					"Failed to get '{CLASS}::{PROPERTY}' reflection - {ERROR}",
					array(
						"CLASS" => get_called_class(),
						"PROPERTY" => $property_name,
						"ERROR" => $e->getMessage()
					)
				),
				Debug_Exception::CODE_INVALID_PROPERTY,
				null,
				$e
			);

		}
	}

	/**
	 * @param array|null $which_properties [optional]
	 * @return \ReflectionProperty[]
	 */
	public function ___debug___getObjectPropertiesReflections(array $which_properties = null){
		$output = array();

		if($which_properties !== null){
			foreach($which_properties as $property_name){
				$output[$property_name] = $this->___debug___getObjectPropertyReflection($property_name);
			}
			return $output;
		}

		$reflection = $this->___debug___getObjectReflection();
		foreach($reflection->getProperties() as $property){
			if($property->isStatic()){
				continue;
			}
			$property->setAccessible(true);
			$output[$property->getName()] = $property;
		}
		return $output;
	}

	/**
	 * @param array|null $which_properties [optional]
	 * @return \ReflectionProperty[]
	 */
	public static function ___debug___getClassPropertiesReflections(array $which_properties = null){
		$output = array();

		if($which_properties !== null){
			foreach($which_properties as $property_name){
				$output[$property_name] = static::___debug___getClassPropertyReflection($property_name);
			}
			return $output;
		}

		$reflection = static::___debug___getClassReflection();
		foreach($reflection->getProperties() as $property){
			$property->setAccessible(true);
			$output[$property->getName()] = $property;
		}

		return $output;
	}

	/**
	 * @param array|null $which_properties [optional]
	 * @return \ReflectionProperty[]
	 */
	public static function ___debug___getNonStaticPropertiesReflections(array $which_properties = null){
		$reflections = static::___debug___getClassPropertiesReflections($which_properties);
		foreach($reflections as $k => $ref){
			if($ref->isStatic()){
				unset($reflections[$k]);
			}
		}
		return $reflections;
	}

	/**
	 * @param array|null $which_properties [optional]
	 * @return \ReflectionProperty[]
	 */
	public static function ___debug___getStaticPropertiesReflections(array $which_properties = null){
		$reflections = static::___debug___getClassPropertiesReflections($which_properties);
		foreach($reflections as $k => $ref){
			if(!$ref->isStatic()){
				unset($reflections[$k]);
			}
		}
		return $reflections;
	}



	/**
	 * @param string $method_name
	 * @return \ReflectionMethod
	 * @throws Debug_Exception
	 */
	public function ___debug___getObjectMethodReflection($method_name){
		try {
			$reflection = new \ReflectionMethod($this, $method_name);
			$reflection->setAccessible(true);
			return $reflection;
		} catch(\ReflectionException $e){
			et_require('Debug_Exception');
			throw new Debug_Exception(
				self::___debug___formatMessage(
					"Failed to get '{CLASS}->{METHOD}()' reflection - {ERROR}",
					array(
						"CLASS" => get_class($this),
						"METHOD" => $method_name,
						"ERROR" => $e->getMessage()
					)
				),
				Debug_Exception::CODE_INVALID_METHOD,
				null,
				$e
			);
		}
	}

	/**
	 * @param string $method_name
	 * @return \ReflectionMethod
	 * @throws Debug_Exception
	 */
	public static function ___debug___getClassMethodReflection($method_name){
		try {
			$reflection = new \ReflectionMethod(get_called_class(), $method_name);
			$reflection->setAccessible(true);
			return $reflection;
		} catch(\ReflectionException $e){
			et_require('Debug_Exception');
			throw new Debug_Exception(
				self::___debug___formatMessage(
					"Failed to get '{CLASS}::{METHOD}()' reflection - {ERROR}",
					array(
						"CLASS" => get_called_class(),
						"METHOD" => $method_name,
						"ERROR" => $e->getMessage()
					)
				),
				Debug_Exception::CODE_INVALID_METHOD,
				null,
				$e
			);
		}
	}




	/**
	 * @param string $property
	 * @param mixed $value
	 *
	 * @throws Debug_Exception
	 * @return Object
	 */
	public function ___debug___setObjectPropertyValue($property, $value){
		$this->___debug___getObjectPropertyReflection($property)->setValue($this, $value);
		return $this;
	}


	/**
	 * @param string $property
	 *
	 * @return mixed
	 * @throws Debug_Exception when property not exists
	 */
	public function ___debug___getObjectPropertyValue($property){
		$this->___debug___getObjectPropertyReflection($property)->getValue($this);
	}



	/**
	 * @param array $which_properties [optional] Return only given properties instead of all
	 *
	 * @throws Debug_Exception when any property not exists
	 * @return array
	 */
	public function ___debug___getObjectPropertiesValues(array $which_properties = null){
		$reflections = $this->___debug___getObjectPropertiesReflections($which_properties);
		$output = array();
		foreach($reflections as $property_name => $reflection){
			$output[$property_name] = $reflection->getValue($this);
		}
		return $output;
	}

	/**
	 * @param array $properties_values
	 *
	 * @throws Debug_Exception when any property not exists
	 * @return Object
	 */
	public function ___debug___setObjectPropertiesValues(array $properties_values){
		foreach($properties_values as $property => $value){
			$this->___debug___setObjectPropertyValue($property, $value);
		}
		return $this;
	}

	/**
	 * @param string $property
	 * @param mixed $value
	 *
	 * @throws Debug_Exception when property not exists
	 */
	public static function ___debug___setClassPropertyValue($property, $value){
		static::___debug___getClassPropertyReflection($property)->setValue(null, $value);
	}

	/**
	 * @param string $property
	 *
	 * @return mixed
	 * @throws Debug_Exception when property not exists
	 */
	public static function ___debug___getClassPropertyValue($property){
		return static::___debug___getClassPropertyReflection($property)->getValue(null);
	}



	/**
	 * @param array $which_properties [optional] Return only given properties instead of all
	 *
	 * @throws Debug_Exception when any property not exists
	 * @return array
	 */
	public static function ___debug___getClassPropertiesValues(array $which_properties = null){
		$reflections = static::___debug___getClassPropertiesReflections($which_properties);
		$output = array();
		foreach($reflections as $property_name => $reflection){
			$output[$property_name] = $reflection->getValue(null);
		}
		return $output;
	}

	/**
	 * @param array $properties_values
	 *
	 * @throws Debug_Exception when any property not exists
	 */
	public static function ___debug___setClassPropertiesValues(array $properties_values){
		foreach($properties_values as $property => $value){
			static::___debug___setClassPropertyValue($property, $value);
		}
	}

	/**
	 * @param array $which_properties [optional] Return only given properties instead of all
	 *
	 * @throws Debug_Exception when any property not exists
	 * @return array
	 */
	public static function ___debug___getStaticPropertiesValues(array $which_properties = null){
		$reflections = static::___debug___getStaticPropertiesReflections($which_properties);
		$output = array();
		foreach($reflections as $property_name => $reflection){
			$output[$property_name] = $reflection->getValue(null);
		}
		return $output;
	}

	/**
	 * @param array $which_properties [optional] Return only given properties instead of all
	 *
	 * @throws Debug_Exception when any property not exists
	 * @return array
	 */
	public static function ___debug___getNonStaticPropertiesValues(array $which_properties = null){
		$reflections = static::___debug___getNonStaticPropertiesReflections($which_properties);
		$output = array();
		foreach($reflections as $property_name => $reflection){
			$output[$property_name] = $reflection->getValue(null);
		}
		return $output;
	}

	/**
	 * @param string $method_name
	 *
	 * @return mixed
	 * @throws Debug_Exception
	 */
	public function ___debug___callObjectMethod($method_name){
		$reflection = $this->___debug___getObjectMethodReflection($method_name);
		$args = func_get_args();
		array_shift($args);
		return $reflection->invokeArgs($this, $args);
	}

	/**
	 * @param string $method_name
	 * @param array $method_arguments [optional]
	 *
	 * @return mixed
	 * @throws Debug_Exception
	 */
	public function ___debug___callObjectMethodArray($method_name, array $method_arguments = array()){
		$reflection = $this->___debug___getObjectMethodReflection($method_name);
		return $reflection->invokeArgs($this, $method_arguments);
	}

	/**
	 * @param string $method_name
	 *
	 * @return mixed
	 * @throws Debug_Exception
	 */
	public static function ___debug___callStaticMethod($method_name){
		$reflection = static::___debug___getClassMethodReflection($method_name);
		$args = func_get_args();
		array_shift($args);
		return $reflection->invokeArgs(null, $args);
	}

	/**
	 * @param string $method_name
	 * @param array $method_arguments [optional]
	 *
	 * @return mixed
	 * @throws Debug_Exception
	 */
	public static function ___debug___callStaticMethodArray($method_name, array $method_arguments = array()){
		$reflection = static::___debug___getClassMethodReflection($method_name);
		return $reflection->invokeArgs(null, $method_arguments);
	}


	/**
	 * @return Object
	 */
	public static function ___debug___createInstanceWithoutConstructor(){
		return static::___debug___getClassReflection()->newInstanceWithoutConstructor();
	}

	/**
	 * @param array $constructor_arguments [optional]
	 * @return Object
	 */
	public static function ___debug___createInstance(array $constructor_arguments = array()){
		return static::___debug___getClassReflection()->newInstanceArgs($constructor_arguments);
	}
}
