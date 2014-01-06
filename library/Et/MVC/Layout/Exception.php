<?php
namespace Et;
class MVC_Layout_Exception extends Exception {

	const CODE_INVALID_LAYOUT = 10;
	const CODE_INVALID_LAYOUTS_DIRECTORY = 20;
	const CODE_RENDERING_FAILED = 30;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_LAYOUT => "Invalid layout",
		self::CODE_INVALID_LAYOUTS_DIRECTORY => "Invalid layouts directory",
		self::CODE_RENDERING_FAILED => "Layout rendering failed",
	);
}