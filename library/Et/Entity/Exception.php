<?php
namespace Et;
class Entity_Exception extends Exception {

	const CODE_INVALID_ENTITY = 10;
	const CODE_INVALID_KEY = 20;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_ENTITY => "Invalid entity",
		self::CODE_INVALID_KEY => "Invalid key"
	);


}