<?php
namespace Et;
class DB_Adapter_SQLite_Config extends DB_Adapter_Config_Abstract {

	/**
	 * @var string
	 */
	protected $_type = "SQLite";

	/**
	 * PDO connection string
	 * @link http://cz2.php.net/manual/en/pdo.construct.php
	 * @link http://cz1.php.net/manual/en/ref.pdo-sqlite.connection.php
	 *
	 * @var string
	 */
	protected $dsn = "sqlite:/tmp/db.sqlite";

}