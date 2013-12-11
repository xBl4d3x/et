<?php
namespace Et;

class System_Components_Backend_Default extends System_Components_Backend_Abstract {

	/**
	 * @var string
	 */
	protected static $_adapter_type = "Default";

	/**
	 * @var System_Components_Backend_Default_Config
	 */
	protected $config;

	/**
	 * @var System_Dir
	 */
	protected $storage_dir;

	/**
	 * @param System_Components_Backend_Default_Config $config
	 */
	function __construct(System_Components_Backend_Default_Config $config){
		parent::__construct($config);
		$this->initStorageDir();
	}

	protected function initStorageDir(){
		$this->storage_dir = $this->config->getStorageDir();
		if(!$this->storage_dir->exists()){
			$this->storage_dir->create();
		}
		$this->storage_dir->checkIsWritable();
	}

	/**
	 * @return System_Components_Backend_Default_Config
	 */
	function getConfig(){
		return $this->config;
	}

	/**
	 * @return System_Dir
	 */
	public function getStorageDir() {
		return $this->storage_dir;
	}

	/**
	 * @return array
	 */
	function getComponentsTypes() {
		$types = $this->storage_dir->listFiles(function(System_File $file){
			return (bool)preg_match('~^\w+\.components$~', $file->getName());
		});
		return array_keys($types);
	}

	/**
	 * @param string $component_type
	 * @return System_File
	 */
	function getComponentsFile($component_type){
		$this->assert()->isVariableName($component_type);
		return new System_File((string)$this->storage_dir . $component_type . ".components");
	}

	/**
	 * @param string $components_type
	 * @return System_Components_List|bool
	 */
	function loadComponents($components_type) {
		$file = $this->getComponentsFile($components_type);

		try {
			return $file->getUnserializedContent('Et\System_Components_List');
		} catch(Exception $e){
			return false;
		}
	}

	/**
	 * @param System_Components_List $components
	 * @throws System_Components_Exception
	 */
	function storeComponents(System_Components_List $components) {
		$file = $this->getComponentsFile($components->getComponentsType());
		$file->writeContent(serialize($components), true);
	}

	/**
	 * @param string $components_type
	 * @return bool
	 */
	function removeComponents($components_type) {
		$file = $this->getComponentsFile($components_type);
		if($file->exists()){
			return false;
		}
		$file->delete();
		return true;
	}

	/**
	 * @param string $components_type
	 * @return bool
	 */
	function getComponentsExist($components_type) {
		return $this->getComponentsFile($components_type)->exists();
	}
}