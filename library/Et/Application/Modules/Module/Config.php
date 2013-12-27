<?php
namespace Et;
abstract class Application_Modules_Module_Config extends Config {

	/**
	 * @var string
	 */
	protected $_module_name;

	/**
	 * @var Application_Modules_Module_Metadata
	 */
	protected $_module_metadata;

	/**
	 * @var System_File
	 */
	protected $_config_file;

	/**
	 * @param Application_Modules_Module_Metadata $module_metadata
	 */
	function __construct(Application_Modules_Module_Metadata $module_metadata){
		$this->_module_metadata = $module_metadata;
		$this->_module_name = $module_metadata->getModuleID();
		$options = $this->loadOptionsFromConfigFile();
		parent::__construct($options);
	}

	/**
	 * @return array
	 */
	protected function loadOptionsFromConfigFile(){
		$env = Application::getEnvironmentName();
		$module_dir = $this->_module_metadata->getModuleDirectory();
		$fp = $module_dir . "config/config_{$env}.php";
		if(!file_exists($fp)){
			$fp = $module_dir . "config/config.php";
			if(!file_exists($fp)){
				return array();
			}
		}
		$this->_config_file = System::getFile($fp);
		return $this->_config_file->includeArrayContent();
	}

	/**
	 * @return System_File|null
	 */
	public function getConfigFile() {
		return $this->_config_file;
	}

	/**
	 * @return Application_Modules_Module_Metadata
	 */
	public function getModuleMetadata() {
		return $this->_module_metadata;
	}

	/**
	 * @return string
	 */
	public function getModuleName() {
		return $this->_module_name;
	}


}