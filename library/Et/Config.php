<?php
namespace Et;
et_require('Object');
abstract class Config extends Object {

	const ERR_NOT_DEFINED = "not_defined";
	const ERR_REQUIRED = "required";
	const ERR_TOO_LOW = "too_low";
	const ERR_TOO_HIGH = "too_high";
	const ERR_INVALID_FORMAT = "invalid_format";
	const ERR_INVALID_TYPE = "invalid_type";
	const ERR_INVALID_VALUE = "invalid_value";

	const GENERAL_ERROR = "_general_";
	const CONFIG_TYPE_TEMPLATE = "{TYPE}";
	const CONFIG_TYPE_KEY = "_type_";

	const DEF_TYPE = "type";
	const DEF_NAME = "name";
	const DEF_DESCRIPTION = "description";
	const DEF_REQUIRED = "required";
	const DEF_ARRAY_VALUE_TYPE = "array_value_type";
	const DEF_CONFIG_CLASS = "config_class";
	const DEF_CONFIG_CLASS_TEMPLATE = "config_class_template";
	const DEF_MIN_VALUE = "min_value";
	const DEF_MAX_VALUE = "max_value";
	const DEF_FORMAT = "format";

	const TYPE_BOOL = "Bool";
	const TYPE_INT = "Int";
	const TYPE_STRING = "String";
	const TYPE_FLOAT = "Float";
	const TYPE_ARRAY = "Array";
	const TYPE_CONFIG = "Config";
	const TYPE_CONFIGS_LIST = "ConfigsList";

	/**
	 * @var array[]
	 */
	protected static $__cached_definitions = array();

	/**
	 * @var array
	 */
	protected static $_allowed_properties_types = array(
		self::TYPE_BOOL,
		self::TYPE_INT,
		self::TYPE_STRING,
		self::TYPE_FLOAT,
		self::TYPE_ARRAY,
		self::TYPE_CONFIG,
		self::TYPE_CONFIGS_LIST
	);

	/**
	 * @var array
	 */
	protected static $_common_error_codes = array(
		self::ERR_NOT_DEFINED => "Value is not defined",
		self::ERR_REQUIRED => "Non-empty value is required",
		self::ERR_INVALID_FORMAT => "Invalid value format",
		self::ERR_INVALID_TYPE => "Invalid value type, must be {REQUIRED_TYPE}, is {GIVEN_TYPE}",
		self::ERR_INVALID_VALUE => "Invalid value - {REASON}",
		self::ERR_TOO_HIGH => "Value is too high, must be {MAX_VALUE} or lower",
		self::ERR_TOO_LOW => "Value is too low, must be {MIN_VALUE} or greater",
		self::ERR_INVALID_VALUE => "Invalid value - {REASON}",
	);

	/**
	 * Section name pointing to main config file
	 *
	 * @var string
	 */
	protected static $_system_config_section;

	/**
	 * Definition of config properties
	 *
	 * @var array
	 */
	protected static $_definition = array();

	/**
	 * @var array
	 */
	protected static $_error_codes_list = array();

	/**
	 * @var string
	 */
	protected $_type;


	/**
	 * @var Data_Validation_Result
	 */
	protected $_last_validation_result;


	/**
	 * @param array $properties_values
	 */
	function __construct(array $properties_values = null){
		$this->getDefinition();
		if(!$properties_values){
			$properties_values = array();
		}

		$default_values = $this->getDefaultValues();
		foreach($default_values as $k => $v){
			if(!is_scalar($v) && !isset($properties_values[$k])){
				$properties_values[$k] = $v;
				continue;
			}
			$this->{$k} = $v;
		}


		if($properties_values){
			$this->setPropertiesValues($properties_values);
		}
	}

	/**
	 * @return array
	 */
	public static function getErrorCodesList(){
		$parent_class = get_parent_class(static::class);

		if(is_subclass_of($parent_class, __CLASS__)){
			/** @var $parent_class Config */
			$codes = $parent_class::getErrorCodesList();
		} else {
			$codes = static::$_common_error_codes;
		}

		foreach(static::$_error_codes_list as $code => $message){
			$codes[$code] = $message;
		}
		
		return $codes;

	}

	/**
	 * @return array
	 */
	public static function getDefaultValues(){
		return static::getVisibleClassPropertiesValues();
	}

