<?php
namespace Et;
et_require("Exception");
class Data_Array_Source_Exception extends Exception {

	const CODE_INVALID_SOURCE = 10;
	const CODE_DATA = 20;
	const CODE_LOAD_FAILED = 30;
	const CODE_STORE_FAILED = 40;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_SOURCE => "Invalid array source",
		self::CODE_DATA => "Invalid array source data",
		self::CODE_LOAD_FAILED => "Failed to load data",
		self::CODE_STORE_FAILED => "Failed to store data"
	);
}