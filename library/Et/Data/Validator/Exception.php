<?php
namespace Et;
class Data_Validator_Exception extends Exception {

	const CODE_INVALID_ERROR_CODE = 10;
	const CODE_INVALID_VALIDATOR_TYPE = 20;
	const CODE_INVALID_VALIDATOR_DEFINITION = 30;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_ERROR_CODE => "Invalid error code",
		self::CODE_INVALID_VALIDATOR_TYPE => "Invalid validator type",
		self::CODE_INVALID_VALIDATOR_DEFINITION => "Invalid validator definition"
	);



}