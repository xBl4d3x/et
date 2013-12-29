<?php
namespace Et;
class Entity_Key_Generator_Exception extends Exception {

	const CODE_FAILED_TO_GENERATE_ID = 10;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_FAILED_TO_GENERATE_ID => "Failed to generate ID"
	);


}