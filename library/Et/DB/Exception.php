<?php
namespace Et;
/**
 * DB exceptions
 */
class DB_Exception extends Exception {

	const CODE_INVALID_CONNECTION_NAME = 10;
	const CODE_INVALID_CONNECTION_CONFIG = 20;
	const CODE_INVALID_COLUMN_NAME = 30;
	const CODE_INVALID_COLUMN_VALUE = 40;
	const CODE_INVALID_KEY = 50;
	const CODE_KEY_NOT_EXIST = 60;
	const CODE_KEY_ALREADY_EXISTS = 70;
	const CODE_INVALID_TABLE_NAME = 80;
	const CODE_INVALID_COLUMN_DEFINITION = 90;
	const CODE_NOT_SUPPORTED = 100;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_CONNECTION_NAME => "Invalid connection name",
		self::CODE_INVALID_CONNECTION_CONFIG => "Invalid connection config",
		self::CODE_INVALID_COLUMN_NAME => "Invalid column name",
		self::CODE_INVALID_COLUMN_VALUE => "Invalid column value",
		self::CODE_INVALID_KEY => "Invalid key",
		self::CODE_KEY_NOT_EXIST => "Key not exists",
		self::CODE_KEY_ALREADY_EXISTS => "Key already exists",
		self::CODE_INVALID_TABLE_NAME => "Invalid table name",
		self::CODE_INVALID_COLUMN_DEFINITION => "Invalid column definition",
		self::CODE_NOT_SUPPORTED => "Not supported"
	);
}