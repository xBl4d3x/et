<?php
namespace Et;
class DB_Adapter_MySQL_Config extends DB_Adapter_Config_Abstract {

	/**
	 * @var string
	 */
	protected $_type = "MySQL";

	/**
	 * @var string
	 */
	protected $charset = "utf8";

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
	public function getCharset() {
		return $this->charset;
	}



}