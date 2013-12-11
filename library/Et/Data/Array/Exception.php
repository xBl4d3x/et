<?php
namespace Et;
et_require("Exception");
class Data_Array_Exception extends Exception {

	const CODE_INVALID_PATH = 10;
	const CODE_DIRECT_ACCESS_FORBIDDEN = 20;
	const CODE_INVALID_VALUE = 30;
	const CODE_INVALID_FILE = 40;
	const CODE_FAILED_TO_LOAD_DATA = 50;
	const CODE_FAILED_TO_STORE_DATA = 60;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_PATH => "Invalid path",
		self::CODE_DIRECT_ACCESS_FORBIDDEN => "Direct access forbidden",
		self::CODE_INVALID_VALUE => "Invalid value",
		self::CODE_INVALID_FILE => "Invalid file",
		self::CODE_FAILED_TO_LOAD_DATA => "Failed to load array data",
		self::CODE_FAILED_TO_STORE_DATA => "Failed to store data"
	);
}