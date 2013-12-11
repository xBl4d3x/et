<?php
namespace Et;
et_require("Object");
class System_Components extends Object {

	/**
	 * @var System_Components_Config
	 */
	protected static $config;

	/**
	 * @var System_Components_Backend_Abstract
	 */
	protected static $backend;

	/**
	 * @var System_Components_List[]
	 */
	protected static $components = array();

	/**
	 * @var bool
	 */
	protected static $_save_registered = false;

	/**
	 * @param System_Components_Backend_Abstract $backend
	 */
	public static function setBackend(System_Components_Backend_Abstract $backend) {
		self::$backend = $backend;
	}

	/**
	 * @return System_Components_Backend_Abstract
	 */
	public static function getBackend() {
		if(!self::$backend){
			self::$backend = self::getConfig()->getBackendConfig()->getBackendInstance();
		}
		return self::$backend;
	}

	/**
	 * @param System_Components_Config $config
	 */
	public static function setConfig(System_Components_Config $config) {
		self::$config = $config;
		self::$backend = null;
	}

	/**
	 * @return System_Components_Config
	 */
	public static function getConfig() {
		if(!self::$config){
			self::$config = System_Components_Config::getFromApplicationConfig();
		}
		return self::$config;
	}
	
	protected static function registerSave(){
		if(self::$_save_registered){
			return;
		}
		Application::addOnEndCallback(array(get_called_class(), "saveComponents"));
		self::$_save_registered = true;
	}


	/**
	 * @param string $components_type
	 * @return System_Components_List
	 * @throws System_Components_Exception when load failed (i.e. backend failure)
	 */
	public static function getComponentsList($components_type){
		if(isset(self::$components[$components_type])){
			return self::$components[$components_type];
		}		
		
		try {
			
			$list = self::getBackend()->loadComponents($components_type);
			if(!$list){
				$list = new System_Components_List($components_type);
			}
			self::$components[$components_type] = $list;
			
			self::registerSave();
			
			return self::$components[$components_type];
			
		} catch(\Exception $e){
			
			throw new System_Components_Exception(
				"Failed to load components with type '{$components_type} - {$e->getMessage()}",
				System_Components_Exception::CODE_LOAD_FAILED,
				null,
				$e
			);
		}	
	}

	/**
	 * @param string $components_type
	 * @return System_Components_Component[]
	 */
	public static function getComponents($components_type){
		return self::getComponentsList($components_type)->getComponents();
	}

	/**
	 * @param string $components_type
	 * @return System_Components_Component[]
	 */
	public static function getInstalledComponents($components_type){
		return self::getComponentsList($components_type)->getInstalledComponents();
	}

	/**
	 * @param string $components_type
	 * @return System_Components_Component[]
	 */
	public static function getEnabledComponents($components_type){
		return self::getComponentsList($components_type)->getEnabledComponents();
	}
	

	/**
	 * @param string $component_type
	 * @param string $component_name
	 * @return bool|System_Components_Component
	 */
	public static function getComponent($component_type, $component_name){
		return self::getComponentsList($component_type)->getComponent($component_name);
	}

	/**
	 * @param string $component_type
	 * @param string $component_name
	 * @return bool
	 */
	public static function getComponentExists($component_type, $component_name){
		return self::getComponentsList($component_type)->getComponentExists($component_name);	
	}

	/**
	 * @param string $component_type
	 * @param string $component_name
	 * @return bool
	 */
	public static function getComponentIsInstalled($component_type, $component_name){
		return self::getComponentsList($component_type)->getComponentIsInstalled($component_name);
	}

	/**
	 * @param string $component_type
	 * @param string $component_name
	 * @return bool
	 */
	public static function getComponentIsEnabled($component_type, $component_name){
		return self::getComponentsList($component_type)->getComponentIsEnabled($component_name);
	}

	/**
	 * @param string $component_type
	 * @param string $component_name
	 * @return bool
	 */
	public static function removeComponent($component_type, $component_name){
		return self::getComponentsList($component_type)->removeComponent($component_name);
	}

	/**
	 * @param string $components_type
	 * @return bool
	 */
	public static function removeComponents($components_type){
		if(isset(self::$components[$components_type])){
			unset(self::$components[$components_type]);
		}
		return self::getBackend()->removeComponents($components_type);
	}


	/**
	 * @param string $component_type
	 * @param string $component_name
	 * @param string $label [optional]
	 * @param string $description [optional]
	 * @return System_Components_Component
	 */
	public static function createComponent($component_type, $component_name, $label = "", $description = ""){
		return self::getComponentsList($component_type)->createComponent($component_name, $label, $description);
	}

	/**
	 * @param string $component_type
	 * @param string $component_name
	 * @param string $label [optional]
	 * @param string $description [optional]
	 * @param bool $store_component [optional]
	 * @return System_Components_Component
	 */
	public static function getOrCreateComponent($component_type, $component_name, $label = "", $description = "", $store_component = true){
		return self::getComponentsList($component_type)->getOrCreateComponent($component_name, $label, $description, $store_component);
	}
	
	

	public static function saveComponents(){
		foreach(self::$components as $list){
			if($list->hasChanged()){
				self::getBackend()->storeComponents($list);
			}
		}
	}
	

	/**
	 * @return System_Components_List[]
	 */
	public static function getAllComponentsLists(){
		$backend = self::getBackend();
		$types = $backend->getComponentsTypes();
		foreach($types as $type){
			if(!isset(self::$components[$type])){
				self::$components[$type] = self::getComponentsList($type);
			}
		}
		return self::$components;
	}

	/**
	 * @return System_Components_Component[][]
	 */
	public static function getAllComponents(){
		$output = array();
		$lists = self::getAllComponentsLists();
		foreach($lists as $components_type => $components){
			$output[$components_type] = array();
			foreach($components as $component_name => $component){
				$output[$components_type][$component_name] = $component;
			}
		}
		return $output;
	}
	

	
}