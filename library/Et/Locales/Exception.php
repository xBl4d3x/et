<?php
namespace Et;
et_require("Exception");
class Locales_Exception extends Exception {

	const CODE_INVALID_LOCALE = 10;
	const CODE_FORMATTER_FAILURE = 20;
	const CODE_INVALID_LANGUAGE = 30;
	const CODE_INVALID_DATE_TIME = 40;
	const CODE_INVALID_TIME_ZONE = 50;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_LOCALE => "Invalid locale",
		self::CODE_FORMATTER_FAILURE => "Formatter failure",
		self::CODE_INVALID_LANGUAGE => "Invalid language",
		self::CODE_INVALID_DATE_TIME => "Invalid date/time",
		self::CODE_INVALID_TIME_ZONE => "Invalid timezone"
	);
}