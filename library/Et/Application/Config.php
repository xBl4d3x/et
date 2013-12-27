<?php
namespace Et;
abstract class Application_Config extends Config {

	/**
	 * @var Application_Metadata
	 */
	protected $_application_metadata;

	/**
	 * @var System_File
	 */
	protected $_config_file;

	/**
	 * Definition of config properties
	 *
	 * @var array
	 */
	protected static $_definition = array(
		"base_URLs" => [
			self::DEF_TYPE => self::TYPE_ARRAY,
			self::DEF_ARRAY_VALUE_TYPE => self::TYPE_STRING
		]
	);


	/**
	 * @var array
	 */
	protected $base_URLs;

	/**
	 * @var array
	 */
	protected $base_SSL_URLs = array();

	/**
	 * @param Application_Metadata $application_metadata
	 */
	function __construct(Application_Metadata $application_metadata){
		$this->_application_metadata = $application_metadata;
		$this->_application_name = $application_metadata->getApplicationID();
		$options = $this->loadOptionsFromConfigFile();
		parent::__construct($options);
	}

	/**
	 * @return array
	 */
	protected function loadOptionsFromConfigFile(){
		$env = System::getEnvironmentName();
		$application_dir = $this->_application_metadata->getApplicationDirectory();
		$fp = $application_dir . "config/config_{$env}.php";
		if(!file_exists($fp)){
			$fp = $application_dir . "config/config.php";
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
	 * @return Application_Metadata
	 */
	public function getApplicationMetadata() {
		return $this->_application_metadata;
	}

	/**
	 * @return string
	 */
	public function getApplicationName() {
		return $this->_application_metadata->getApplicationName();
	}


}