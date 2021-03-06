<?php
namespace Et;
class DB_Adapter_Sphinx_Config extends DB_Adapter_MySQL_Config {

	/**
	 * @var string
	 */
	protected $_type = "Sphinx";

	/**
	 * PDO connection string
	 * @link http://cz2.php.net/manual/en/pdo.construct.php
	 *			self::DEF
	 * @var string
	 */
	protected $dsn = "mysql:host=127.0.0.1;port=9306;charset=utf8";
}