<?php
namespace Et;
et_require("Exception");
class ClassLoader_Exception extends Exception {

	const CODE_INVALID_LOADER_CLASS = 10;
	const CODE_INVALID_CLASS_NAME_PREFIX = 20;
	const CODE_NOT_EXISTS = 30;
	const CODE_NOT_SUBCLASS = 40;
	const CODE_MISSING_LOADER_NAME = 50;


	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_LOADER_CLASS => "Invalid loader class",
		self::CODE_INVALID_CLASS_NAME_PREFIX => "Invalid class name prefix",
		self::CODE_NOT_EXISTS => "Class not exists",
		self::CODE_NOT_SUBCLASS => "Class not valid subclass",
		self::CODE_MISSING_LOADER_NAME => "Missing loader name"
	);
}
