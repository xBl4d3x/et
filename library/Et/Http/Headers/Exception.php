<?php
namespace Et;
class Http_Headers_Exception extends Exception {

	const CODE_HEADERS_SENT = 10;
	const CODE_FAILED_TO_SEND_HEADER = 20;
	const CODE_INVALID_RESPONSE_CODE = 30;
	const CODE_INVALID_HEADER = 40;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_HEADERS_SENT => "Headers already sent",
		self::CODE_FAILED_TO_SEND_HEADER => "Failed to send header",
		self::CODE_INVALID_RESPONSE_CODE => "Invalid response code",
		self::CODE_INVALID_HEADER => "Invalid header"
	);
}