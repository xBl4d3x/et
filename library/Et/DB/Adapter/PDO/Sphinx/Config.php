<?php
namespace Et;
class DB_Adapter_PDO_Sphinx_Config extends DB_Adapter_PDO_Config {

	/**
	 * @var string
	 */
	protected $_type = "PDO_Sphinx";

	/**
	 * PDO connection string
	 * @link http://cz2.php.net/manual/en/pdo.construct.php
	 *			self::DEF
	 * @var string
	 */
	protected $dsn = "mysql:host=127.0.0.1;port=9306;charset=utf8";
}