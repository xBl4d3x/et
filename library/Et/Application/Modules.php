<?php
namespace Et;
class Application_Modules extends Object {

	const SYSTEM_COMPONENTS_TYPE = "modules";
	
	/**
	 * @var array
	 */
	protected static $module_IDs = array();

	/**
	 * @var Application_Modules_Module_Metadata[]|System_Components_List
	 */
	protected static $modules_metadata = array();

	/**
	 * @var Application_Modules_Module[]
	 */
	protected static $module_instances = array();

	/**
	 * @var bool
	 */
	protected static $initialized = false;

	
	public static function initialize(){
		if(self::$initialized){
			return;
		}

		Loader::registerLoader(new Application_Modules_Loader());

		$modules_dir = System::getDir(ET_MODULES_PATH);
		$modules_names = $modules_dir->listDirNames(
			function($dir_name){
				return (bool)preg_match('~^\w+$~', $dir_name);
			}
		);
		
		static::$module_IDs = array_combine($modules_names, $modules_names);
		static::$modules_metadata = System_Components::getComponentsList(static::SYSTEM_COMPONENTS_TYPE);

		self::$initialized = true;
	}


	/**
	 * @param string $module_ID
	 * @return Application_Modules_Module_Config
	 * @throws Application_Modules_Exception
	 */
	public static function getModuleConfig($module_ID){
		return self::getModuleMetadata($module_ID)->getConfig();
	}

	/**
	 * @param string $module_ID
	 * @return Application_Modules_Module_Installer
	 * @throws Application_Modules_Exception
	 */
	public static function getModuleInstaller($module_ID){
		return static::getModuleMetadata($module_ID)->getInstaller();
	}

	/**
	 * @param string $module_ID
	 * @param bool $enabled_only [optional]
	 * @return Application_Modules_Module
	 * @throws Application_Modules_Exception
	 */
	public static function getModuleInstance($module_ID, $enabled_only = true){

		$metadata = self::getModuleMetadata($module_ID);
		$enabled = $metadata->isEnabled();
		if(!$enabled && $enabled_only){
			throw new Application_Modules_Exception(
				"Module '{$module_ID}' is not enabled",
				Application_Modules_Exception::CODE_MODULE_NOT_ENABLED
			);
		}

		if(isset(self::$module_instances[$module_ID])){
			if(!$enabled){
				unset(self::$module_instances[$module_ID]);
			} else {
				return self::$module_instances[$module_ID];
			}
		}


		$module_class = "EtM\\{$module_ID}\\Module";
		if(!class_exists($module_class) || !is_subclass_of($module_class, 'Et\Application_Modules_Module', true)){
			throw new Application_Modules_Exception(
				"Module '{$module_ID}' main model (class {$module_class}) does not exist or is not subclass of Et\\Application_Modules_Module",
				Application_Modules_Exception::CODE_MODULE_NOT_EXIST
			);
		}


		$initialize = $enabled;
		$module_instance = new $module_class($metadata, $initialize);
		if(!$enabled){
			return $module_instance;
		}

		self::$module_instances[$module_ID] = $module_instance;
		return self::$module_instances[$module_ID];
	}

	/**
	 * @param string $module_ID
	 * @return bool
	 */
	public static function getModuleExists($module_ID){
		return isset(static::$module_IDs[$module_ID]);
	}

	/**
	 * @param string $module_ID
	 * @return bool
	 */
	public static function getModuleIsInstalled($module_ID){
		return static::getModuleExists($module_ID) && static::getModuleMetadata($module_ID)->isInstalled();
	}

	/**
	 * @param string $module_ID
	 * @return bool
	 */
	public static function getModuleIsEnabled($module_ID){
		return static::getModuleExists($module_ID) && static::getModuleMetadata($module_ID)->isEnabled();
	}

	/**
	 * @param string $module_ID
	 * @return bool
	 */
	public static function getModuleIsOutdated($module_ID){
		return static::getModuleExists($module_ID) && static::getModuleMetadata($module_ID)->isOutdated();
	}

	/**
	 * @param string $module_ID
	 */
	public static function reloadModuleMetadata($module_ID){
		static::getModuleMetadata($module_ID)->reload();
	}
	
	public static function reloadModulesMetadata(){
		foreach(static::$module_IDs as $module_ID){
			static::reloadModuleMetadata($module_ID);
		}
	}

