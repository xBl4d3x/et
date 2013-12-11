<?php
namespace Et;
et_require('Exception_PHPError');
class Exception_LastError extends Exception_PHPError {

	/**
	 * @param string $error_message_prefix [optional]
	 * @param array $error_context [optional]
	 */
	function __construct($error_message_prefix = "", array $error_context = array()){

		$last_error = error_get_last();
		if(!$last_error){
			Exception_PHPError::triggerError("No error detected");
		}

		parent::__construct(
			$error_message_prefix . $last_error["message"],
			$last_error["type"],
			$error_context,
			$last_error["script"],
			$last_error["line"]
		);
	}

	/**
	 * @param string $error_message_prefix [optional]
	 * @param array $error_context [optional]
	 *
	 * @return Exception_LastError
	 */
	public static function getInstance($error_message_prefix = "", array $error_context = array()){
		return new static($error_message_prefix, $error_context);
	}

}