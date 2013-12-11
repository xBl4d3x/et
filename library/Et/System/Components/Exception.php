<?php
namespace Et;
et_require("Exception");
class System_Components_Exception extends Exception {

	const CODE_BACKEND_FAILURE = 10;
	const CODE_INVALID_DATA = 20;
	const CODE_INVALID_TYPE = 30;
	const CODE_INVALID_NAME = 40;
	const CODE_NOT_INSTALLED = 50;
	const CODE_INVALID_VERSION = 60;
	const CODE_INVALID_ARGUMENT = 70;
	const CODE_LOAD_FAILED = 80;
	const CODE_STORE_FAILED = 90;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_BACKEND_FAILURE => "Backend failure",
		self::CODE_INVALID_DATA => "Invalid data",
		self::CODE_INVALID_TYPE => "Invalid type",
		self::CODE_INVALID_NAME => "Invalid name",
		self::CODE_NOT_INSTALLED => "Not installed",
		self::CODE_INVALID_VERSION => "Invalid version",
		self::CODE_INVALID_ARGUMENT => "Invalid argument",
		self::CODE_LOAD_FAILED => "Components load failed",
		self::CODE_STORE_FAILED => "Components store failed"
	);
}