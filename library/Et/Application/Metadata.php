<?php
namespace Et;
class Application_Metadata extends System_Components_Component {

	/**
	 * @var System_File
	 */
	protected $_metadata_file;

	/**
	 * @var System_Dir
	 */
	protected $_application_directory;

	/**
	 * @var string
	 */
	protected $_application_URL;

	/**
	 * @var string
	 */
	protected $_application_URI;

	/**
	 * @var Application_Config
	 */
	protected $_config;

	/**
	 * @var Application_Installer
	 */
	protected $_installer;


	/**
	 * @var int
	 */
	protected $version;

	/**
	 * @var array
	 */
	protected $localized_names = array();

	/**
	 * @var callable[]
	 */
	protected $signal_handlers = array();

	/**
	 * @var array
	 */
	protected $factory_class_map = array();


	/**
	 * @param string $application_ID
	 */
	function __construct($application_ID){
		Application::checkApplicationIDFormat($application_ID);
		$this->name = $application_ID;
		$this->loadMetadata();
		parent::__construct($application_ID, $this->getName(), $this->getDescription());
	}

	function reload(){
		$this->loadMetadata();
		$this->changed = true;
	}


	/**
	 * @throws Application_Exception
	 */
	protected function loadMetadata(){
		try {

			$metadata = new Data_Array($this->getMetadataFile()->includeArrayContent());
			
			$this->name = trim($metadata->getString("name"));
			$this->description = trim($metadata->getString("description"));
			$this->version = $metadata->getInt("version");
			$this->signal_handlers = $metadata->getRawValue("signal_handlers", array());
			$this->factory_class_map = $metadata->getRawValue("factory_class_map", array());

			$assert = $this->assert();
			$assert->isNotEmpty($this->name, "application name not specified");
			$assert->isArray($this->signal_handlers, "signal handlers must be an array");
			$assert->isArray($this->factory_class_map, "factory class map must be an array");
			$assert->isArray($this->factory_class_map, "factory class map must be an array");

		} catch(Exception $e){

			throw new Application_Exception(
				"Failed to load application '{$this->getApplicationID()}' metadata - {$e->getMessage()}",
				Application_Exception::CODE_INVALID_METADATA,
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
			$this->_metadata_file = new System_File((string)$this->getApplicationDirectory() . "metadata.php");
		}
		return $this->_metadata_file;
	}

	/**
	 * @return string
	 */
	public function getApplicationURI() {
		if(!$this->_application_URI){
			return ET_BASE_URI . "applications/{$this->getApplicationID()}/";
		}
		return $this->_application_URI;
	}

	/**
	 * @return string
	 */
	public function getApplicationURL() {
		if(!$this->_application_URI){
			return ET_BASE_URL . "applications/{$this->getApplicationID()}/";
		}
		return $this->_application_URL;
	}

	/**
	 * @return System_Dir
	 */
	public function getApplicationDirectory() {
		if(!$this->_application_directory){
			$this->_application_directory = System::getDir(ET_APPLICATIONS_PATH . $this->getApplicationID() . "/");
		}
		return $this->_application_directory;
	}

	/**
	 * @return bool
	 */
	function isOutdated(){
		return $this->isInstalled() && $this->getVersion() != $this->getInstalledVersion();
	}

	/**
	 * @return Application_Config
	 * @throws Application_Exception
	 */
	public function getConfig(){
		if($this->_config){
			return $this->_config;
		}

		$config_class = "EtApp\\{$this->getApplicationID()}\\Config";
		if(!class_exists($config_class) || !is_subclass_of($config_class, 'Et\Application_Config', true)){
			throw new Application_Exception(
				"Configuration class '{$config_class}' for application {$this->getID()} ({$this->getName()}) not found or is not subclass of Et\\Application_Config",
				Application_Exception::CODE_INVALID_CONFIG
			);
		}
		$this->_config = new $config_class($this);

		return $this->_config;
	}

	/**
	 * @return Application_Installer
	 * @throws Application_Exception
	 */
	public function getInstaller(){
		if($this->_installer){
			return $this->_installer;
		}

		$installer_class = "EtApp\\{$this->getApplicationID()}\\Installer";
		if(!class_exists($installer_class) || !is_subclass_of($installer_class, 'Et\Application_Installer', true)){
			throw new Application_Exception(
				"Installer class '{$installer_class}' for application {$this->getID()} ({$this->getName()}) not found or is not subclass of Et\\Application_Installer",
				Application_Exception::CODE_INVALID_CONFIG
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
	public function getApplicationName(){
		return $this->getName();
	}

	/**
	 * @return string
	 */
	public function getApplicationID() {
		return $this->getID();
	}

	/**
	 * Array like signal_name => signal_handler_application_method
	 *
	 * @return callable[]
	 */
	public function getSignalHandlers() {
		return $this->signal_handlers;
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
	public function getApplicationFile(){
		return new System_File((string)$this->getApplicationDirectory() . "Application.php");
	}

	/**
	 * @return bool
	 */
	public function getApplicationExists(){
		return class_exists("Et\\{$this->getApplicationID()}\\Main");
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
			: $this->getApplicationName();
	}
}