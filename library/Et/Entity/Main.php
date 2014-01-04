<?php
namespace Et;

class Entity_Main extends Entity_Abstract {

	/**
	 * @return bool
	 */
	public static function isMainEntity(){
		return true;
	}


	/**
	 * @return Entity_Definition_Main
	 */
	public static function getEntityDefinition(){
		return parent::getEntityDefinition();
	}

	/**
	 * @return bool
	 * @throws \Et\Entity_Exception
	 */
	public function save(){
		$this->checkEntity();

		if(!$this->validate(true)){
			return false;
		}

		$properties = static::getEntityDefinition()->getProperties();
		$db = static::getDB();



		return $this->_save();
	}
}