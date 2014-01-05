<?php
namespace Et;
class Entity_Adapter_MySQL_Config extends Entity_Adapter_Config_Abstract {

	/**
	 * @var string
	 */
	protected $_type = "MySQL";

	/**
	 * @var string
	 */
	protected $database_connection_name;

	/**
	 * PDO connection string
	 * @link http://cz2.php.net/manual/en/pdo.construct.php
	 *			self::DEF
	 * @var string
	 */
	protected $dsn = "mysql:host=127.0.0.1;port=3306;dbname=mydb;charset=utf8";

	/**
	 * @return string
	 */
	public function getDatabaseconnectionname() {
		return $this->database_connection_name;
	}



}