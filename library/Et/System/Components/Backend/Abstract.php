<?php
namespace Et;

abstract class System_Components_Backend_Abstract extends Object {

	/**
	 * @var System_Components_Backend_Config_Abstract
	 */
	protected $config;

	/**
	 * @param System_Components_Backend_Config_Abstract $config
	 */
	function __construct(System_Components_Backend_Config_Abstract $config){
		$this->config = $config;
	}

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