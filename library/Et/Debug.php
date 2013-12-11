<?php
namespace Et;
class Debug {

	const VAR_TYPE_BOOL = "boolean";
	const VAR_TYPE_INT = "integer";
	const VAR_TYPE_FLOAT = "double";
	const VAR_TYPE_NULL = "NULL";
	const VAR_TYPE_STRING = "string";
	const VAR_TYPE_OBJECT = "object";
	const VAR_TYPE_ARRAY = "array";
	const VAR_TYPE_RESOURCE = "resource";


	/**
	 * @var int
	 */
	protected static $default_dump_depth = 5;

	/**
	 * @var int
	 */
	protected static $default_dump_max_text_length = 0;

	/**
	 * @param int $max_dump_depth
	 */
	public static function setDefaultDumpDepth($max_dump_depth) {
		static::$default_dump_depth = max(0, (int)$max_dump_depth);
	}

	/**
	 * @return int
	 */
	public static function getDefaultDumpDepth() {
		return static::$default_dump_depth;
	}

	/**
	 * @param int $default_dump_max_text_length
	 */
	public static function setDefaultDumpMaxTextLength($default_dump_max_text_length) {
		static::$default_dump_max_text_length = max(0, (int)$default_dump_max_text_length);
	}

	/**
	 * @return int
	 */
	public static function getDefaultDumpMaxTextLength() {
		return static::$default_dump_max_text_length;
	}



	/**
	 * @param object $object
	 *
	 * @return string like ClassName#1
	 */
	public static function getObjectID($object){
		$class = get_class($object);
		$ID = sprintf("%u", crc32("{$class}#" . spl_object_hash($object)));
		return "{$class}#{$ID}";
	}

	/**
	 * @param bool $html [optional]
	 * @param null|int $max_depth [optional]
	 * @param null|int $max_text_length [optional]
	 * @return Debug_VarDump_Abstract
	 */
	public static function getVarDumperInstance($html = false, $max_depth = null, $max_text_length = null){
		if($max_depth === null){
			$max_depth = static::getDefaultDumpDepth();
		} else {
			$max_depth = max(0, (int)$max_depth);
		}

		if($max_text_length === null){
			$max_text_length = static::getDefaultDumpMaxTextLength();
		} else {
			$max_text_length = max(0, (int)$max_text_length);
		}

		if($html){
			trigger_error("NOT IMPLEMENTED", E_USER_ERROR);
			return false;
		} else {
			et_require("Debug_VarDump_Text");
			return new Debug_VarDump_Text($max_depth, $max_text_length);
		}
	}

	/**
	 * @param mixed $variable
	 * @param bool $html [optional]
	 * @param null|int $max_depth [optional]
	 * @param null|int $max_text_length [optional]
	 *
	 * @return string
	 */
	public static function varDump($variable, $html = false, $max_depth = null, $max_text_length = null){
		echo static::getVarDump($variable, $html, $max_depth, $max_text_length);
	}

	/**
	 * @param mixed $variable
	 * @param bool $html [optional]
	 * @param null|int $max_depth [optional]
	 * @param null|int $max_text_length [optional]
	 *
	 * @return string
	 */
	public static function getVarDump($variable, $html = false, $max_depth = null, $max_text_length = null){
		return static::getVarDumperInstance($html, $max_depth, $max_text_length)->getDump($variable);
	}

	/**
	 * @return float
	 */
	public static function getMicroTime(){
		return microtime(true);
	}

	/**
	 * @param float|null $since_time [optional] If NULL, ET_REQUEST_TIME is used
	 *
	 * @return float
	 */
	public static function getDuration($since_time = null){
		if($since_time === null){
			$since_time = ET_REQUEST_TIME;
		}
		return microtime(true) - $since_time;
	}

	/**
	 * @param string $class_name
	 * @return \ReflectionClass
	 * @throws Debug_Exception
	 */
	public static function getClassReflection($class_name){
		try {
			return new \ReflectionClass($class_name);
		} catch(\ReflectionException $e){
			throw new Debug_Exception(
				"Failed to get reflection of class '{$class_name}' - {$e->getMessage()}",
				Debug_Exception::CODE_INVALID_ARGUMENT,
				null,
				$e
			);
		}	
	}

	/**
	 * @param string $object_class_name
	 * @param array $constructor_arguments [optional]
	 * @return object
	 */
	public static function createObjectInstance($object_class_name, array $constructor_arguments = array()){
		return static::getClassReflection($object_class_name)->newInstanceArgs($constructor_arguments);
	}

	/**
	 * @param string $object_class_name
	 * @return object
	 */
	public static function createObjectInstanceWithoutConstructor($object_class_name){
		return static::getClassReflection($object_class_name)->newInstanceWithoutConstructor();
	}

	/**
	 * @param null|string $pattern [optional]
	 * @param bool $html_output [optional]
	 * @param bool $return_output [optional]
	 * @return bool|string
	 */
	public static function dumpConstants($pattern = null, $html_output = false, $return_output = false){
		$constants = get_defined_constants(false);
		if($pattern !== null || $html_output){
			foreach($constants as $constant => $value){
				if($pattern !== null && !preg_match("~{$pattern}~", $constant)){
					unset($constants[$constant]);
					continue;
				}
				if($html_output){
					$constants[$constant] = htmlspecialchars($value);
				}
			}
		}
		if($return_output){
			ob_start();
		}
		if(!$html_output){
			foreach($constants as $constant => $value){
				echo "{$constant} = " . var_export($value, true) . "\n";
			}
		} else {
			echo "<table border='1'>\n";
			foreach($constants as $constant => $value){
				echo "<tr><td>{$constant}</td><td>" . var_export($value, true) . "</td></tr>\n";
			}
			echo "</table>\n";
		}
		if($return_output){
			return ob_get_clean();
		}
		return true;
	}

	/**
	 * @param string $error_message
	 * @param array $error_context [optional]
	 * @param null|int $error_code [optional] NULL = E_USER_ERROR
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Exception_PHPError
	 */
	public static function triggerError($error_message, array $error_context = array(), $error_code = null, $backtrace_offset = 3){
		et_require("Exception_PHPError");
		Exception_PHPError::triggerError($error_message, $error_context, $error_code, $backtrace_offset);
	}

	/**
	 * @param string $error_message_prefix [optional]
	 * @param array $error_context [optional]
	 *
	 * @throws Exception_LastError
	 */
	public static function triggerLastError($error_message_prefix = "", array $error_context = array()){
		et_require("Exception_LastError");
		throw new Exception_LastError($error_message_prefix, $error_context);
	}
}