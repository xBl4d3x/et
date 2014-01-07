<?php
namespace Et;
abstract class Exception extends \Exception {

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array();

	/**
	 * Custom exception data
	 *
	 * @var mixed
	 */
	protected $context_data;

	/**
	 * Debug backtrace
	 *
	 * @var array
	 */
	protected $debug_backtrace;


	/**
	 * @param string $error_message Exception error message
	 * @param int $error_code The Exception error code.
	 * @param mixed $context_data [optional] Custom exception context data
	 * @param \Exception|null $previous_exception [optional] Used for the exception chaining
	 * @param int $backtrace_offset [optional] How many items from backtrace to skip?
	 */
	public function __construct($error_message,
	                            $error_code,
	                            $context_data = null,
	                            \Exception $previous_exception = null,
	                            $backtrace_offset = 0
	) {

		$backtrace_offset = max(0, (int)$backtrace_offset);
		parent::__construct($error_message, $error_code, $previous_exception);

		$this->context_data = $context_data;
		$this->debug_backtrace = debug_backtrace();
		array_shift($this->debug_backtrace);

		for($skip = $backtrace_offset; $skip > 0 && $this->debug_backtrace; $skip--){
			array_shift($this->debug_backtrace);
		}
	}

	/**
	 * Get debug backtrace
	 *
	 * @return array
	 */
	public function getDebugBacktrace() {
		return $this->debug_backtrace;
	}


	/**
	 * Set custom data to exception
	 *
	 * @param mixed $data
	 */
	public function setContextData($data) {
		$this->context_data = $data;
	}

	/**
	 * Get custom exception context data
	 *
	 * @return mixed
	 */
	public function getContextData() {
		return $this->context_data;
	}

	/**
	 * Get human readable error label
	 *
	 * @param int|null $error_code [optional] NULL = current error code
	 *
	 * @return string
	 */
	public function getErrorCodeLabel($error_code = null) {

		if($error_code === null){
			$error_code = $this->code;
		}

		$codes = $this->getErrorCodesLabels(true, false);
		if(isset($codes[$error_code])){
			return $codes[$error_code];
		}

		return "Unknown error code {$error_code}";
	}

	/**
	 * Set human readable error code labels
	 *
	 * @param array $error_codes_labels
	 *
	 * @return array last labels
	 */
	public static function setErrorCodesLabels(array $error_codes_labels) {
		$original_labels = static::$error_codes_labels;
		static::$error_codes_labels = $error_codes_labels;
		return $original_labels;
	}

	/**
	 * Get error code labels
	 *
	 * @param bool $get_inherited [optional]
	 *
	 * @return array
	 */
	public static function getErrorCodesLabels($get_inherited = true) {
		$current_class = static::class;

		$error_codes_labels = static::$error_codes_labels;
		if(!$get_inherited || $current_class == __CLASS__){
			return $error_codes_labels;
		}

		$parent_class = get_parent_class($current_class);
		/** @var $parent_class Exception */
		$merged = $error_codes_labels + $parent_class::getErrorCodesLabels(true);
		ksort($merged);

		return $merged;
	}

	/**
	 * @return string
	 */
	function getErrorID(){
		return md5("{$this->file}:{$this->line}:{$this->code}:{$this->message}");
	}

	/**
	 * @return string
	 */
	function __toString(){
		return $this->getMessage();
	}
}