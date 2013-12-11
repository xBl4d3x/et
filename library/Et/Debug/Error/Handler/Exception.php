<?php
namespace Et;
et_require('Exception');
class Debug_Error_Handler_Exception extends Exception {

	const CODE_INVALID_ERROR_PAGES_DIR = 10;


	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_ERROR_PAGES_DIR => "Invalid error pages directory",
	);

}