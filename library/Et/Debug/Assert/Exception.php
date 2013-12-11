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
	 * @param string $comment_or_reason
	 * @param int $backtrace_offset [optional]
	 * @param null|string $reason [optional]
	 */
	function __construct($comment_or_reason, $backtrace_offset = 1, $reason = null){
		$exception_data = null;
		if($reason != $comment_or_reason){
			$exception_data = array("reason" => $reason);
		}
		parent::__construct($comment_or_reason, self::CODE_ASSERT_FAILED, $exception_data, null, $backtrace_offset);
		if($this->debug_backtrace){
			$origin = $this->debug_backtrace[0];
			$this->file = $origin["file"];
			$this->line = $origin["line"];
		}
	}
}