<?php
namespace Et;
class Session_Exception extends Exception {

	const CODE_INITIALIZATION_FAILED = 10;
	const CODE_SESSION_START_FAILED = 20;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INITIALIZATION_FAILED => "Initialization failed",
		self::CODE_SESSION_START_FAILED => "Session start failed"
	);



}