<?php
namespace Et;
et_require('Exception');
class Factory_Exception extends Exception {

	const CODE_CLASS_NOT_EXISTS = 10;
	const CODE_WRONG_CLASS_PARENT = 20;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_CLASS_NOT_EXISTS => "Class not exists",
		self::CODE_WRONG_CLASS_PARENT => "Wrong class parent"
	);
}