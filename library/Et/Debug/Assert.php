<?php
namespace Et;
et_require('Debug_Assert_Exception');
class Debug_Assert {

	const TYPE_BOOL = "boolean";
	const TYPE_INT = "integer";
	const TYPE_FLOAT = "double";
	const TYPE_STRING = "string";
	const TYPE_ARRAY = "array";
	const TYPE_OBJECT = "object";
	const TYPE_RESOURCE = "resource";
	const TYPE_NULL = "NULL";

	
	/**
	 * @param string $reason
	 * @param string $comment
	 * @param array $reason_data [optional]
	 *
	 * @throws Debug_Assert_Exception
	 */
	protected static function throwException($reason, $comment, array $reason_data = array()){
		
		if(!$reason){
			$reason = "Assert failed";
		}

		foreach($reason_data as $k => $v){
			$reason = str_replace("{{$k}}", (string)$v, $reason);
		}

		$error_message = "Assert failed";
		if($reason){
			$error_message .= ": {$reason}";
		}

		$comment = trim($comment);
		if($comment){
			$error_message .= "\nComment: {$comment}";
		}


		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$backtrace_offset = 0;
		do {
			$b = array_shift($backtrace);
			if(!isset($b["class"]) || $b["class"] != static::class){
				break;
			}
			$backtrace_offset++;
			
		} while($backtrace);
		
		throw new Debug_Assert_Exception(
			$error_message,
			$backtrace_offset
		);
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isTrue($value, $comment = ""){
		if($value !== true){
			static::throwException("Value is not TRUE (boolean)", $comment);
		}
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotTrue($value, $comment = ""){
		if($value === true){
			static::throwException("Value is TRUE (boolean)", $comment);
		}
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isFalse($value, $comment = ""){
		if($value !== false){
			static::throwException("Value is not FALSE (boolean)", $comment);
		}
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotFalse($value, $comment = ""){
		if($value === false){
			static::throwException("Value is FALSE (boolean)", $comment);
		}
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isEmpty($value, $comment = ""){
		if(!empty($value)){
			static::throwException("Value is not empty", $comment);
		}
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 *
	 */
	public static function isNotEmpty($value, $comment = ""){
		if(empty($value)){
			static::throwException("Value is empty", $comment);
		}

	}

	/**
	 * @param mixed $value
	 * @param bool $check_mx [optional]
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isEmail($value, $check_mx = false, $comment = ""){
		static::isString($value, $comment);

		if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
			static::throwException("Invalid e-mail format", $comment);
		}

		if($check_mx){
			list($domain) = explode("@", $value, 2);
			if(!getmxrr($domain, $mx_hosts)){
				static::throwException("Invalid e-mail - DNS MX record not found", $comment);
			}
		}
	}

	/**
	 * @param mixed $value
	 * @param bool $path_required [optional]
	 * @param bool $query_allowed [optional]
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isURL($value, $path_required = true, $query_allowed = true, $comment = ""){

		static::isString($value, $comment);
		if($path_required){
			$res = filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);
		} else {
			$res = filter_var($value, FILTER_VALIDATE_URL);
		}

		if(!$res){
			static::throwException("Invalid URL format", $comment);
		}

		if(!preg_match('~^(http|https):~', $value)){
			static::throwException("Invalid or missing URL scheme (http/https)", $comment);
		}

		if(!$query_allowed && strpos($value, "?") !== false){
			static::throwException("URL query part (after '?') not allowed", $comment);
		}		
	}

	/**
	 * @param mixed $value
	 * @param bool $query_allowed [optional]
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isURI($value, $query_allowed = true, $comment = ""){
		static::isString($value, $comment);
		if($value === "" || $value[0] != '/' || !filter_var("http://localhost{$value}", FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)){
			static::throwException("Invalid URI format", $comment);
		}

		if(!$query_allowed && strpos($value, "?") !== false){
			static::throwException("URI query part (?) not allowed", $comment);
		}		
	}

	/**
	 * @param mixed $value
	 * @param bool $IPv4_only [optional]
	 * @param bool $IPv6_only [optional]
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isIP($value, $IPv4_only = false, $IPv6_only = false, $comment = ""){

		static::isString($value, $comment);
		$IPv4_only = (bool)$IPv4_only;
		$IPv6_only = (bool)$IPv6_only;

		if($IPv4_only == $IPv6_only){
			if(!filter_var($value, FILTER_VALIDATE_IP)){
				static::throwException("Invalid IP", $comment);
			}
			return;
		}

		if($IPv4_only && !filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){

			static::throwException("Invalid IPv4 IP", $comment);

		} elseif($IPv6_only && !filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){

			static::throwException("Invalid IPv6 IP", $comment);

		}
	}


	/**
	 * @param mixed $value
	 * @param mixed $equals_to
	 * @param bool $strict
	 *
	 * @return bool
	 */
	protected static function valueEquals(&$value, &$equals_to, $strict){
		if($strict){
			if($value !== $equals_to){
				return false;
			}
		} else {
			if($value != $equals_to){
				return false;
			}
		}

		if(is_array($value) && !is_array($equals_to)){
			return false;
		}

		if(is_object($value) && !is_object($equals_to)){
			return false;
		}

		return true;
	}

	/**
	 * @param mixed $value
	 * @param mixed $equals_to
	 * @param bool $strict_compare [optional]
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	static function isEqual($value, $equals_to, $strict_compare = false, $comment = ""){
		if(!static::valueEquals($value, $equals_to, $strict_compare)){
			static::throwException(
				"Values are not equal",
				$comment
			);
		}

		
	}

	/**
	 * @param mixed $value
	 * @param mixed $equals_to
	 * @param bool $strict_compare [optional]
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	static function isNotEqual($value, $equals_to, $strict_compare = false, $comment = ""){
		if(static::valueEquals($value, $equals_to, $strict_compare)){
			static::throwException(
				"Values are equal",
				$comment
			);
		}

		
	}

	/**
	 * @param mixed $value
	 * @param mixed $equals_to
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	static function isSame($value, $equals_to, $comment = ""){
		if(!static::valueEquals($value, $equals_to, true)){
			static::throwException(
				"Values are not same",
				$comment
			);
		}

		
	}

	/**
	 * @param mixed $value
	 * @param mixed $equals_to
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	static function isNotSame($value, $equals_to, $comment = ""){
		if(static::valueEquals($value, $equals_to, true)){
			static::throwException(
				"Values are same",
				$comment
			);
		}

		
	}

	/**
	 * @param mixed $value
	 * @param string|array $allowed_type_or_types
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function hasType($value, $allowed_type_or_types, $comment = ""){
		if(!is_array($allowed_type_or_types)){
			$allowed_type_or_types = array($allowed_type_or_types);
		}

		if(!in_array(gettype($value), $allowed_type_or_types)){
			$reason = "Value must be {ALLOWED_TYPES}, not {GIVEN_TYPE}";
			static::throwException(
				$reason,
				$comment,
				array(
					"ALLOWED_TYPES" => implode(" or ", $allowed_type_or_types),
					"GIVEN_TYPE" => gettype($value)
				)
			);
		}

		
	}

	/**
	 * @param mixed $value
	 * @param string|array $disallowed_type_or_types
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function hasNotType($value, $disallowed_type_or_types, $comment = ""){
		if(!is_array($disallowed_type_or_types)){
			$disallowed_type_or_types = array($disallowed_type_or_types);
		}

		if(!in_array(gettype($value), $disallowed_type_or_types)){
			$reason = "Value may not be {DISALLOWED_TYPES}";
			static::throwException(
				$reason,
				$comment,
				array(
					"DISALLOWED_TYPES" => implode(" or ", $disallowed_type_or_types)
				)
			);
		}

		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isBool($value, $comment = ""){
		static::hasType($value, self::TYPE_BOOL, $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotBool($value, $comment = ""){
		static::hasNotType($value, self::TYPE_BOOL, $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isScalar($value, $comment = ""){
		if(!is_scalar($value)){
			static::throwException("Value must be scalar, not {TYPE}", $comment, array("TYPE" => gettype($value)));
		}
		

	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotScalar($value, $comment = ""){
		if(is_scalar($value)){
			static::throwException("Value may not be scalar", $comment);
		}
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isScalarOrNULL($value, $comment = ""){
		if(!is_scalar($value) && $value !== null){
			$reason = "Value must be scalar or NULL, not {TYPE}";
			static::throwException($reason, $comment, array("TYPE" => gettype($value)));
		}

		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotScalarOrNULL($value, $comment = ""){
		if(is_scalar($value) || $value === null){
			static::throwException("Value may not be scalar or NULL", $comment);
		}

		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNumber($value, $comment = ""){
		if(!is_numeric($value)){
			$reason = "Value must be number, not {TYPE}";
			static::throwException($reason, $comment, array("TYPE" => gettype($value)));
		}
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotNumber($value, $comment = ""){
		if(is_numeric($value)){
			static::throwException("Value may not be number", $comment);
		}
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isInteger($value, $comment = ""){
		static::hasType($value, self::TYPE_INT, $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotInteger($value, $comment = ""){
		static::hasNotType($value, self::TYPE_INT, $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isFloat($value, $comment = ""){
		static::hasType($value, self::TYPE_FLOAT, $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotFloat($value, $comment = ""){
		static::hasNotType($value, self::TYPE_FLOAT, $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNull($value, $comment = ""){
		if($value !== null){
			static::throwException("Value is not NULL", $comment);
		}
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotNull($value, $comment = ""){
		if($value === null){
			static::throwException("Value may not be NULL", $comment);
		}
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isString($value, $comment = ""){
		static::hasType($value, self::TYPE_STRING, $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotString($value, $comment = ""){
		static::hasNotType($value, self::TYPE_STRING, $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isStringOrNumber($value, $comment = ""){
		static::hasType($value, array(self::TYPE_STRING, self::TYPE_INT, self::TYPE_FLOAT), $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotStringOrNumber($value, $comment = ""){
		static::hasNotType($value, array(self::TYPE_STRING, self::TYPE_INT, self::TYPE_FLOAT), $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isArray($value, $comment = ""){
		static::hasType($value, self::TYPE_ARRAY, $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isLinearArray($value, $comment = ""){
		static::isArray($value, $comment);

		if(!$value){
			return;
		}

		if(array_keys($value) !== range(0, count($value) - 1, 1)){
			static::throwException("Value is not linear array", $comment);
		}
		
	}

	/**
	 * @param mixed $value
	 * @param array|string $allowed_value_type_or_types
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isArrayOfType($value, $allowed_value_type_or_types, $comment = ""){
		static::isArray($value, $comment);
		if(!$value){
			return;
		}

		if(!is_array($allowed_value_type_or_types)){
			$allowed_value_type_or_types = array($allowed_value_type_or_types);
		}

		foreach($value as $k => $v){
			$type = gettype($v);
			if(!in_array($type, $allowed_value_type_or_types)){
				$reason = "Value with key '{KEY}' must be {ALLOWED_TYPES}, not {GIVEN_TYPE}";
				static::throwException(
					$reason,
					$comment,
					array(
						"ALLOWED_TYPES" => implode(" or ", $allowed_value_type_or_types),
						"GIVEN_TYPE" => $type,
						"KEY" => $k
					)
				);
			}
		}
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isArrayOfArrays($value, $comment = ""){
		static::isArrayOfType($value, self::TYPE_ARRAY, $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isArrayOfStrings($value, $comment = ""){
		static::isArrayOfType($value, self::TYPE_STRING, $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isArrayOfNumbers($value, $comment = ""){
		static::isArrayOfType($value, array(self::TYPE_INT, self::TYPE_FLOAT), $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isArrayOfStringsOrNumbers($value, $comment = ""){
		static::isArrayOfType($value, array(self::TYPE_STRING, self::TYPE_INT, self::TYPE_FLOAT), $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isArrayOfScalars($value, $comment = ""){
		static::isArray($value, $comment);
		if(!$value){
			return;
		}

		foreach($value as $k => $v){
			if(!is_scalar($value)){
				$reason = "Value with key '{KEY}' must be scalar, not {GIVEN_TYPE}";
				static::throwException(
					$reason,
					$comment,
					array(
						"GIVEN_TYPE" => gettype($v),
						"KEY" => $k
					)
				);
			}
		}
		
	}

	/**
	 * @param mixed|array $value
	 * @param string $instances_of_class
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isArrayOfInstances($value, $instances_of_class, $comment = ""){
		static::isArray($value, $comment);
		static::isClassName($instances_of_class, true, $comment);

		foreach($value as $k => $v){
			if(!($v instanceof $instances_of_class)){

				static::throwException(
					"Value with key {KEY} is not instance of {CLASS}",
					$comment,
					array(
						"KEY" => $k,
						"CLASS" => $instances_of_class
					)
				);

			}
		}
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotArray($value, $comment = ""){
		static::hasNotType($value, self::TYPE_ARRAY, $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isObject($value, $comment = ""){
		static::hasType($value, self::TYPE_OBJECT, $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotObject($value, $comment = ""){
		static::hasNotType($value, self::TYPE_OBJECT, $comment);
		
	}

	/**
	 * @param string|Locales_Locale $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isLocale($value, $comment = ""){
		if($value instanceof Locales_Locale){
			return;
		}

		try {
			Locales::checkLocale($value);
		} catch(Locales_Exception $e){
			static::throwException("Invalid locale", $comment);
		}
		
	}

	/**
	 * @param string $date
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isDate($date, $comment = ""){
		if($date instanceof Locales_DateTime){
			return;
		}

		static::isStringMatching($date, '~^\d{4}-\d{2}-\d{2}$~', $comment ? $comment : "Invalid date format");
		if(@strtotime($date) === false){
			static::throwException("Invalid date", $comment);
		}
		
	}

	/**
	 * @param string $time
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isTime($time, $comment = ""){
		if($time instanceof Locales_DateTime){
			return;
		}

		static::isStringMatching($time, '~^\d{2}:\d{2}:\d{2}$~', $comment ? $comment : "Invalid time format");
		$time_ts = @strtotime("2020-01-01 {$time}");
		if($time_ts === false || date("Y-m-d", $time_ts) !== "2020-01-01"){
			static::throwException("Invalid time", $comment);
		}
		
	}

	/**
	 * @param string $date_time
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isDateTime($date_time, $comment = ""){
		if($date_time instanceof Locales_DateTime){
			return;
		}

		static::isStringMatching($date_time, '~^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}$~', $comment ? $comment : "Invalid date and time format");
		$date = substr($date_time, 0, 10);
		if(@strtotime($date) === false){
			static::throwException("Invalid date", $comment);
		}

		$time = substr($date_time, 11, 8);
		$time_ts = @strtotime("2020-01-01 {$time}");
		if($time_ts === false || date("Y-m-d", $time_ts) !== "2020-01-01"){
			static::throwException("Invalid time", $comment);
		}

		
	}

	/**
	 * @param mixed $value
	 * @param string $instance_of
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isInstanceOf($value, $instance_of, $comment = ""){
		if(!$value instanceof $instance_of){
			static::throwException("Value must be instance of {INSTANCE_OF}", $comment, array("INSTANCE_OF" => $instance_of));
		}
		
	}

	/**
	 * @param mixed|object $value
	 * @param string $instance_of
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotInstanceOf($value, $instance_of, $comment = ""){
		if($value instanceof $instance_of){
			static::throwException("Value may no be instance of {INSTANCE_OF}", $comment, array("INSTANCE_OF" => $instance_of));
		}
		
	}

	/**
	 * @param mixed $value
	 * @param int|float $greater_than
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isGreaterThan($value, $greater_than, $comment = ""){
		static::isNumber($value, $comment);
		if($value <= $greater_than){
			$reason = "Value must be greater than {GREATER_THAN}";
			$reason_data = array(
				"GREATER_THAN" => $greater_than,
			);
			static::throwException($reason, $comment, $reason_data);
		}
		
	}

	/**
	 * @param mixed $value
	 * @param int|float $greater_or_equal_than
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isGreaterOrEqualThan($value, $greater_or_equal_than, $comment = ""){
		static::isNumber($value, $comment);
		if($value < $greater_or_equal_than){
			$reason = "Value must be greater than {GREATER_THAN} or equal";
			$reason_data = array(
				"GREATER_THAN" => $greater_or_equal_than
			);
			static::throwException($reason, $comment, $reason_data);
		}
		
	}

	/**
	 * @param mixed $value
	 * @param int|float $lower_than
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isLowerThan($value, $lower_than, $comment = ""){
		static::isNumber($value, $comment);
		if($value >= $lower_than){
			$reason = "Value must be lower than {LOWER_THAN}";
			$reason_data = array(
				"LOWER_THAN" => $lower_than,
				"VALUE" => $value
			);
			static::throwException($reason, $comment, $reason_data);
		}

		
	}

	/**
	 * @param mixed $value
	 * @param int|float $lower_or_equal_than
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isLowerOrEqualThan($value, $lower_or_equal_than, $comment = ""){
		static::isNumber($value, $comment);
		if($value > $lower_or_equal_than){
			$reason = "Value must be lower than {LOWER_THAN} or equal";
			$reason_data = array(
				"LOWER_THAN" => $lower_or_equal_than
			);
			static::throwException($reason, $comment, $reason_data);
		}
		
	}

	/**
	 * @param array|mixed $value
	 * @param string $key
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function arrayKeyExists($value, $key, $comment = ""){
		static::isArray($value, $comment);
		if(!array_key_exists($key, $value)){
			$reason = "Key '{KEY}' not found in array";
			static::throwException($reason, $comment, array("KEY" => $key));
		}
		
	}

	/**
	 * @param array|mixed $value
	 * @param string $key
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function arrayKeyNotExists($value, $key, $comment = ""){
		static::isArray($value, $comment);
		if(array_key_exists($key, $value)){
			$reason = "Key '{KEY}' may not exist";
			static::throwException($reason, $comment, array("KEY" => $key));
		}
		
	}

	/**
	 * @param array|mixed $value
	 * @param array $keys
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function arrayKeysExist($value, array $keys, $comment = ""){
		static::isArray($value, $comment);
		if(!$keys){
			return;
		}

		$missing_keys = array();
		foreach($keys as $key){
			if(!array_key_exists($key, $value)){
				$missing_keys[] = $key;
			}
		}

		if($missing_keys){
			$reason = "Missing {KEYS} key(s) in array";
			$reason_data = array(
				"KEYS" => "'" . implode("', '", $missing_keys) . "'"
			);
			static::throwException($reason, $comment, $reason_data);
		}

		
	}

	/**
	 * @param array|mixed $value
	 * @param array $keys
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function arrayKeysNotExist($value, array $keys, $comment = ""){
		static::isArray($value, $comment);
		if(!$keys){
			return;
		}

		$existing_keys = array();
		foreach($keys as $key){
			if(array_key_exists($key, $value)){
				$existing_keys[] = $key;
			}
		}

		if($existing_keys){
			$reason = "Key(s) {KEYS} may not exist";
			$reason_data = array(
				"KEYS" => "'" . implode("', '", $existing_keys) . "'"
			);
			static::throwException($reason, $comment, $reason_data);
		}
		
	}

	/**
	 * @param mixed $value
	 * @param string $regex_pattern
	 * @param string $comment [optional]
	 * @param string $regex_modifiers [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isStringMatching($value, $regex_pattern, $comment = "", $regex_modifiers = ""){
		static::isStringOrNumber($value, $comment);
		if(!preg_match("~{$regex_pattern}~{$regex_modifiers}", $value)){
			$reason = "Value has invalid format";
			static::throwException($reason, $comment);
		}
		
	}

	/**
	 * @param mixed $value
	 * @param string $regex_pattern
	 * @param string $comment [optional]
	 * @param string $regex_modifiers [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isStringNotMatching($value, $regex_pattern, $comment = "", $regex_modifiers = ""){
		static::isStringOrNumber($value, $comment);
		if(preg_match("~{$regex_pattern}~{$regex_modifiers}", $value)){
			$reason = "Value has invalid format";
			static::throwException($reason, $comment);
		}
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isVariableName($value, $comment = ""){
		static::isStringMatching($value, '^\w+$', $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isIdentifier($value, $comment = ""){
		static::isStringMatching($value, '^\w[-\w]*$', $comment);
		
	}

	/**
	 * @param mixed $value
	 * @param bool $check_if_exists [optional]
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isClassName($value, $check_if_exists = false, $comment = ""){
		static::isStringMatching($value, '~^\\\\?\w+(\\\\\w+)*$~', $comment ? $comment : "Invalid class name");
		if($check_if_exists && !class_exists($value)){
			static::throwException("Class '{CLASS_NAME}' not exists", $comment, array("CLASS_NAME" => $value));
		}
	}

	/**
	 * @param mixed $value
	 * @param bool $check_if_exists [optional]
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 *
	 */
	public static function isInterfaceName($value, $check_if_exists = false, $comment = ""){
		static::isStringMatching($value, '~^\\\\?\w+(\\\\\w+)*$~', $comment ? $comment : "Invalid class name");
		if($check_if_exists && !interface_exists($value)){
			static::throwException("Interface '{CLASS_NAME}' not exists", $comment, array("CLASS_NAME" => $value));
		}
	}

	/**
	 * @param mixed $value
	 * @param bool $check_if_exists [optional]
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 *
	 */
	public static function isTraitName($value, $check_if_exists = false, $comment = ""){
		static::isStringMatching($value, '~^\\\\?\w+(\\\\\w+)*$~', $comment ? $comment : "Invalid class name");
		if($check_if_exists && !trait_exists($value)){
			static::throwException("Trait '{CLASS_NAME}' not exists", $comment, array("CLASS_NAME" => $value));
		}
	}

	/**
	 * @param mixed $value
	 * @param string $parent_class_name
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isSubclass($value, $parent_class_name, $comment = ""){
		static::isClassName($value, $comment);
		static::isClassName($parent_class_name, $comment);
		if(!is_subclass_of($value, $parent_class_name, true)){
			$reason = "Class {CLASS_NAME} is not subclass of {PARENT_CLASS}";
			static::throwException($reason, $comment, array("CLASS_NAME" => $value, "PARENT_CLASS" => $parent_class_name));
		}
		
	}

	/**
	 * @param mixed $value
	 * @param string $parent_class_name
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isSubclassOrSameClass($value, $parent_class_name, $comment = ""){

		if($value == $parent_class_name){
			static::isClassName($value, true, $comment);
		} else {
			static::isSubclass($value, $parent_class_name, $comment);
		}
	}


	/**
	 * @param string $file_path
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function fileExists($file_path, $comment = ""){
		static::isString($file_path, $comment);
		if(!file_exists($file_path) || !@is_file($file_path)){
			$reason = "File '{FILE}' does not exist";
			static::throwException($reason, $comment, array("FILE" => $file_path));
		}
		
	}

	/**
	 * @param string $file_path
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function fileNotExists($file_path, $comment = ""){
		static::isString($file_path, $comment);
		if(file_exists($file_path) && @is_file($file_path)){
			$reason = "File '{FILE}' already exists";
			static::throwException($reason, $comment, array("FILE" => $file_path));
		}
		
	}

	/**
	 * @param string $file_path
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isReadableFile($file_path, $comment = ""){
		static::fileExists($file_path, $comment);
		if(!@is_readable($file_path)){
			$reason = "File '{FILE}' is not readable";
			static::throwException($reason, $comment, array("FILE" => $file_path));
		}
		
	}

	/**
	 * @param string $file_path
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotReadableFile($file_path, $comment = ""){
		static::isString($file_path, $comment);
		if(file_exists($file_path) && @is_file($file_path) && @is_readable($file_path)){
			$reason = "File '{FILE}' already exists and is readable";
			static::throwException($reason, $comment, array("FILE" => $file_path));
		}
		
	}

	/**
	 * @param string $file_path
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isWritableFile($file_path, $comment = ""){
		static::fileExists($file_path, $comment);
		if(!@is_writable($file_path)){
			$reason = "File '{FILE}' is not writable";
			static::throwException($reason, $comment, array("FILE" => $file_path));
		}
		
	}

	/**
	 * @param string $file_path
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotWritableFile($file_path, $comment = ""){
		static::isString($file_path, $comment);
		if(file_exists($file_path) && @is_file($file_path) && @is_readable($file_path)){
			$reason = "File '{FILE}' already exists and is writable";
			static::throwException($reason, $comment, array("FILE" => $file_path));
		}
		
	}


	/**
	 * @param string $dir_path
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function directoryExists($dir_path, $comment = ""){
		static::isString($dir_path, $comment);
		if(!file_exists($dir_path) || !@is_dir($dir_path)){
			$reason = "Directory '{DIR}' does not exist";
			static::throwException($reason, $comment, array("DIR" => $dir_path));
		}
		
	}

	/**
	 * @param string $dir_path
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function directoryNotExists($dir_path, $comment = ""){
		static::isString($dir_path, $comment);
		if(file_exists($dir_path) && @is_dir($dir_path)){
			$reason = "Directory '{DIR}' already exists";
			static::throwException($reason, $comment, array("DIR" => $dir_path));
		}
		
	}

	/**
	 * @param string $dir_path
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isReadableDirectory($dir_path, $comment = ""){
		static::directoryExists($dir_path, $comment);
		if(!@is_readable($dir_path)){
			$reason = "Directory '{DIR}' is not readable";
			static::throwException($reason, $comment, array("DIR" => $dir_path));
		}
		
	}

	/**
	 * @param string $dir_path
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotReadableDirectory($dir_path, $comment = ""){
		static::isString($dir_path, $comment);
		if(file_exists($dir_path) && @is_dir($dir_path) && @is_readable($dir_path)){
			$reason = "Directory '{DIR}' exists and is readable";
			static::throwException($reason, $comment, array("DIR" => $dir_path));
		}
		
	}

	/**
	 * @param string $dir_path
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isWritableDirectory($dir_path, $comment = ""){
		static::directoryExists($dir_path, $comment);
		if(!@is_writable($dir_path)){
			$reason = "Directory '{DIR}' is not writable";
			static::throwException($reason, $comment, array("DIR" => $dir_path));
		}
		
	}

	/**
	 * @param string $dir_path
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function isNotWritableDirectory($dir_path, $comment = ""){
		static::isString($dir_path, $comment);
		if(file_exists($dir_path) && @is_dir($dir_path) && @is_writable($dir_path)){
			$reason = "Directory '{DIR}' exists and is writable";
			static::throwException($reason, $comment, array("DIR" => $dir_path));
		}
		
	}

	/**
	 * @param mixed $value
	 * @param array $array
	 * @param bool $strict_compare [optional]
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function arrayContains($value, array $array, $strict_compare = false, $comment = ""){
		if(!in_array($value, $array, $strict_compare)){
			static::throwException("Value not found in array", $comment);
		}
	}

	/**
	 * @param mixed $value
	 * @param array $array
	 * @param bool $strict_compare [optional]
	 * @param string $comment [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * 
	 */
	public static function arrayNotContains($value, array $array, $strict_compare = false, $comment = ""){
		if(in_array($value, $array, $strict_compare)){
			static::throwException("Value should NOT be in array", $comment);
		}
		
	}
}