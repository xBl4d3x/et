<?php
namespace Et;
et_require('Exception');
/**
 * Debug exception
 */
class Debug_Exception extends Exception {

	const CODE_INVALID_CLASS_NAME = 10;
	const CODE_INVALID_PHP_ERROR = 20;
	const CODE_INVALID_ARGUMENT = 30;
	const CODE_INVALID_PROPERTY = 40;
	const CODE_INVALID_METHOD = 50;


	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_CLASS_NAME => "Invalid class name",
		self::CODE_INVALID_PHP_ERROR => "Invalid PHP error",
		self::CODE_INVALID_ARGUMENT => "Invalid argument",
		self::CODE_INVALID_PROPERTY => "Invalid property",
		self::CODE_INVALID_METHOD => "Invalid method"
	);



}