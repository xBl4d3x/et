<?php
namespace Et;
class Application_Modules_Module_Metadata extends System_Components_Component {

	const METADATA_PATH_RELATIVE = "config/metadata.php";

	/**
	 * @var System_File
	 */
	protected $_metadata_file;

	/**
	 * @var System_Dir
	 */
	protected $_module_directory;

	/**
	 * @var string
	 */
	protected $_module_URL;

	/**
	 * @var string
	 */
	protected $_module_URI;

	/**
	 * @var Application_Modules_Module_Config
	 */
	protected $_config;

	/**
	 * @var Application_Modules_Module_Installer
	 */
	protected $_installer;




	/**
	 * @var string
	 */
	protected $vendor = "ET";

	/**
	 * @var array
	 */
	protected $tags = array();

	/**
	 * @var array
	 */
	protected $localized_names = array();

	/**
	 * @var int
	 */
	protected $version;

	/**
	 * @var callable[]
	 */
	protected $signal_handlers = array();

	/**
	 * @var array
	 */
	protected $factory_class_map = array();


	/**
	 * @param string $module_ID
	 */
	function __construct($module_ID){
		Application_Modules::checkModuleIDFormat($module_ID);
		$this->name = $module_ID;
		$this->loadMetadata();
		parent::__construct($module_ID, $this->getName(), $this->getDescription());
	}

	function reload(){
		$this->loadMetadata();
		$this->changed = true;
	}


	/**
	 * @throws Application_Modules_Exception
	 */
	protected function loadMetadata(){
		try {

			$metadata = new Data_Array($this->getMetadataFile()->includeArrayContent());

			$this->vendor = trim($metadata->getString("vendor"));
			$this->name = trim($metadata->getString("name"));
			$this->description = trim($metadata->getString("description"));
			$this->tags = $metadata->getRawValue("tags", array());
			$this->version = $metadata->getInt("version");
			$this->signal_handlers = $metadata->getRawValue("signal_handlers", array());
			$this->factory_class_map = $metadata->getRawValue("factory_class_map", array());
			$this->localized_names = $metadata->getRawValue("localized_names", array());

			$assert = $this->assert();
			$assert->isNotEmpty($this->vendor, "vendor not specified");
			$assert->isNotEmpty($this->name, "module name not specified");
			$assert->isArray($this->tags, "tags must be an array");
			$assert->isArray($this->signal_handlers, "signal handlers must be an array");
			$assert->isArray($this->factory_class_map, "factory class map must be an array");
			$assert->isArray($this->localized_names, "localized names must be an array");

			$this->tags = array_combine(array_values($this->tags), array_values($this->tags));

		} catch(Exception $e){

			throw new Application_Modules_Exception(
				"Failed to load module '{$this->getModuleID()}' metadata - {$e->getMessage()}",
				Application_Modules_Exception::CODE_INVALID_METADATA,
				null,
				$e
			);
		}
	}

	function __sleep(){
		return $this->getVisibleObjectPropertiesNames();
	}

	/**
	 * @return System_File
	 */
	public function getMetadataFile() {
		if(!$this->_metadata_file){
			$this->_metadata_file = new System_File((string)$this->getModuleDirectory() . self::METADATA_PATH_RELATIVE);
		}
		return $this->_metadata_file;
	}

	/**
	 * @return string
	 */
	public function getModuleURI() {
		if(!$this->_module_URI){
			return ET_BASE_URI . "modules/{$this->getModuleID()}/";
		}
		return $this->_module_URI;
	}

	/**
	 * @return string
	 */
	public function getModuleURL() {
		if(!$this->_module_URI){
			return ET_BASE_URL . "modules/{$this->getModuleID()}/";
		}
		return $this->_module_URL;
	}

	/**
	 * @return System_Dir
	 */
	public function getModuleDirectory() {
		if(!$this->_module_directory){
			$this->_module_directory = System::getDir(ET_MODULES_PATH . $this->getModuleID() . "/");
		}
		return $this->_module_directory;
	}

