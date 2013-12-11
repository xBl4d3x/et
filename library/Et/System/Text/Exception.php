<?php
namespace Et;
et_require("Exception");

class System_Text_Exception extends Exception {

	const CODE_MISSING_EXTENSION = 10;
	const CODE_INVALID_ENCODING = 20;
	const CODE_INVALID_IDENTIFIER = 30;
	const CODE_IDENTIFIER_GENERATION_FAILED = 40;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_MISSING_EXTENSION => "Missing extension",
		self::CODE_INVALID_ENCODING => "Invalid encoding",
		self::CODE_INVALID_IDENTIFIER => "Invalid identifier",
		self::CODE_IDENTIFIER_GENERATION_FAILED => "Identifier generation failed"
	);
}