<?php
namespace Et;

abstract class System_Components_Backend_Abstract extends Object_Adapter {

	/**
	 * @var string
	 */
	protected static $__adapter_class_prefix = 'Et\System_Components_Backend';

	/**
	 * @var System_Components_Backend_Config_Abstract
	 */
	protected $config;

	/**
	 * @return System_Components_Backend_Config_Abstract
	 */
	function getConfig(){
		return $this->config;
	}

	/**
	 * @return array
	 */
	abstract function getComponentsTypes();

	/**
	 * @param string $components_type
	 * @return System_Components_List|bool
	 */
	abstract function loadComponents($components_type);

	/**
	 * @param System_Components_List $components
	 * @throws System_Components_Exception
	 */
	abstract function storeComponents(System_Components_List $components);

	/**
	 * @param string $components_type
	 * @return bool
	 */
	abstract function removeComponents($components_type);

	/**
	 * @param string $components_type
	 * @return bool
	 */
	abstract function getComponentsExist($components_type);

}