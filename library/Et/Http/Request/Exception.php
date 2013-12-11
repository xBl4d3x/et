<?php
namespace Et;
class Http_Request_Exception extends Exception {

	const DATA_ACCESS_PERMISSION_DENIED = 10;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::DATA_ACCESS_PERMISSION_DENIED => "Data access permission denied"
	);

}