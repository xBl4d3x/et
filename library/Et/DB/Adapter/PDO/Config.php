<?php
namespace Et;
abstract class DB_Adapter_PDO_Config extends DB_Adapter_Config_Abstract {

	/**
	 * Definition of config properties
	 *
	 * @var array
	 */
	protected static $_definition = array(
		"dsn" => [
			self::DEF_TYPE => self::TYPE_STRING,
			self::DEF_NAME => "PDO DSN"
		],
		"connection_timeout" => [
			self::DEF_MIN_VALUE => 0
		],
		"driver_options" => [
			self::DEF_TYPE => self::TYPE_ARRAY,
			self::DEF_NAME => "PDO driver options"
		]
	);

	/**
	 * PDO connection string
	 * @link http://cz2.php.net/manual/en/pdo.construct.php
	 *			self::DEF
	 * @var string
	 */
	protected $dsn;

	/**
	 * @var string
	 */
	protected $username = "";
	/**
	 * @var string
	 */
	protected $password = "";

	/**
	 * Timeout in seconds to connect to server
	 *
	 * @var int
	 */
	protected $connection_timeout = 5;

	/**
	 * PDO driver options
	 *
	 * @var array
	 */
	protected $driver_options = array();


	/**
	 * @return string
	 */
	public function getDSN() {
		return $this->dsn;
	}

	/**
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * @return int
	 */
	public function getConnectionTimeout() {
		return $this->connection_timeout;
	}

	/**
	 * @param bool $convert_constants [optional]
	 * @return array
	 */
	function getDriverOptions($convert_constants = true){

		$driver_options = array();

		foreach($this->driver_options as $option => $value){
			if($convert_constants && preg_match('~PDO::~', $option)){
				$option = constant("\\".ltrim($option, "\\"));
			}

			if($convert_constants && preg_match('~PDO::~', $value)){
				$value = constant("\\".ltrim($value, "\\"));
			}
			$driver_options[$option] = $value;
		}

		return $driver_options;

	}
}