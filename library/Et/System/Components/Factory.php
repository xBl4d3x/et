<?php
namespace Et;
et_require('Object');
class System_Components_Factory extends Object {

	const BACKEND_CLASS_NAME_TEMPLATE = 'Et\System_Components_Backend_{TYPE}';
	const BACKEND_CONFIG_CLASS_NAME_TEMPLATE = 'Et\System_Components_Backend_{TYPE}_Config';
	const COMPONENT_CLASS_NAME = 'Et\System_Components_Component';

	/**
	 * @param string $type
	 * @param string $template
	 * @return string
	 */
	protected static function getOriginalClassName($type, $template){
		self::assert()->isVariableName($type);
		return str_replace("{TYPE}", $type, $template);
	}

	/**
	 * @param string $backend_type
	 * @return string
	 */
	public static function getBackendClassName($backend_type){
		$original_class = static::getOriginalClassName($backend_type, static::BACKEND_CLASS_NAME_TEMPLATE);
		return Factory::getClassName($original_class, 'Et\System_Components_Backend_Abstract');
	}

	/**
	 * @param string $backend_type
	 * @return string
	 */
	public static function getBackendConfigClassName($backend_type){
		$original_class = static::getOriginalClassName($backend_type, static::BACKEND_CONFIG_CLASS_NAME_TEMPLATE);
		return Factory::getClassName($original_class, 'Et\System_Components_Backend_Config_Abstract');
	}


	/**
	 * @param string $backend_type
	 * @param array $config_data [optional]
	 * @return System_Components_Backend_Config_Abstract
	 */
	public static function getBackendConfigInstance($backend_type, array $config_data = array()){
		$class_name = static::getBackendConfigClassName($backend_type);
		return new $class_name($config_data);
	}

	/**
	 * @return string
	 */
	public static function getComponentClassName(){
		return Factory::getClassName(static::COMPONENT_CLASS_NAME, static::COMPONENT_CLASS_NAME);
	}
}