	/**
	 * @param string $module_ID
	 * @throws Application_Modules_Exception
	 */
	public static function checkModuleIDFormat($module_ID){
		if(!preg_match('~^\w+$~', $module_ID)){
			throw new Application_Modules_Exception(
				"Invalid module name format of '{$module_ID}'",
				Application_Modules_Exception::CODE_INVALID_MODULE_ID
			);
		}
	}

	/**
	 * @param string $module_ID
	 * @return Application_Modules_Module_Metadata
	 * @throws Application_Modules_Exception
	 */
	public static function getModuleMetadata($module_ID){
		static::checkModuleExists($module_ID);
		if(!isset(self::$modules_metadata[$module_ID])){
			$metadata = new Application_Modules_Module_Metadata($module_ID);
			static::$modules_metadata->addComponent($metadata);
		}
		return self::$modules_metadata[$module_ID];
	}

	/**
	 * @return array
	 */
	public static function getModuleIDs(){
		return static::$module_IDs;
	}

	/**
	 * @return array
	 */
	public static function getModuleNames(){
		$names = array();
		foreach(static::$module_IDs as $module_ID){
			$names[$module_ID] = static::getModuleMetadata($module_ID)->getModuleName();
		}
		return $names;
	}

	/**
	 * @param string $module_ID
	 * @return string
	 */
	public static function getModuleName($module_ID){
		return static::getModuleMetadata($module_ID)->getModuleName();
	}

	/**
	 * @return Application_Modules_Module_Metadata[]|System_Components_List
	 */
	public static function getModulesMetadata(){
		foreach(static::$module_IDs as $module_ID){
			if(!isset(static::$modules_metadata[$module_ID])){
				static::getModuleMetadata($module_ID);
			}
		}
		return static::$modules_metadata;
	}

	/**
	 * @return Application_Modules_Module_Metadata[]
	 */
	public static function getInstalledModulesMetadata(){
		$output = array();
		foreach(static::$module_IDs as $module_ID){
			$metadata = static::getModuleMetadata($module_ID);
			if(!$metadata->isInstalled()){
				continue;
			}
			$output[$module_ID] = $metadata;
		}
		return $output;
	}

	/**
	 * @return array
	 */
	public static function getInstalledModuleIDs(){
		return array_keys(self::getInstalledModulesMetadata());
	}

	/**
	 * @return array
	 */
	public static function getInstalledModuleNames(){
		$labels = array();
		$modules_metadata = self::getInstalledModulesMetadata();
		foreach($modules_metadata as $module_ID => $metadata){
			$labels[$module_ID] = $metadata->getModuleName();
		}
		return $labels;
	}


	/**
	 * @return Application_Modules_Module_Metadata[]
	 */
	public static function getEnabledModulesMetadata(){
		$output = array();
		foreach(static::$module_IDs as $module_ID){
			$metadata = static::getModuleMetadata($module_ID);
			if(!$metadata->isEnabled()){
				continue;
			}
			$output[$module_ID] = $metadata;
		}
		return $output;
	}

	/**
	 * @return array
	 */
	public static function getEnabledModuleIDs(){
		return array_keys(self::getEnabledModulesMetadata());
	}


	/**
	 * @return array
	 */
	public static function getEnabledModuleNames(){
		$labels = array();
		$modules_metadata = self::getEnabledModulesMetadata();
		foreach($modules_metadata as $module_ID => $metadata){
			$labels[$module_ID] = $metadata->getModuleName();
		}
		return $labels;
	}


	/**
	 * @return Application_Modules_Module_Metadata[]
	 */
	public static function getOutdatedModulesMetadata(){
		$output = array();
		foreach(static::$module_IDs as $module_ID){
			$metadata = static::getModuleMetadata($module_ID);
			if(!$metadata->isOutdated()){
				continue;
			}
			$output[$module_ID] = $metadata;
		}
		return $output;
	}

	/**
	 * @return array
	 */
	public static function getOutdatedModuleIDs(){
		return array_keys(self::getOutdatedModulesMetadata());
	}


	/**
	 * @return array
	 */
	public static function getOutdatedModuleNames(){
		$labels = array();
		$modules_metadata = self::getOutdatedModulesMetadata();
		foreach($modules_metadata as $module_ID => $metadata){
			$labels[$module_ID] = $metadata->getModuleName();
		}
		return $labels;
	}

