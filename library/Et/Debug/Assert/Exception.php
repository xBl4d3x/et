<?php
namespace Et;
et_require('Exception');

class Debug_Assert_Exception extends Exception {

	const CODE_ASSERT_FAILED = 10;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_ASSERT_FAILED => "Assert failed",
	);

	/**
	 * @param string $error_message
	 * @param int $backtrace_offset [optional]
	 */
	function __construct($error_message, $backtrace_offset = 1){
		parent::__construct($error_message, self::CODE_ASSERT_FAILED, null, null, $backtrace_offset);
		if($this->debug_backtrace){
			$origin = $this->debug_backtrace[0];
			$this->file = $origin["file"];
			$this->line = $origin["line"];
		}
	}
}