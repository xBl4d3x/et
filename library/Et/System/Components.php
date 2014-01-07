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
		static::$backend = $backend;
	}

	/**
	 * @return System_Components_Backend_Abstract
	 */
	public static function getBackend() {
		if(!static::$backend){
			$backend_config = static::getConfig()->getBackendConfig();
			$backend_class = Factory::getClassName(
									"Et\\System_Components_Backend_{$backend_config->getType()}",
									"Et\\System_Components_Backend_Abstract"
							);
			static::$backend = new $backend_class($backend_config);
		}
		return static::$backend;
	}

	/**
	 * @param System_Components_Config $config
	 */
	public static function setConfig(System_Components_Config $config) {
		static::$config = $config;
		static::$backend = null;
	}

	/**
	 * @return System_Components_Config
	 */
	public static function getConfig() {
		if(!static::$config){
			static::$config = System_Components_Config::getFromSystemConfig();
		}
		return static::$config;
	}
	
	protected static function registerSave(){
		if(static::$_save_registered){
			return;
		}
		System::addShutdownCallback(array(static::class, "saveComponents"));
		static::$_save_registered = true;
	}


	/**
	 * @param string $components_type
	 * @return System_Components_List
	 * @throws System_Components_Exception when load failed (i.e. backend failure)
	 */
	public static function getComponentsList($components_type){
		if(isset(static::$components[$components_type])){
			return static::$components[$components_type];
		}		
		
		try {
			
			$list = static::getBackend()->loadComponents($components_type);
			if(!$list){
				$list = new System_Components_List($components_type);
			}
			static::$components[$components_type] = $list;
			
			static::registerSave();
			
			return static::$components[$components_type];
			
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
		return static::getComponentsList($components_type)->getComponents();
	}

	/**
	 * @param string $components_type
	 * @return System_Components_Component[]
	 */
	public static function getInstalledComponents($components_type){
		return static::getComponentsList($components_type)->getInstalledComponents();
	}

	/**
	 * @param string $components_type
	 * @return System_Components_Component[]
	 */
	public static function getEnabledComponents($components_type){
		return static::getComponentsList($components_type)->getEnabledComponents();
	}
	

	/**
	 * @param string $component_type
	 * @param string $component_name
	 * @return bool|System_Components_Component
	 */
	public static function getComponent($component_type, $component_name){
		return static::getComponentsList($component_type)->getComponent($component_name);
	}

	/**
	 * @param string $component_type
	 * @param string $component_name
	 * @return bool
	 */
	public static function getComponentExists($component_type, $component_name){
		return static::getComponentsList($component_type)->getComponentExists($component_name);	
	}

	/**
	 * @param string $component_type
	 * @param string $component_name
	 * @return bool
	 */
	public static function getComponentIsInstalled($component_type, $component_name){
		return static::getComponentsList($component_type)->getComponentIsInstalled($component_name);
	}

	/**
	 * @param string $component_type
	 * @param string $component_name
	 * @return bool
	 */
	public static function getComponentIsEnabled($component_type, $component_name){
		return static::getComponentsList($component_type)->getComponentIsEnabled($component_name);
	}

	/**
	 * @param string $component_type
	 * @param string $component_name
	 * @return bool
	 */
	public static function removeComponent($component_type, $component_name){
		return static::getComponentsList($component_type)->removeComponent($component_name);
	}

	/**
	 * @param string $components_type
	 * @return bool
	 */
	public static function removeComponents($components_type){
		if(isset(static::$components[$components_type])){
			unset(static::$components[$components_type]);
		}
		return static::getBackend()->removeComponents($components_type);
	}


	/**
	 * @param string $component_type
	 * @param string $component_name
	 * @param string $label [optional]
	 * @param string $description [optional]
	 * @return System_Components_Component
	 */
	public static function createComponent($component_type, $component_name, $label = "", $description = ""){
		return static::getComponentsList($component_type)->createComponent($component_name, $label, $description);
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
		return static::getComponentsList($component_type)->getOrCreateComponent($component_name, $label, $description, $store_component);
	}
	
	

	public static function saveComponents(){
		foreach(static::$components as $list){
			if($list->hasChanged()){
				static::getBackend()->storeComponents($list);
			}
		}
	}
	

	/**
	 * @return System_Components_List[]
	 */
	public static function getAllComponentsLists(){
		$backend = static::getBackend();
		$types = $backend->getComponentsTypes();
		foreach($types as $type){
			if(!isset(static::$components[$type])){
				static::$components[$type] = static::getComponentsList($type);
			}
		}
		return static::$components;
	}

	/**
	 * @return System_Components_Component[][]
	 */
	public static function getAllComponents(){
		$output = array();
		$lists = static::getAllComponentsLists();
		foreach($lists as $components_type => $components){
			$output[$components_type] = array();
			foreach($components as $component_name => $component){
				$output[$components_type][$component_name] = $component;
			}
		}
		return $output;
	}
	

	
}