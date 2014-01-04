<?php
namespace Et;
class Entity_Exception extends Exception {

	const CODE_INVALID_ENTITY = 10;
	const CODE_INVALID_KEY = 20;
	const CODE_INVALID_DEFINITION = 30;
	const CODE_INVALID_PROPERTY = 40;
	const CODE_INVALID_ERROR_CODE = 50;
	const CODE_INVALID_QUERY = 60;
	const CODE_NOT_INSTALLED = 70;
	const CODE_NOT_ENABLED = 80;
	const CODE_OUTDATED = 90;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_ENTITY => "Invalid entity",
		self::CODE_INVALID_KEY => "Invalid key",
		self::CODE_INVALID_DEFINITION => "Invalid definition",
		self::CODE_INVALID_PROPERTY => "Invalid property",
		self::CODE_INVALID_ERROR_CODE => "Invalid error code",
		self::CODE_INVALID_QUERY => "Invalid query",
		self::CODE_NOT_INSTALLED => "Entity not installed",
		self::CODE_NOT_ENABLED => "Entity not enabled",
		self::CODE_OUTDATED => "Entity outdated"
	);


}