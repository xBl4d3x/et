<?php
namespace Et;
et_require('Exception');
class Object_Exception extends Exception {

	const CODE_PROTECTED_PROPERTY_ACCESS = 10;
	const CODE_UNKNOWN_PROPERTY_ACCESS = 20;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_PROTECTED_PROPERTY_ACCESS => "Protected property access",
		self::CODE_UNKNOWN_PROPERTY_ACCESS => "Unknown property access",
	);



}