	/**
	 * @param array $properties_values
	 * @throws Config_Exception
	 */
	function setPropertiesValues(array $properties_values){
		if(!$properties_values){
			return;
		}

		$this->_last_validation_result = null;
		$definitions = $this->getDefinition();
		
		foreach($properties_values as $property => $value){
			if(!isset($definitions[$property])){
				throw new Config_Exception(
					"Property {$this->className()}::\${$property} does not exist or is not defined",
					Config_Exception::CODE_INVALID_ERROR_CODE
				);
			}
			$this->setPropertyValue($property, $definitions[$property], $value);
		}
	}

	/**
	 * @return string|null
	 */
	public function getType() {
		return $this->_type;
	}
	
	
	/**
	 * @return array
	 */
	function getPropertiesValues(){
		$definitions = $this->getDefinition();
		$output = array();
		if($this->_type){
			$output[static::CONFIG_TYPE_KEY] = $this->_type;
		}
		
		foreach($definitions as $property => $definition){
			$output[$property] = $this->{$property};
		}
		return $output;
	}

	/**
	 * @param string $property
	 * @param array $definition
	 * @param mixed $value
	 * @throws Config_Exception
	 */
	protected function setPropertyValue($property, array $definition, $value){

		$property_full_name = static::class . "::\${$property}";

		switch($definition[self::DEF_TYPE]){
			case self::TYPE_BOOL:
				$value = (bool)$value;
				break;

			case self::TYPE_INT:
				$value = (int)$value;
				break;

			case self::TYPE_FLOAT:
				$value = (float)$value;
				break;

			case self::TYPE_STRING:
				$value = (string)$value;
				break;

			case self::TYPE_ARRAY:
				if(!is_array($value)){
					throw new Config_Exception(
						"Property {$property_full_name} value must be array, not " . gettype($value),
						Config_Exception::CODE_INVALID_ERROR_CODE
					);
				}

				$value_type = isset($definition[self::DEF_ARRAY_VALUE_TYPE])
							? self::DEF_ARRAY_VALUE_TYPE
							: self::TYPE_STRING;

				foreach($value as &$v){
					switch($value_type){
						case self::TYPE_BOOL:
							$v = (bool)$v;
							break;

						case self::TYPE_INT:
							$v = (int)$v;
							break;

						case self::TYPE_FLOAT:
							$v = (float)$v;
							break;

						case self::TYPE_STRING:
						default:
							$v = (string)$v;
					}
				}

				break;

			case self::TYPE_CONFIGS_LIST:
				if(!is_array($value)){
					$value = array();
				}

				foreach($value as &$v){
					$v = $this->getConfigProperty($property, $definition, $v);
				}
				break;

			case self::TYPE_CONFIG:
				$value = $this->getConfigProperty($property, $definition, $value);
				break;


			default:
				throw new Config_Exception(
					"Definition type ({$definition[self::DEF_TYPE]}) of property {$property_full_name} is not valid",
					Config_Exception::CODE_INVALID_ERROR_CODE
				);
		}

		$this->{$property} = $value;
	}

	/**
	 * @param string $property
	 * @param array $definition
	 * @param array|\Et\Config $value
	 * @return \Et\Config
	 * @throws Config_Exception
	 */
	protected function getConfigProperty($property, array $definition, $value){

		$property_full_name = static::class . "::\${$property}";

		if(!isset($definition[self::DEF_CONFIG_CLASS])){
			throw new Config_Exception(
				"Missing config class in definition of property {$property_full_name}",
				Config_Exception::CODE_INVALID_DEFINITION
			);
		}

		$config_class = $definition[self::DEF_CONFIG_CLASS];
		if(!is_subclass_of($config_class, "Et\\Config", true)){
			throw new Config_Exception(
				"Class of {$property_full_name} property in definition ({$config_class}) is not subclass of Et\\Config",
				Config_Exception::CODE_INVALID_DEFINITION
			);
		}

		if($value instanceof $config_class){
			return $value;
		}

		if(!is_array($value)){
			throw new Config_Exception(
				"Value of {$property_full_name} must be array or instance of {$config_class} class",
				Config_Exception::CODE_INVALID_PROPERTY
			);
		}

		if(!isset($definition[self::DEF_CONFIG_CLASS_TEMPLATE])){

			$real_config_class = $config_class;

		} else {

			if(!isset($value[static::CONFIG_TYPE_KEY])){
				throw new Config_Exception(
					"Missing config type (array key '".static::CONFIG_TYPE_KEY."') in {$property_full_name} property",
					Config_Exception::CODE_INVALID_PROPERTY
				);
			}
			
			$real_config_class = str_replace(
								static::CONFIG_TYPE_TEMPLATE, 
								$value[static::CONFIG_TYPE_KEY], 
								$definition[static::DEF_CONFIG_CLASS_TEMPLATE]
							);
			
			unset($value[static::CONFIG_TYPE_KEY]);

			try {
				$real_config_class = Factory::getClassName($real_config_class, $config_class, true);
			} catch(Exception $e){
				throw new Config_Exception(
					"Cannot determine config class name for {$property_full_name} - {$e->getMessage()}",
					Config_Exception::CODE_INVALID_PROPERTY,
					null,
					$e
				);
			}

		}


		try {
			
			return new $real_config_class($value);
		} catch(Exception $e){
			throw new Config_Exception(
				"Cannot create instance of config {$real_config_class} for {$property_full_name} property - {$e->getMessage()}",
				Config_Exception::CODE_INVALID_PROPERTY,
				null,
				$e
			);
		}

	}



