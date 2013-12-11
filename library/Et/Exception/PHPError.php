<?php
namespace Et;
et_require('Exception');
class Exception_PHPError extends Exception {

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		E_ERROR => "PHP Error",
		E_WARNING => "PHP Warning",
		E_PARSE => "PHP Parsing Error",
		E_NOTICE => "PHP Notice",
		E_CORE_ERROR => "PHP Core Error",
		E_CORE_WARNING => "PHP Core Warning",
		E_COMPILE_ERROR => "PHP Compile Error",
		E_COMPILE_WARNING => "PHP Compile Warning",
		E_RECOVERABLE_ERROR => "PHP Recoverable error",
		E_USER_ERROR => "PHP User Error",
		E_USER_WARNING => "PHP User Warning",
		E_USER_NOTICE => "PHP User Notice",
		E_STRICT => "PHP Runtime Notice",
		E_DEPRECATED => "PHP Deprecated",
		E_USER_DEPRECATED => "PHP User Deprecated"
	);

	/**
	 * PHP error codes to string
	 *
	 * @var array
	 */
	protected static $errors_constants = array(
		E_ERROR => "E_ERROR",
		E_WARNING => "E_WARNING",
		E_PARSE => "E_PARSE",
		E_NOTICE => "E_NOTICE",
		E_CORE_ERROR => "E_CORE_ERROR",
		E_CORE_WARNING => "E_CORE_WARNING",
		E_COMPILE_ERROR => "E_COMPILE_ERROR",
		E_COMPILE_WARNING => "E_COMPILE_WARNING",
		E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
		E_USER_ERROR => "E_USER_ERROR",
		E_USER_WARNING => "E_USER_WARNING",
		E_USER_NOTICE => "E_USER_NOTICE",
		E_STRICT => "E_STRICT",
		E_DEPRECATED => "E_DEPRECATED",
		E_USER_DEPRECATED => "E_USER_DEPRECATED"
	);

	/**
	 * Fatal errors list
	 *
	 * @var array
	 */
	protected static $fatal_errors = array(
		E_ERROR,
		E_PARSE,
		E_CORE_ERROR,
		E_CORE_WARNING,
		E_COMPILE_ERROR,
		E_COMPILE_WARNING,
		E_USER_ERROR,
		E_RECOVERABLE_ERROR
	);

	/**
	 * @param string $error_message
	 * @param array $error_context [optional]
	 * @param int $error_code [optional] NULL = E_USER_ERROR
	 * @param string $script [optional]
	 * @param int $line_number [optional]
	 * @param int $backtrace_offset [optional]
	 */
	function __construct($error_message, array $error_context = array(), $error_code = null, $script = null, $line_number = null, $backtrace_offset = 1){

		if($error_code === null){
			$error_code = E_USER_ERROR;
		}

		parent::__construct($error_message, $error_code, $error_context, null, $backtrace_offset);

		if($script === null || $line_number === null){
			$backtrace = debug_backtrace();
			while(isset($backtrace[1]) && $backtrace_offset > 1){
				array_shift($backtrace);
				$backtrace_offset--;
			}
			$script = $backtrace[0]["file"];
			$line_number = $backtrace[0]["line"];
		}

		$this->line = $line_number;
		$this->file = $script;

	}

	/**
	 * @param string $error_message
	 * @param array $error_context [optional]
	 * @param null|int $error_code [optional] NULL = E_USER_ERROR
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Exception_PHPError
	 */
	public static function triggerError($error_message, array $error_context = array(), $error_code = null, $backtrace_offset = 2){
		if(!$error_code){
			$error_code = E_USER_ERROR;
		}

		throw new Exception_PHPError(
			$error_message,
			$error_context,
			$error_code,
			null,
			null,
			$backtrace_offset
		);
	}

	/**
	 * Returns true if error is fatal
	 *
	 * @param int $error_number
	 * @return bool
	 */
	public function isFatal($error_number = null){
		if($error_number === null){
			$error_number = $this->code;
		}
		return in_array($error_number, self::$fatal_errors);
	}

	/**
	 * Get error constant name (E_*)
	 *
	 * @param int $error_number
	 *
	 * @return string
	 */
	public function getErrorConstantName($error_number = null){
		if($error_number === null){
			$error_number = $this->code;
		}

		return isset(self::$errors_constants[$error_number])
			? self::$errors_constants[$error_number]
			: "UNKNOWN [{$error_number}]";
	}

	/**
	 * @return array
	 */
	public static function getErrorsConstants() {
		return self::$errors_constants;
	}

	/**
	 * @return array
	 */
	public static function getFatalErrorsCodes() {
		return self::$fatal_errors;
	}
}