	/**
	 * @param string $module_ID
	 * @throws Application_Modules_Exception
	 */
	public static function checkModuleExists($module_ID){
		if(!self::getModuleExists($module_ID)){
			throw new Application_Modules_Exception(
				"Module '{$module_ID}' does not exist",
				Application_Modules_Exception::CODE_MODULE_NOT_EXIST
			);
		}
	}

	/**
	 * @param string $module_ID
	 * @throws Application_Modules_Exception
	 */
	public static function checkModuleIsInstalled($module_ID){
		self::checkModuleExists($module_ID);
		if(!self::getModuleIsInstalled($module_ID)){
			throw new Application_Modules_Exception(
				"Module '{$module_ID}' is not installed",
				Application_Modules_Exception::CODE_MODULE_NOT_INSTALLED
			);
		}
	}

	/**
	 * @param string $module_ID
	 * @throws Application_Modules_Exception
	 */
	public static function checkModuleIsEnabled($module_ID){
		self::checkModuleExists($module_ID);
		if(!self::getModuleIsEnabled($module_ID)){
			throw new Application_Modules_Exception(
				"Module '{$module_ID}' is not enabled",
				Application_Modules_Exception::CODE_MODULE_NOT_ENABLED
			);
		}
	}

	/**
	 * @param array $tags
	 * @param bool $enabled_only [optional]
	 * @return array
	 */
	public static function getModuleIDsHavingAnyTag(array $tags, $enabled_only = true){
		return array_keys(static::getModulesMetadataHavingAnyTag($tags, $enabled_only));
	}

	/**
	 * @param array $tags
	 * @param bool $enabled_only [optional]
	 * @return Application_Modules_Module_Metadata[]
	 */
	public static function getModulesMetadataHavingAnyTag(array $tags, $enabled_only = true){
		$output = array();
		foreach(static::$modules_metadata as $ID => $metadata){
			if(!$metadata->isEnabled() && $enabled_only){
				continue;
			}
			if($metadata->hasAnyTag($tags)){
				$output[$ID] = $metadata;
			}
		}
		return $output;
	}


	/**
	 * @param array $tags
	 * @param bool $enabled_only [optional]
	 * @return array
	 */
	public static function getModuleIDsHavingAllTags(array $tags, $enabled_only = true){
		return array_keys(static::getModulesMetadataHavingAllTags($tags, $enabled_only));
	}

	/**
	 * @param array $tags
	 * @param bool $enabled_only [optional]
	 * @return Application_Modules_Module_Metadata[]
	 */
	public static function getModulesMetadataHavingAllTags(array $tags, $enabled_only = true){
		$output = array();
		foreach(static::$modules_metadata as $ID => $metadata){
			if(!$metadata->isEnabled() && $enabled_only){
				continue;
			}
			if($metadata->hasAllTags($tags)){
				$output[$ID] = $metadata;
			}
		}
		return $output;
	}

	/**
	 * @param array $tags
	 * @param bool $enabled_only [optional]
	 * @return array
	 */
	public static function getModuleIDsNotHavingAnyTag(array $tags, $enabled_only = true){
		return array_keys(static::getModulesMetadataNotHavingAnyTag($tags, $enabled_only));
	}

	/**
	 * @param array $tags
	 * @param bool $enabled_only [optional]
	 * @return Application_Modules_Module_Metadata[]
	 */
	public static function getModulesMetadataNotHavingAnyTag(array $tags, $enabled_only = true){
		$output = array();
		foreach(static::$modules_metadata as $ID => $metadata){
			if(!$metadata->isEnabled() && $enabled_only){
				continue;
			}
			if($metadata->hasNotAnyTag($tags)){
				$output[$ID] = $metadata;
			}
		}
		return $output;
	}

	/**
	 * @param array $tags
	 * @param bool $enabled_only [optional]
	 * @return array
	 */
	public static function getModuleIDsNotHavingAllTags(array $tags, $enabled_only = true){
		return array_keys(static::getModulesMetadataNotHavingAllTags($tags, $enabled_only));
	}

	/**
	 * @param array $tags
	 * @param bool $enabled_only [optional]
	 * @return Application_Modules_Module_Metadata[]
	 */
	public static function getModulesMetadataNotHavingAllTags(array $tags, $enabled_only = true){
		$output = array();
		foreach(static::$modules_metadata as $ID => $metadata){
			if(!$metadata->isEnabled() && $enabled_only){
				continue;
			}
			if($metadata->hasNotAllTags($tags)){
				$output[$ID] = $metadata;
			}
		}
		return $output;
	}
}