	/**
	 * @return bool
	 */
	function isOutdated(){
		return $this->isInstalled() && $this->getVersion() != $this->getInstalledVersion();
	}

	/**
	 * @return Application_Modules_Module_Config
	 * @throws Application_Modules_Exception
	 */
	public function getConfig(){
		if($this->_config){
			return $this->_config;
		}

		$config_class = "EtM\\{$this->getModuleID()}\\Config";
		if(!class_exists($config_class) || !is_subclass_of($config_class, 'Et\Application_Modules_Module_Config', true)){
			throw new Application_Modules_Exception(
				"Configuration class '{$config_class}' for module {$this->getID()} ({$this->getName()}) not found or is not subclass of Et\\Application_Modules_Module_Config",
				Application_Modules_Exception::CODE_INVALID_CONFIG
			);
		}
		$this->_config = new $config_class($this);

		return $this->_config;
	}

	/**
	 * @return Application_Modules_Module_Installer
	 * @throws Application_Modules_Exception
	 */
	public function getInstaller(){
		if($this->_installer){
			return $this->_installer;
		}

		$installer_class = "EtM\\{$this->getModuleID()}\\Installer";
		if(!class_exists($installer_class) || !is_subclass_of($installer_class, 'Et\Application_Modules_Module_Installer', true)){
			throw new Application_Modules_Exception(
				"Installer class '{$installer_class}' for module {$this->getID()} ({$this->getName()}) not found or is not subclass of Et\\Application_Modules_Module_Installer",
				Application_Modules_Exception::CODE_INVALID_CONFIG
			);
		}
		$this->_installer = new $installer_class($this);

		return $this->_installer;
	}

	/**
	 * @return array
	 */
	public function getFactoryClassMap() {
		return $this->factory_class_map;
	}

	/**
	 * @return string
	 */
	public function getModuleName(){
		return $this->getName();
	}

	/**
	 * @return string
	 */
	public function getModuleID() {
		return $this->getID();
	}

	/**
	 * @return array
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 * Array like signal_name => signal_handler_module_method
	 *
	 * @return callable[]
	 */
	public function getSignalHandlers() {
		return $this->signal_handlers;
	}

	/**
	 * @return string
	 */
	public function getVendor() {
		return $this->vendor;
	}

	/**
	 * @return int
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * @return System_File
	 */
	public function getMainModelFile(){
		return new System_File((string)$this->getModuleDirectory() . "Module.php");
	}

	/**
	 * @return bool
	 */
	public function getModuleExists(){
		return class_exists("EtM\\{$this->getModuleID()}\\Main");
	}

	/**
	 * @return array
	 */
	public function getLocalizedNames() {
		return $this->localized_names;
	}

	/**
	 * @param null|string|Locales_Locale $locale
	 * @return string
	 */
	public function getLocalizedName($locale = null){
		$locale = Locales::getLocale($locale);
		return isset($this->localized_names[(string)$locale])
				? $this->localized_names[(string)$locale]
				: $this->getModuleName();
	}

	/**
	 * @param array $tags
	 * @return bool
	 */
	function hasAnyTag(array $tags){
		foreach($tags as $tag){
			if(isset($this->tags[$tag])){
				return true;
			}
		}
		return false;
	}

	/**
	 * @param array $tags
	 * @return bool
	 */
	function hasAllTags(array $tags){
		foreach($tags as $tag){
			if(!isset($this->tags[$tag])){
				return false;
			}
		}
		return true;
	}

	/**
	 * @param array $tags
	 * @return bool
	 */
	function hasNotAnyTag(array $tags){
		foreach($tags as $tag){
			if(!isset($this->tags[$tag])){
				return true;
			}
		}
		return false;
	}

	/**
	 * @param array $tags
	 * @return bool
	 */
	function hasNotAllTags(array $tags){
		foreach($tags as $tag){
			if(isset($this->tags[$tag])){
				return false;
			}
		}
		return true;
	}
}