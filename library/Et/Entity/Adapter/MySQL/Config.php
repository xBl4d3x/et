<?php
namespace Et;
class Entity_Adapter_MySQL_Config extends Entity_Adapter_Config_Abstract {

	/**
	 * @var string
	 */
	protected $_type = "MySQL";

	/**
	 * Definition of config properties
	 *
	 * @var array
	 */
	protected static $_definition = array(
		"database_connection_name" => array(
			self::DEF_TYPE => self::TYPE_STRING
		)
	);

	/**
	 * @var string
	 */
	protected $database_connection_name;

	/**
	 * @return string
	 */
	public function getDatabaseConnectionName() {
		return $this->database_connection_name;
	}



}