	/**
	 * @return \Et\Data_Validation_Result
	 */
	public function getLastValidationResult() {
		if(!$this->_last_validation_result){
			$this->validate();
		}
		return $this->_last_validation_result;
	}

	/**
	 * @return bool
	 */
	public function isValid(){
		return $this->getLastValidationResult()->isValid();
	}


	/**
	 * @param bool $force_new_validation
	 * @return bool
	 */
	function validate($force_new_validation = false){
		if(!$this->_last_validation_result || $force_new_validation){
			$this->_last_validation_result = new Data_Validation_Result();
		}
		
		
		$definition = $this->getDefinition();
		$error_codes = $this->getErrorCodesList();
		
		foreach($definition as $prop => $def){
			$value = $this->{$prop};
			if($value === null){
				$this->_last_validation_result->setError($prop, static::ERR_NOT_DEFINED, $error_codes[static::ERR_NOT_DEFINED]);
				continue;
			}

			if(isset($def[self::DEF_MIN_VALUE]) && $value < self::DEF_MIN_VALUE){
				$this->_last_validation_result->setError($prop, static::ERR_TOO_LOW, $error_codes[static::ERR_TOO_LOW], array("MIN_VALUE" => $def[self::DEF_MIN_VALUE]));
				continue;
			}

			if(isset($def[self::DEF_MAX_VALUE]) && $value > self::DEF_MAX_VALUE){
				$this->_last_validation_result->setError($prop, static::ERR_TOO_LOW, $error_codes[static::ERR_TOO_LOW], array("MAX_VALUE" => $def[self::DEF_MAX_VALUE]));
				continue;
			}
			
			if(!empty($def[self::DEF_REQUIRED]) && empty($value)){
				$this->_last_validation_result->setError($prop, static::ERR_REQUIRED, $error_codes[static::ERR_REQUIRED]);
				continue;
			}
			
			if(isset($def[self::DEF_FORMAT])){
				if(is_callable($def[self::DEF_FORMAT])){
					$validation_callback = $def[self::DEF_FORMAT];
					$error_code = static::ERR_INVALID_FORMAT;
					$error_message = $error_codes[static::ERR_INVALID_FORMAT];
					if(!$validation_callback($value, $prop, $def, $this, $error_code, $error_message)){
						$this->_last_validation_result->setError($prop, $error_code, $error_message);
						continue;
					}

				} elseif(!preg_match('~'.$def[self::DEF_FORMAT].'~', $value)) {
					$this->_last_validation_result->setError($prop, static::ERR_INVALID_FORMAT, $error_codes[static::ERR_INVALID_FORMAT]);
					continue;	
				}
			}

		}

		return $this->_last_validation_result->isValid();
	}


