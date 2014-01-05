<?php
namespace Et;
et_require("Exception");
class Debug_PHPError extends Exception {

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
	 * @param string $file [optional]
	 * @param int $line_number [optional]
	 * @param int $backtrace_offset [optional]
	 */
	function __construct($error_message, array $error_context = array(), $error_code = null, $file = null, $line_number = null, $backtrace_offset = 1){

		if($error_code === null){
			$error_code = E_USER_ERROR;
		}

		parent::__construct($error_message, $error_code, $error_context, null, $backtrace_offset);

		if($file === null || $line_number === null){
			$backtrace = debug_backtrace();
			while(isset($backtrace[1]) && $backtrace_offset > 1){
				array_shift($backtrace);
				$backtrace_offset--;
			}
			$file = $backtrace[0]["file"];
			$line_number = $backtrace[0]["line"];
		}

		$this->line = $line_number;
		$this->file = $file;

	}

	/**
	 * Returns true if error is fatal
	 *
	 * @return bool
	 */
	public function isFatal(){
		return in_array($this->code, self::$fatal_errors);
	}

	/**
	 * Get error constant name (E_*)
	 *
	 * @return string
	 */
	public function getErrorConstantName(){
		return isset(self::$errors_constants[$this->code])
			? self::$errors_constants[$this->code]
			: "UNKNOWN [{$this->code}]";
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