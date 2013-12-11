<?php
namespace Et;
et_require('Exception');
class Application_Exception extends Exception {

	const CODE_INVALID_CONFIG_FILE = 10;
	const CODE_INVALID_SECTION_DATA = 20;
	const CODE_INVALID_ENVIRONMENT = 30;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_CONFIG_FILE => "Invalid config file",
		self::CODE_INVALID_SECTION_DATA => "Invalid config section data",
		self::CODE_INVALID_ENVIRONMENT => "Invalid environment"
	);
}
