<?php
namespace Et;
abstract class Debug_Error_Handler_Abstract {

	const LINE_DELIMITER_LENGTH = 120;

	/**
	 * @var bool
	 */
	protected $handle_exceptions = true;

	/**
	 * @var bool
	 */
	protected $handle_errors = true;

	/**
	 * @var int E_* combination
	 */
	protected $error_code_mask = E_ALL;

	/**
	 * @var bool
	 */
	protected $enabled = true;

	function __construct(){

	}


	/**
	 * @param Debug_Error $e
	 */
	function handleError(Debug_Error $e){
		if(!$this->enabled){
			return;
		}

		if(!$this->handle_exceptions && $e->isException()){
			return;
		}

		if($e->isError()){
			if(!$this->handle_errors){
				return;
			}
			if(!($e->getErrorCode() & $this->error_code_mask)){
				return;
			}

		}

		$this->_handleError($e);
	}

	/**
	 * @param Debug_Error $e
	 */
	abstract protected function _handleError(Debug_Error $e);

	/**
	 * @return static|\Et\Debug_Error_Handler_Abstract
	 */
	public function enable() {
		$this->enabled = true;
		return $this;
	}

	/**
	 * @return static|\Et\Debug_Error_Handler_Abstract
	 */
	public function disable() {
		$this->enabled = false;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isEnabled() {
		return $this->enabled;
	}

	/**
	 * @param int $error_code_mask
	 * @return static|\Et\Debug_Error_Handler_Abstract
	 */
	public function setErrorCodeMask($error_code_mask) {
		$this->error_code_mask = (int)$error_code_mask;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getErrorCodeMask() {
		return $this->error_code_mask;
	}

	/**
	 * @param boolean $handle_exceptions
	 *
	 * @return static|\Et\Debug_Error_Handler_Abstract
	 */
	public function setHandleExceptions($handle_exceptions) {
		$this->handle_exceptions = (bool)$handle_exceptions;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getHandleExceptions() {
		return $this->handle_exceptions;
	}

	/**
	 * @param boolean $handle_errors
	 * @return static|\Et\Debug_Error_Handler_Abstract
	 */
	public function setHandleErrors($handle_errors) {
		$this->handle_errors = (bool)$handle_errors;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getHandleErrors() {
		return $this->handle_errors;
	}

	/**
	 * @param Debug_Error $e
	 * @param int $max_text_length [optional]
	 * @param array &$dumped_objects [optional]
	 *
	 * @return string
	 */
	public function getBacktraceAsText(Debug_Error $e, $max_text_length = 256, array &$dumped_objects = array()){
		$backtrace = $e->getBacktrace();
		$lines = array();

		foreach($backtrace as $row){
			$path = $row["file"];
			if(defined(ET_BASE_PATH)){
				$path = preg_replace("~^(".preg_quote(ET_BASE_PATH, ")~")."~", "[root]/", $path);
			}
			$line = "#" . (count($lines) + 1) . " [{$path}:{$row["line"]}]";
			if(!$row["method"] && !$row["function"]){
				$lines[] = $line;
				continue;
			}

			$line .= " ";
			if($row["method"]){
				$line .= $row["method"] . "(";
			} elseif($row["function"]){
				$line .= $row["function"] . "(";
			}

			if(!$row["args"]){
				$line .= ")";
				$lines[] = $line;
				continue;
			}

			$args = array();
			$any_new_line = false;
			foreach($row["args"] as $arg){
				$dumped_arg = Debug::getVarDump($arg, false, null, $max_text_length, $dumped_objects);
				if(!$any_new_line && strpos($dumped_arg, "\n") !== false){
					$any_new_line = true;
				}
				$args[] = $dumped_arg;
			}

			if(!$any_new_line){
				$line .= implode(", ", $args) . ")";
			} else {
				foreach($args as $i => $arg){
					$line .= "\n    " . str_replace("\n", "\n    ", $arg);
					if(isset($args[$i+1])){
						$line .= ",";
					}
				}
				$line .= "\n)";

			}
			$lines[] = $line;
		}
		return implode("\n", $lines);
	}


	/**
	 * @param Debug_Error $e
	 *
	 * @return string
	 */
	public function getErrorHeaderAsText(Debug_Error $e){
		if($e->isException()){
			$output = "Exception {$e->getExceptionClass()} - {$e->getErrorCodeLabel()} (code {$e->getErrorCode()}) occurred:\n";
		} else {
			$output = $e->getErrorCodeLabel() . " occurred:\n";
		}

		$output .= trim($e->getErrorMessage()) . "\n\n";
		$output .= "File: {$e->getFile()}\n";
		$output .= "Line: {$e->getLine()}\n";
		if($e->getURL()){
			$output .= "URL: " . $e->getURL() . "\n";
		}

		$output .= "Time: " . date("Y-m-d H:i:sP", $e->getTimestamp()) . "\n";
		$output .= "Strict mode: " . ($e->getStrictModeEnabled() ? "YES" : "NO") . "\n";
		$output .= "On shutdown: " . ($e->hasOccurredOnShutdown() ? "YES" : "NO");


		return $output;
	}

	/**
	 * @param Debug_Error $e
	 * @param int $max_text_length [optional]
	 * @param array &$dumped_objects [optional]
	 *
	 * @return string
	 */
	public function getErrorContextAsText(Debug_Error $e, $max_text_length = 256, array &$dumped_objects = array()){
		if(!$e->hasContextData()){
			return "";
		}

		$context = $e->getContextData();
		if($e->isError() && is_array($context)){
			$output = "";
			foreach($context as $var => $value){;
				$dump = Debug::getVarDump($value, false, null, $max_text_length, $dumped_objects);
				$line = "\${$var} => ";
				if(strpos($dump, "\n") === false){
					$line .= $dump;
				} else {
					$line .= str_replace("\n", "\n" . str_repeat(" ", strlen($line)), $dump);
				}
				$output .= $line . "\n";
			}
			return rtrim($output, "\n");
		} else {
			return rtrim(Debug::getVarDump($context, false, null, $max_text_length, $dumped_objects));
		}
	}

	/**
	 * @return string
	 */
	function getFactoryMapAsText(){
		$factory_map = array();
		if(class_exists("Et\\Factory", false)){
			$factory_map = Factory::getClassOverrideMap();
		}

		if(!$factory_map){
			return "";
		}

		$longest_class = 0;
		foreach($factory_map as $orig => $new){
			$longest_class = max($longest_class, strlen($orig));
		}

		$output = array();
		foreach($factory_map as $orig => $new){
			$output[] = str_pad($orig, $longest_class, " ", STR_PAD_RIGHT) . " => {$new}";
		}
		return implode("\n", $output);
	}

	/**
	 * @param Debug_Error $e
	 *
	 * @return string
	 */
	function getErrorAsText(Debug_Error $e){
		$output = str_repeat("=", self::LINE_DELIMITER_LENGTH) . "\n";
		$dumped_objects = array();
		$output .= $this->getErrorHeaderAsText($e);

		if($e->hasContextData()){
			$output .= "\n\n";
			$output .= "Error context/data:\n";
			$output .=  str_repeat("=", self::LINE_DELIMITER_LENGTH) . "\n";
			$output .= $this->getErrorContextAsText($e, 256, $dumped_objects);
		}


		if(class_exists('Et\Factory', false)){
			$factory_map = $this->getFactoryMapAsText();
			if($factory_map){
				$output .= "\n\n";
				$output .= "Factory overloaded classes:\n";
				$output .=  str_repeat("=", self::LINE_DELIMITER_LENGTH) . "\n";
				$output .= $factory_map;
			}
		}

		$backtrace = $this->getBacktraceAsText($e, 256, $dumped_objects);
		if($backtrace){
			$output .= "\n\n";
			$output .= "Debug backtrace:\n";
			$output .=  str_repeat("=", self::LINE_DELIMITER_LENGTH) . "\n";
			$output .= $backtrace;
		}
		$output .= "\n" . str_repeat("=", self::LINE_DELIMITER_LENGTH) . "\n";

		$previous = $e->getPreviousError();
		if($previous){
			$previous_content = "\n";
			$previous_content .=  str_repeat("=", self::LINE_DELIMITER_LENGTH) . "\n";
			$previous_content .= "Previous ".($previous->isError() ? "error" : "exception").":\n";

			$previous_content .= $this->getErrorAsText($previous);
			$output .= str_replace("\n", "\n    ", rtrim($previous_content));
		}

		$output .= "\n\n\n";

		return $output;
	}
}
