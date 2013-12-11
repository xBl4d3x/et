<?php
namespace Et;
et_require('Exception');
class Config_Exception extends Exception {

	const CODE_INVALID_DEFINITION = 10;
	const CODE_INVALID_PROPERTY = 20;
	const CODE_INVALID_ERROR_CODE = 30;
	const CODE_INVALID_SECTION = 40;


	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_DEFINITION => "Invalid config definition",
		self::CODE_INVALID_PROPERTY => "Invalid config property",
		self::CODE_INVALID_ERROR_CODE => "Invalid error code",
		self::CODE_INVALID_SECTION => "Invalid config section"
	);
}