<?php
namespace Et;
class System_Components_Backend_Default_Config extends System_Components_Backend_Config_Abstract {

	/**
	 * @var string
	 */
	protected $_type = "Default";

	/**
	 * @var bool
	 */
	protected $enable_write_lock = true;

	/**
	 * @var string
	 */
	protected $storage_dir = "";

	/**
	 * @var System_Dir
	 */
	protected $_storage_dir;

	/**
	 * @return array
	 */
	public static function getDefaultValues(){
		$values = parent::getDefaultValues();
		$values["storage_dir"] =  ET_SYSTEM_DATA_PATH . "components/";
		return $values;
	}

	/**
	 * @return boolean
	 */
	public function getEnableWriteLock() {
		return $this->enable_write_lock;
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
			$this->_storage_dir = System::getDir($this->storage_dir);
			$this->storage_dir = (string)$this->_storage_dir;
		}

		return $this->_storage_dir;
	}
}