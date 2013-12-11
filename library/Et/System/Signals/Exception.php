<?php
namespace Et;
et_require("Exception");
class System_Signals_Exception extends Exception {

	const CODE_INVALID_NAME = 10;
	const CODE_PUBLISH_FAILED = 20;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_NAME => "Invalid name",
		self::CODE_PUBLISH_FAILED => "Publish failed"
	);
}