	protected static function fillDefinition($property, $default_value, array $definition = array()){
		if(!isset($definition[self::DEF_TYPE])){
			$value_type = gettype($default_value);
			switch($value_type){
				case "boolean":
					$definition[self::DEF_TYPE] = self::TYPE_BOOL;
					break;

				case "integer":
					$definition[self::DEF_TYPE] = self::TYPE_INT;
					break;

				case "double":
					$definition[self::DEF_TYPE] = self::TYPE_FLOAT;
					break;

				case "string":
					$definition[self::DEF_TYPE] = self::TYPE_STRING;
					break;

				case "array":
					if(isset($default_value[self::CONFIG_TYPE_KEY]) || isset($definition[self::DEF_CONFIG_CLASS])){
						$definition[self::DEF_TYPE] = self::TYPE_CONFIG;
					} else {
						foreach($default_value as $v){
							if(is_array($v) && isset($v[self::CONFIG_TYPE_KEY])){
								$definition[self::DEF_TYPE] = self::TYPE_CONFIG;
							}
						}
						if(!isset($definition[self::DEF_TYPE])){
							$definition[self::DEF_TYPE] = self::TYPE_ARRAY;
						}
					}
					break;

				default:
					if($default_value instanceof Config){
						$definition[self::DEF_TYPE] = self::TYPE_CONFIG;
						if(!isset($definition[self::DEF_CONFIG_CLASS])){
							$definition[self::DEF_CONFIG_CLASS] = get_class($default_value);
						}
					} else {
						$definition[self::DEF_TYPE] = self::TYPE_STRING;
					}
			}
		}

		if(!isset($definition[self::DEF_NAME])){
			$definition[self::DEF_NAME] = ucfirst(str_replace("_", " ", $property));
		}

		if(!isset($definition[self::DEF_DESCRIPTION])){
			$definition[self::DEF_DESCRIPTION] = "";
		}

		if(!isset($definition[self::DEF_REQUIRED])){
			$definition[self::DEF_REQUIRED] = $default_value === null;
		}

		return $definition;
	}

	/**
	 * @throws Config_Exception
	 * @return array
	 */
	public static function getDefinition(){
		$class = static::class;

		if(isset(self::$__cached_definitions[$class])){
			return self::$__cached_definitions[$class];
		}

		$parent_class = get_parent_class($class);

		if(is_subclass_of($parent_class, __CLASS__)){
			/** @var $parent_class Config */
			$definition = $parent_class::getDefinition();
		} else {
			$definition = array();
		}

		$default_values = static::getDefaultValues();
		foreach($default_values as $property => $default_value){

			$property_full_name = "{$class}::\${$property}";

			if(isset(static::$_definition[$property])){
				$definition[$property] = static::$_definition[$property];
			}

			if(!isset($definition[$property])){
				$definition[$property] = array();
				/*
				throw new Config_Exception(
					"Missing definition of property '{$property_full_name}'",
					Config_Exception::CODE_INVALID_DEFINITION
				);
				*/
			}

			if(!is_array($definition[$property])){
				throw new Config_Exception(
					"Invalid config property '{$property_full_name}' definition - must be an array, not " . gettype($definition[$property]),
					Config_Exception::CODE_INVALID_DEFINITION
				);
			}

			$definition[$property] = $def = static::fillDefinition($property, $default_value, $definition[$property]);

			if(!isset($def[self::DEF_TYPE])){
				throw new Config_Exception(
					"Missing type of config property '{$property_full_name}' and cannot determine automatically",
					Config_Exception::CODE_INVALID_DEFINITION
				);
			}

			if(!in_array($def[self::DEF_TYPE], static::$_allowed_properties_types)){
				throw new Config_Exception(
					"Invalid type '{$def[self::DEF_TYPE]}' of config property '{$property_full_name}' - only '".implode("', '", static::$_allowed_properties_types)."' allowed",
					Config_Exception::CODE_INVALID_DEFINITION
				);
			}


		}

		self::$__cached_definitions[$class] = $definition;
		return self::$__cached_definitions[$class];
	}


	/**
	 * @param null|string $custom_application_config_section [optional]
	 * @return static|\Et\Config
	 * @throws Config_Exception
	 */
	public static function getFromEnvironmentConfig($custom_application_config_section = null){
		if(!$custom_application_config_section){
			$custom_application_config_section = static::$_system_config_section;
		}
		
		if(!$custom_application_config_section){
			throw new Config_Exception(
				"No environment config section defined for config " . static::class,
				Config_Exception::CODE_INVALID_SECTION
			);
		}
		
		$data = System::getConfig()->getSectionData($custom_application_config_section);
		return new static($data);
	}

	/**
	 * @param array|null $properties_values [optional]
	 * @return static|\Et\Config
	 */
	public static function getInstance(array $properties_values = null){
		return new static($properties_values);
	}
	

}