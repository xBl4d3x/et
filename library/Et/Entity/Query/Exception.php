<?php
namespace Et;
class Entity_Query_Exception extends Exception {

	const CODE_INVALID_EXPRESSION = 10;
	const CODE_INVALID_PROPERTY = 20;
	const CODE_INVALID_OPERATOR = 30;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_EXPRESSION => "Invalid expression",
		self::CODE_INVALID_PROPERTY => "Invalid property",
		self::CODE_INVALID_OPERATOR => "Invalid operator",
	);


}