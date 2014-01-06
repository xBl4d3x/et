<?php
namespace Et;
/**
 * MVC view exception
 */
class MVC_View_Exception extends Exception {

	const CODE_INVALID_BASE_DIR = 10;
	const CODE_INVALID_VIEW_NAME = 20;
	const CODE_RENDERING_FAILED = 30;
	const CODE_INVALID_FORM_NAME = 40;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_BASE_DIR => "Invalid views base directory",
		self::CODE_INVALID_VIEW_NAME => "Invalid view name",
		self::CODE_RENDERING_FAILED => "View rendering failed",
		self::CODE_INVALID_FORM_NAME => "Invalid form name"
	);
}