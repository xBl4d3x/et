<?php
namespace Et;

class System_Components_Backend_Default_Config extends System_Components_Backend_Config_Abstract {

	/**
	 * @var string
	 */
	protected static $_adapter_type = "Default";

	/**
	 * @var string
	 */
	protected $storage_dir;
	protected static $__storage_dir__definition = array(
		self::DEF_TYPE => self::TYPE_STRING,
		self::DEF_NAME => "Storage directory"
	);

	/**
	 * @var System_Dir
	 */
	protected $_storage_dir;

	protected function initDefaultValues(){
		parent::initDefaultValues();
		$this->storage_dir = ET_SYSTEM_DATA_PATH . "components/";
	}


	/**
	 * @param bool $get_as_string [optional]
	 * @return System_Dir|string
	 */
	function getStorageDir($get_as_string = false){
		if($get_as_string){
			return $this->storage_dir;
		}

		if(!$this->_storage_dir || (string)$this->_storage_dir != $this->storage_dir){
			$this->_storage_dir = new System_Dir($this->storage_dir);
			$this->storage_dir = (string)$this->_storage_dir;
		}

		return $this->_storage_dir;
	}
}