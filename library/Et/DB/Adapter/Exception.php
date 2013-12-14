<?php
namespace Et;
/**
 * DB adapter exception
 */
class DB_Adapter_Exception extends Exception {

	const CODE_INVALID_CONFIG = 10;
	const CODE_MISSING_EXTENSION = 20;
	const CODE_CONNECTION_FAILED = 30;
	const CODE_DISCONNECTION_FAILED = 40;
	const CODE_QUERY_FAILED = 50;
	const CODE_ADAPTER_FAILURE = 60;
	const CODE_NOT_SUPPORTED = 70;
	const CODE_NOT_CONNECTED = 80;
	const CODE_INVALID_ARGUMENT = 90;
	const CODE_QUOTE_FAILED = 100;
	const CODE_BINDING_FAILED = 110;
	const CODE_INVALID_RESULT = 120;
	const CODE_OPERATION_FAILED = 130;
	const CODE_INVALID_COLUMN = 140;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_CONFIG => "Invalid adapter config",
		self::CODE_MISSING_EXTENSION => "Missing PHP extension",
		self::CODE_CONNECTION_FAILED => "Connection failed",
		self::CODE_DISCONNECTION_FAILED => "Disconnection failed",
		self::CODE_QUERY_FAILED => "Query failed",
		self::CODE_ADAPTER_FAILURE => "Adapter failure",
		self::CODE_NOT_SUPPORTED => "Not supported",
		self::CODE_NOT_CONNECTED => "Not connected",
		self::CODE_INVALID_ARGUMENT => "Invalid argument",
		self::CODE_QUOTE_FAILED => "Quote failed",
		self::CODE_BINDING_FAILED => "Data binding failed",
		self::CODE_INVALID_RESULT => "Invalid result",
		self::CODE_OPERATION_FAILED => "Operation failed",
		self::CODE_INVALID_COLUMN => "Invalid column"
	);

	/**
	 * @var int
	 */
	protected $sql_error_code = 0;

	/**
	 * @var string
	 */
	protected $sql_error_message = "";

	/**
	 * @param int $sql_error_code
	 *
	 * @return DB_Adapter_Exception
	 */
	public function setSqlErrorCode($sql_error_code) {
		$this->sql_error_code = (int)$sql_error_code;
	}

	/**
	 * @return int
	 */
	public function getSqlErrorCode() {
		return $this->sql_error_code;
	}

	/**
	 * @param string $sql_error_message
	 *
	 * @return DB_Adapter_Exception
	 */
	public function setSqlErrorMessage($sql_error_message) {
		$this->sql_error_message = (string)$sql_error_message;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSqlErrorMessage() {
		return $this->sql_error_message;
	}


}