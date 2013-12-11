<?php
namespace Et;
class Http_Request_URL_Exception extends Exception {

	const CODE_INVALID_URL = 10;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_URL => "Invalid URL",
	);
}
