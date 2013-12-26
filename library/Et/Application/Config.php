<?php
namespace Et;
et_require("Data_Array");
class Application_Config extends Object {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var Data_Array
	 */
	protected $config_sections;

	/**
	 * @param string $name
	 * @param \Et\Data_Array $config_sections [optional]
	 */
	function __construct($name, Data_Array $config_sections = null){

		$this->assert()->isVariableName($name);
		$this->name = $name;
		if(!$config_sections){
			$config_sections = $this->getConfigSectionsFromFile();
		}

		$this->config_sections = $config_sections;

	}

	/**
	 * @return string
	 */
	function getName(){
		return $this->name;
	}

	/**
	 * @return string
	 */
	function __toString(){
		return $this->getName();
	}


	/**
	 * @throws Application_Exception
	 * @return \Et\Data_Array
	 */
	protected function getConfigSectionsFromFile(){
		$fp = ET_CONFIGS_PATH . $this->getName() . ".php";
		try {

			$config_data = new Data_Array();
			et_require("Data_Array_Source_File");
			$config_source = new Data_Array_Source_File($fp, Data_Array_Source_File::FORMAT_PHP);
			$config_data->loadFromSource($config_source);
			return $config_data;

		} catch(\Exception $e){

			throw new Application_Exception(
				"Failed to load environment config sections from '{$fp}' - {$e->getMessage()}",
				Application_Exception::CODE_INVALID_CONFIG_FILE,
				null,
				$e
			);

		}
	}

	/**
	 * @return Data_Array
	 */
	public function getConfigSections() {
		return $this->config_sections;
	}


	/**
	 * @param string $section_path
	 * @param array $default_value [optional]
	 *
	 * @return array
	 * @throws Application_Exception
	 */
	function getSectionData($section_path, array $default_value = array()){
		$section_path = (string)$section_path;
		$section = $this->config_sections->getRawValue($section_path, $default_value);
		if(!is_array($section)){
			throw new Application_Exception(
				"Application config section '{$section_path}' should be array, not " .gettype($section),
				Application_Exception::CODE_INVALID_SECTION_DATA,
				array(
				     "section data" => $section
				)
			);
		}
		return $section;
	}

}
