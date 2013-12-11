<?php
namespace Et;
class Debug_Assert {

	/**
	 * @var Debug_Assert
	 */
	protected static $instance;

	/**
	 * @return Debug_Assert
	 */
	public static function getInstance(){
		if(!self::$instance){
			self::$instance = new static();
		}
		return self::$instance;
	}

	/**
	 * @param string $reason
	 * @param string $comment
	 * @param int $backtrace_offset
	 * @param array $reason_data [optional]
	 *
	 * @throws Debug_Assert_Exception
	 */
	protected function throwException($reason, $comment, $backtrace_offset, array $reason_data = array()){
		if(!$reason){
			return;
		}

		foreach($reason_data as $k => $v){
			$reason = str_replace("{{$k}}", (string)$v, $reason);
		}

		if(!is_scalar($comment)){
			$comment = "";
		}
		$comment = trim($comment);

		et_require('Debug_Assert_Exception');
		throw new Debug_Assert_Exception(
			$comment ? $comment : $reason,
			$backtrace_offset,
			$reason
		);
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isTrue($value, $comment = "", $backtrace_offset = 2){
		if($value !== true){
			$this->throwException("Value should be TRUE (boolean)", $comment, $backtrace_offset);
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNotTrue($value, $comment = "", $backtrace_offset = 2){
		if($value === true){
			$this->throwException("Value should NOT be TRUE", $comment, $backtrace_offset);
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isFalse($value, $comment = "", $backtrace_offset = 2){
		if($value !== false){
			$this->throwException("Value should be FALSE", $comment, $backtrace_offset);
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNotFalse($value, $comment = "", $backtrace_offset = 2){
		if($value === false){
			$this->throwException("Value should NOT be FALSE", $comment, $backtrace_offset);
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isEmpty($value, $comment = "", $backtrace_offset = 2){
		if(!empty($value)){
			$this->throwException("Value should be empty", $comment, $backtrace_offset);
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param bool $check_mx [optional]
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optiona]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isEmail($value, $check_mx = false, $comment = "", $backtrace_offset = 2){
		$this->isString($value, $comment, $backtrace_offset + 1);
		if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
			$this->throwException("Invalid e-mail format", $comment, $backtrace_offset);
		}

		if($check_mx){
			list($domain) = explode("@", $value);
			if(!getmxrr($domain, $mx_hosts)){
				$this->throwException("Invalid e-mail - DNS MX record not found", $comment, $backtrace_offset);
			}
		}

		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optiona]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isValidEmail($value, $comment = "", $backtrace_offset = 2){
		$this->isEmail($value, true, $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param bool $path_required [optional]
	 * @param bool $query_allowed [optional]
	 * @param bool $hash_allowed [optional]
	 * @param string $comment [optional]
	 * @param array|null $schemes_allowed [optional] Default: array('http', 'https')
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isURL($value, $path_required = true, $query_allowed = true, $hash_allowed = true, $comment = "", array $schemes_allowed = null, $backtrace_offset = 2){

		$this->isString($value, $comment, $backtrace_offset + 1);
		if($path_required){
			$res = filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);
		} else {
			$res = filter_var($value, FILTER_VALIDATE_URL);
		}

		if(!$res){
			$this->throwException("Invalid URL format", $comment, $backtrace_offset);
		}

		if($schemes_allowed === null){
			$schemes_allowed = array("http", "https");
		}

		if($schemes_allowed){
			foreach($schemes_allowed as &$scheme){
				$scheme = preg_quote($scheme);
			}

			if(!preg_match('~^('.implode("|", $schemes_allowed).'):~', $value)){
				$this->throwException("Invalid URL scheme, allowed: {SCHEMES}", $comment, $backtrace_offset, array("SCHEMES" => implode(", ", $schemes_allowed)));
			}
		}

		if(!$query_allowed && strpos($value, "?") !== false){
			$this->throwException("URL query part (?) not allowed", $comment, $backtrace_offset);
		}

		if(!$hash_allowed && strpos($value, "#") !== false){
			$this->throwException("URL hash part (#) not allowed", $comment, $backtrace_offset);
		}

		return $this;
	}


	/**
	 * @param mixed $value
	 * @param bool $path_required [optional]
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isClearURL($value, $path_required = true, $comment = "", $backtrace_offset = 2){
		$this->isURL($value, $path_required, false, false, $comment, null, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param bool $query_allowed [optional]
	 * @param bool $hash_allowed [optional]
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isURI($value, $query_allowed = true, $hash_allowed = true, $comment = "", $backtrace_offset = 2){
		$this->isString($value, $comment, $backtrace_offset + 1);
		if(!isset($value[0]) || $value[0] != '/' || !filter_var("http://localhost{$value}", FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)){
			$this->throwException("Invalid URI format", $comment, $backtrace_offset);
		}

		if(!$query_allowed && strpos($value, "?") !== false){
			$this->throwException("URI query part (?) not allowed", $comment, $backtrace_offset);
		}

		if(!$hash_allowed && strpos($value, "#") !== false){
			$this->throwException("URI hash part (#) not allowed", $comment, $backtrace_offset);
		}
		
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isClearURI($value, $comment = "", $backtrace_offset = 2){
		$this->isURI($value, false, false, $comment, $backtrace_offset + 1);
		return $this;
	}


	/**
	 * @param mixed $value
	 * @param bool $IPv4_only [optional]
	 * @param bool $IPv6_only [optional]
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isIP($value, $IPv4_only = false, $IPv6_only = false, $comment = "", $backtrace_offset = 2){
		$this->isString($value, $comment, $backtrace_offset + 1);
		$IPv4_only = (bool)$IPv4_only;
		$IPv6_only = (bool)$IPv6_only;

		if($IPv4_only != $IPv6_only){
			if($IPv4_only){
				if(!filter_var($value, FILTER_VALIDATE_IP. FILTER_FLAG_IPV4)){
					$this->throwException("Invalid IPv4 IP", $comment, $backtrace_offset);
				}
			} else {
				if(!filter_var($value, FILTER_VALIDATE_IP. FILTER_FLAG_IPV6)){
					$this->throwException("Invalid IPv6 IP", $comment, $backtrace_offset);
				}
			}
		} else {
			if(!filter_var($value, FILTER_VALIDATE_IP)){
				$this->throwException("Invalid IP", $comment, $backtrace_offset);
			}
		}

		return $this;
	}



	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNotEmpty($value, $comment = "", $backtrace_offset = 2){
		if(empty($value)){
			$this->throwException("Value should NOT be empty", $comment, $backtrace_offset);
		}

		return $this;
	}

	/**
	 * @param mixed $value
	 * @param mixed $equals_to
	 * @param bool $strict
	 *
	 * @return bool
	 */
	protected function valueEquals(&$value, &$equals_to, $strict){
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
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	function equalValues($value, $equals_to, $strict_compare = false, $comment = "", $backtrace_offset = 2){
		if(!$this->valueEquals($value, $equals_to, $strict_compare)){
			$this->throwException(
				"Values are not equal",
				$comment,
				$backtrace_offset
			);
		}

		return $this;
	}

	/**
	 * @param mixed $value
	 * @param mixed $equals_to
	 * @param bool $strict_compare [optional]
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	function notEqualValue($value, $equals_to, $strict_compare = false, $comment = "", $backtrace_offset = 2){
		if(!$this->valueEquals($value, $equals_to, $strict_compare)){
			$this->throwException(
				"Values should NOT be equal",
				$comment,
				$backtrace_offset
			);
		}

		return $this;
	}

	/**
	 * @param mixed $value
	 * @param mixed $equals_to
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	function sameValues($value, $equals_to, $comment = "", $backtrace_offset = 2){
		if(!$this->valueEquals($value, $equals_to, true)){
			$this->throwException(
				"Values are not same",
				$comment,
				$backtrace_offset
			);
		}

		return $this;
	}

	/**
	 * @param mixed $value
	 * @param mixed $equals_to
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	function notSame($value, $equals_to, $comment = "", $backtrace_offset = 2){
		if(!$this->valueEquals($value, $equals_to, true)){
			$this->throwException(
				"Values should NOT be same",
				$comment,
				$backtrace_offset
			);
		}

		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string|array $allowed_type_or_types
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function hasType($value, $allowed_type_or_types, $comment = "", $backtrace_offset = 2){
		if(!is_array($allowed_type_or_types)){
			$allowed_type_or_types = array($allowed_type_or_types);
		}

		if(!in_array(gettype($value), $allowed_type_or_types)){
			$reason = "Value type should be {ALLOWED_TYPES}, not {GIVEN_TYPE}";
			$this->throwException(
				$reason,
				$comment,
				$backtrace_offset,
				array(
					"ALLOWED_TYPES" => implode(" or ", $allowed_type_or_types),
					"GIVEN_TYPE" => gettype($value)
				)
			);
		}

		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string|array $disallowed_type_or_types
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function hasNotType($value, $disallowed_type_or_types, $comment = "", $backtrace_offset = 2){
		if(!is_array($disallowed_type_or_types)){
			$disallowed_type_or_types = array($disallowed_type_or_types);
		}

		if(!in_array(gettype($value), $disallowed_type_or_types)){
			$reason = "Value type should NOT be {DISALLOWED_TYPES}";
			$this->throwException(
				$reason,
				$comment,
				$backtrace_offset,
				array(
					"DISALLOWED_TYPES" => implode(" or ", $disallowed_type_or_types)
				)
			);
		}

		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isBool($value, $comment = "", $backtrace_offset = 2){
		$this->hasType($value, "boolean", $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNotBool($value, $comment = "", $backtrace_offset = 2){
		$this->hasNotType($value, "boolean", $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isScalar($value, $comment = "", $backtrace_offset = 2){
		if(!is_scalar($value)){
			$this->throwException("Value should be scalar, not {OBJECT_CLASS}", $comment, $backtrace_offset, array("TYPE" => gettype($value)));
		}
		return $this;

	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNotScalar($value, $comment = "", $backtrace_offset = 2){
		if(is_scalar($value)){
			$this->throwException("Value should NOT be scalar", $comment, $backtrace_offset);
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isScalarOrNULL($value, $comment = "", $backtrace_offset = 2){
		if(!is_scalar($value) && $value !== null){
			$reason = "Value should be scalar or NULL, not {OBJECT_CLASS}";
			$this->throwException($reason, $comment, $backtrace_offset, array("TYPE" => gettype($value)));
		}

		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNotScalarOrNULL($value, $comment = "", $backtrace_offset = 2){
		if(is_scalar($value) || $value === null){
			$this->throwException("Value should NOT be scalar or NULL", $comment, $backtrace_offset);
		}

		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNumber($value, $comment = "", $backtrace_offset = 2){
		if(!is_numeric($value)){
			$reason = "Value should be number, not {TYPE}";
			$this->throwException($reason, $comment, $backtrace_offset, array("TYPE" => gettype($value)));
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNotNumber($value, $comment = "", $backtrace_offset = 2){
		if(is_numeric($value)){
			$this->throwException("Value should NOT be numeric", $comment, $backtrace_offset);
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isInteger($value, $comment = "", $backtrace_offset = 2){
		$this->hasType($value, "integer", $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNotInteger($value, $comment = "", $backtrace_offset = 2){
		$this->hasNotType($value, "integer", $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isFloat($value, $comment = "", $backtrace_offset = 2){
		$this->hasType($value, "double", $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNotFloat($value, $comment = "", $backtrace_offset = 2){
		$this->hasNotType($value, "double", $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNull($value, $comment = "", $backtrace_offset = 2){
		$this->hasType($value, "NULL", $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNotNull($value, $comment = "", $backtrace_offset = 2){
		$this->hasNotType($value, "NULL", $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isString($value, $comment = "", $backtrace_offset = 2){
		$this->hasType($value, "string", $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNotString($value, $comment = "", $backtrace_offset = 2){
		$this->hasNotType($value, "string", $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isStringOrNumber($value, $comment = "", $backtrace_offset = 2){
		$this->hasType($value, array("string", "integer", "double"), $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNotStringOrNumber($value, $comment = "", $backtrace_offset = 2){
		$this->hasNotType($value, array("string", "integer", "double"), $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isArray($value, $comment = "", $backtrace_offset = 2){
		$this->hasType($value, "array", $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isLinearArray($value, $comment = "", $backtrace_offset = 2){
		$this->isArray($value, $comment, $backtrace_offset + 1);
		if(!$value){
			return $this;
		}

		if(array_keys($value) !== range(0, count($value) - 1, 1)){
			$this->throwException("Value is not linear array", $comment, $backtrace_offset);
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param array|string $allowed_value_type_or_types
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isArrayOf($value, $allowed_value_type_or_types, $comment = "", $backtrace_offset = 2){
		$this->isArray($value, $comment, $backtrace_offset + 1);
		if(!$value){
			return $this;
		}

		if(!is_array($allowed_value_type_or_types)){
			$allowed_value_type_or_types = array($allowed_value_type_or_types);
		}

		foreach($value as $k => $v){
			if(!in_array(gettype($v), $allowed_value_type_or_types)){
				$reason = "Value type at key '{KEY}' should be {ALLOWED_TYPES}, not {GIVEN_TYPE}";
				$this->throwException(
					$reason,
					$comment,
					$backtrace_offset,
					array(
						"ALLOWED_TYPES" => implode(" or ", $allowed_value_type_or_types),
						"GIVEN_TYPE" => gettype($v),
						"KEY" => $k
					)
				);
			}
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isArrayOfArrays($value, $comment = "", $backtrace_offset = 2){
		$this->isArrayOf($value, 'array', $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isArrayOfStrings($value, $comment = "", $backtrace_offset = 2){
		$this->isArrayOf($value, 'string', $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isArrayOfNumbers($value, $comment = "", $backtrace_offset = 2){
		$this->isArrayOf($value, array('integer', 'double'), $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isArrayOfStringsOrNumbers($value, $comment = "", $backtrace_offset = 2){
		$this->isArrayOf($value, array('string', 'integer', 'double'), $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isArrayOfScalars($value, $comment = "", $backtrace_offset = 2){
		$this->isArrayOf($value, array('string', 'integer', 'double', 'boolean'), $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed|array $value
	 * @param string $instances_of_class
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isArrayOfInstances($value, $instances_of_class, $comment = "", $backtrace_offset = 2){
		$this->isArray($value, $comment, $backtrace_offset + 1);
		$this->classExists($instances_of_class, $comment, $backtrace_offset + 1);
		foreach($value as $k => $v){
			if(!($v instanceof $instances_of_class)){
				$this->throwException("Value with key {KEY} is not instance of {CLASS}", $comment, $backtrace_offset, array("KEY" => $k, "CLASS" => $instances_of_class));
			}
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNotArray($value, $comment = "", $backtrace_offset = 2){
		$this->hasNotType($value, "array", $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isObject($value, $comment = "", $backtrace_offset = 2){
		$this->hasType($value, "object", $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNotObject($value, $comment = "", $backtrace_offset = 2){
		$this->hasNotType($value, "object", $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param string|Locales_Locale $value
	 * @param bool $instance_only [optional]
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isLocale($value, $instance_only = false, $comment = "", $backtrace_offset = 2){
		if($value instanceof Locales_Locale){
			return $this;
		}

		if($instance_only){
			$this->throwException("Locale instance expected", $comment, $backtrace_offset);
		}

		try {
			Locales::checkLocale($value);
		} catch(Locales_Exception $e){
			$this->throwException("Invalid locale", $comment, $backtrace_offset);
		}
		return $this;
	}

	/**
	 * @param string $date
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isDate($date, $comment = "", $backtrace_offset = 2){
		if($date instanceof Locales_DateTime){
			return $this;
		}
		$this->isStringMatching($date, '~^\d{4}-\d{2}-\d{2}$~', $comment, $backtrace_offset+1);
		if(@strtotime($date) === false){
			$this->throwException("Invalid date", $comment, $backtrace_offset);
		}
		return $this;
	}

	/**
	 * @param string $time
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isTime($time, $comment = "", $backtrace_offset = 2){
		if($time instanceof Locales_DateTime){
			return $this;
		}
		$this->isStringMatching($time, '~^\d{2}:\d{2}:\d{2}$~', $comment, $backtrace_offset+1);
		$time_ts = @strtotime("2020-01-01 {$time}");
		if($time_ts === false || date("Y-m-d", $time_ts) !== "2020-01-01"){
			$this->throwException("Invalid time", $comment, $backtrace_offset);
		}
		return $this;
	}

	/**
	 * @param string $date_time
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isDateTime($date_time, $comment = "", $backtrace_offset = 2){
		$this->isString($date_time, $comment, $backtrace_offset + 1);

		$this->isStringMatching($date_time, '~^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}$~', $comment, $backtrace_offset+1);
		$date = substr($date_time, 0, 10);
		if(@strtotime($date) === false){
			$this->throwException("Invalid date", $comment, $backtrace_offset);
		}

		$time = substr($date_time, 11, 8);
		$time_ts = @strtotime("2020-01-01 {$time}");
		if($time_ts === false || date("Y-m-d", $time_ts) !== "2020-01-01"){
			$this->throwException("Invalid time", $comment, $backtrace_offset);
		}

		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $instance_of
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isInstanceOf($value, $instance_of, $comment = "", $backtrace_offset = 2){
		if(!($value instanceof $instance_of)){
			if(is_object($value)){
				$reason = "Value (object of class {OBJECT_CLASS}) is not instance of {INSTANCE_OF}";
				$reason_data = array(
					"OBJECT_CLASS" => get_class($value),
					"INSTANCE_OF" => $instance_of
				);
			} else {
				$reason = "Value (type {TYPE}) is not instance of {$instance_of}";
				$reason_data = array(
					"TYPE" => gettype($value),
					"INSTANCE_OF" => $instance_of
				);
			}
			$this->throwException($reason, $comment, $backtrace_offset, $reason_data);
		}
		return $this;
	}

	/**
	 * @param mixed|object $value
	 * @param string $instance_of
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNotInstanceOf($value, $instance_of, $comment = "", $backtrace_offset = 2){
		/** @var $value object */
		if($value instanceof $instance_of){
			$reason = "Value (object of class {OBJECT_CLASS}) should NOT be instance of {INSTANCE_OF}";
			$reason_data = array(
				"OBJECT_CLASS" => get_class($value),
				"INSTANCE_OF" => $instance_of
			);
			$this->throwException($reason, $comment, $backtrace_offset, $reason_data);
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param int|float $greater_than
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isGreaterThan($value, $greater_than, $comment = "", $backtrace_offset = 2){
		$this->isNumber($value, $comment, $backtrace_offset + 1);
		if($value <= $greater_than){
			$reason = "Value should be greater than {GREATER_THAN}, {VALUE} is not";
			$reason_data = array(
				"GREATER_THAN" => $greater_than,
				"VALUE" => $value
			);
			$this->throwException($reason, $comment, $backtrace_offset, $reason_data);
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param int|float $greater_or_equal_than
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isGreaterOrEqualThan($value, $greater_or_equal_than, $comment = "", $backtrace_offset = 2){
		$this->isNumber($value, $comment, $backtrace_offset + 1);
		if($value < $greater_or_equal_than){
			$reason = "Value should be greater or equal than {GREATER_THAN}, {VALUE} is not";
			$reason_data = array(
				"GREATER_THAN" => $greater_or_equal_than,
				"VALUE" => $value
			);
			$this->throwException($reason, $comment, $backtrace_offset, $reason_data);
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param int|float $lower_than
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isLowerThan($value, $lower_than, $comment = "", $backtrace_offset = 2){
		$this->isNumber($value, $comment, $backtrace_offset + 1);
		if($value >= $lower_than){
			$reason = "Value should be lower than {LOWER_THAN}, {VALUE} is not";
			$reason_data = array(
				"LOWER_THAN" => $lower_than,
				"VALUE" => $value
			);
			$this->throwException($reason, $comment, $backtrace_offset, $reason_data);
		}

		return $this;
	}

	/**
	 * @param mixed $value
	 * @param int|float $lower_or_equal_than
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isLowerOrEqualThan($value, $lower_or_equal_than, $comment = "", $backtrace_offset = 2){
		$this->isNumber($value, $comment, $backtrace_offset + 1);
		if($value > $lower_or_equal_than){
			$reason = "Value should be lower or equal than {LOWER_THAN}, {VALUE} is not";
			$reason_data = array(
				"LOWER_THAN" => $lower_or_equal_than,
				"VALUE" => $value
			);
			$this->throwException($reason, $comment, $backtrace_offset, $reason_data);
		}
		return $this;
	}

	/**
	 * @param array|mixed $value
	 * @param string $key
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function arrayHasKey($value, $key, $comment = "", $backtrace_offset = 2){
		$this->isArray($value, $comment, $backtrace_offset + 1);
		if(!array_key_exists($key, $value)){
			$reason = "Key '{KEY}' missing in array";
			$this->throwException($reason, $comment, $backtrace_offset, array("KEY" => $key));
		}
		return $this;
	}

	/**
	 * @param array|mixed $value
	 * @param string $key
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function arrayHasNotKey($value, $key, $comment = "", $backtrace_offset = 2){
		$this->isArray($value, $comment, $backtrace_offset);
		if(array_key_exists($key, $value)){
			$reason = "Key '{KEY}' should not exist in array";
			$this->throwException($reason, $comment, $backtrace_offset, array("KEY" => $key));
		}
		return $this;
	}

	/**
	 * @param array|mixed $value
	 * @param array $keys
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function arrayHasKeys($value, array $keys, $comment = "", $backtrace_offset = 2){
		$this->isArray($value, $comment, $backtrace_offset);
		if(!$keys){
			return $this;
		}

		$missing_keys = array();
		foreach($keys as $key){
			if(!array_key_exists($key, $value)){
				$missing_keys[] = $key;
			}
		}

		if($missing_keys){
			$reason = "Keys {KEYS} missing in array";
			$reason_data = array(
				"KEYS" => "'" . implode("', '", $missing_keys) . "'"
			);
			$this->throwException($reason, $comment, $backtrace_offset, $reason_data);
		}

		return $this;
	}

	/**
	 * @param array|mixed $value
	 * @param array $keys
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function arrayHasNotKeys($value, array $keys, $comment = "", $backtrace_offset = 2){
		$this->isArray($value, $comment, $backtrace_offset);
		if(!$keys){
			return $this;
		}

		$existing_keys = array();
		foreach($keys as $key){
			if(array_key_exists($key, $value)){
				$existing_keys[] = $key;
			}
		}

		if($existing_keys){
			$reason = "Keys {KEYS} should not exist in array";
			$reason_data = array(
				"KEYS" => "'" . implode("', '", $existing_keys) . "'"
			);
			$this->throwException($reason, $comment, $backtrace_offset, $reason_data);
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $regex_pattern
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 * @param string $regex_modifiers [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isStringMatching($value, $regex_pattern, $comment = "", $backtrace_offset = 2, $regex_modifiers = ""){
		$this->isStringOrNumber($value, $comment, $backtrace_offset);
		if(!preg_match("~{$regex_pattern}~{$regex_modifiers}", $value)){
			$reason = "Value does not match pattern {PATTERN}";
			$this->throwException($reason, $comment, $backtrace_offset, array("PATTERN" => $regex_pattern));
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $regex_pattern
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 * @param string $regex_modifiers [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isStringNotMatching($value, $regex_pattern, $comment = "", $backtrace_offset = 2, $regex_modifiers = ""){
		$this->isStringOrNumber($value, $comment, $backtrace_offset);
		if(preg_match("~{$regex_pattern}~{$regex_modifiers}", $value)){
			$reason = "Value should NOT match pattern {PATTERN}";
			$this->throwException($reason, $comment, $backtrace_offset, array("PATTERN" => $regex_pattern));
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isVariableName($value, $comment = "", $backtrace_offset = 2){
		$this->isStringMatching($value, '^\w+$', $comment, $backtrace_offset + 1, "i");
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isIdentifier($value, $comment = "", $backtrace_offset = 2){
		$this->isStringMatching($value, '^\w+(-\w+)*$', $comment, $backtrace_offset + 1, "i");
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param bool $check_if_exists [optional]
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isClassName($value, $check_if_exists = false, $comment = "", $backtrace_offset = 2){
		if(!is_string($value) || !preg_match('~^\\\\?\w+(\\\\\w+)*$~', $value)){
			$this->throwException("Value ".(is_string($value) ? "'{$value}'" : "")." is not valid class name", $comment, $backtrace_offset);
		}

		if($check_if_exists && !class_exists($value)){
			$reason = "Class {CLASS_NAME} not exists";
			$this->throwException($reason, $comment, $backtrace_offset, array("CLASS_NAME" => $value));
		}

		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function classExists($value, $comment = "", $backtrace_offset = 2){
		$this->isClassName($value, true, $comment, $backtrace_offset + 1);
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $parent_class_name
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function classIsSubclassOf($value, $parent_class_name, $comment = "", $backtrace_offset = 2){
		$this->isClassName($value, $comment, $backtrace_offset + 1);
		$this->isClassName($parent_class_name, $comment, $backtrace_offset + 1);
		if(!is_subclass_of($value, $parent_class_name)){
			$reason = "Class {CLASS_NAME} is not subclass of {PARENT_CLASS}";
			$this->throwException($reason, $comment, $backtrace_offset, array("CLASS_NAME" => $value, "PARENT_CLASS" => $parent_class_name));
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string $parent_class_name
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function classIsSameClassOrSubclassOf($value, $parent_class_name, $comment = "", $backtrace_offset = 2){
		$this->isClassName($value, true, $comment, $backtrace_offset + 1);
		$this->isClassName($parent_class_name, true, $comment, $backtrace_offset + 1);
		if(!is_a($value, $parent_class_name, true)){
			$reason = "Class {CLASS_NAME} is not {PARENT_CLASS} neither its subclass";
			$this->throwException($reason, $comment, $backtrace_offset, array("CLASS_NAME" => $value, "PARENT_CLASS" => $parent_class_name));
		}
		return $this;
	}


	/**
	 * @param string $file_path
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function fileExists($file_path, $comment = "", $backtrace_offset = 2){
		$this->isString($file_path, $comment, $backtrace_offset + 1);
		if(!file_exists($file_path) || !@is_file($file_path)){
			$reason = "File '{FILE}' does not exist";
			$this->throwException($reason, $comment, $backtrace_offset, array("FILE" => $file_path));
		}
		return $this;
	}

	/**
	 * @param string $file_path
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function fileNotExists($file_path, $comment = "", $backtrace_offset = 2){
		$this->isString($file_path, $comment, $backtrace_offset + 1);
		if(file_exists($file_path) && @is_file($file_path)){
			$reason = "File '{FILE}' should NOT exist";
			$this->throwException($reason, $comment, $backtrace_offset, array("FILE" => $file_path));
		}
		return $this;
	}

	/**
	 * @param string $file_path
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function fileIsReadable($file_path, $comment = "", $backtrace_offset = 2){
		$this->fileExists($file_path, $comment, $backtrace_offset + 1);
		if(!@is_readable($file_path)){
			$reason = "File '{FILE}' is not readable";
			$this->throwException($reason, $comment, $backtrace_offset, array("FILE" => $file_path));
		}
		return $this;
	}

	/**
	 * @param string $file_path
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function fileIsNotReadable($file_path, $comment = "", $backtrace_offset = 2){
		$this->isString($file_path, $comment, $backtrace_offset + 1);
		if(file_exists($file_path) && @is_file($file_path) && @is_readable($file_path)){
			$reason = "File '{FILE}' should NOT be readable";
			$this->throwException($reason, $comment, $backtrace_offset, array("FILE" => $file_path));
		}
		return $this;
	}

	/**
	 * @param string $file_path
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function fileIsWritable($file_path, $comment = "", $backtrace_offset = 2){
		$this->fileExists($file_path, $comment, $backtrace_offset + 1);
		if(!@is_writable($file_path)){
			$reason = "File '{FILE}' is not writable";
			$this->throwException($reason, $comment, $backtrace_offset, array("FILE" => $file_path));
		}
		return $this;
	}

	/**
	 * @param string $file_path
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function fileIsNotWritable($file_path, $comment = "", $backtrace_offset = 2){
		$this->isString($file_path, $comment, $backtrace_offset + 1);
		if(file_exists($file_path) && @is_file($file_path) && @is_readable($file_path)){
			$reason = "File '{FILE}' should NOT be writable";
			$this->throwException($reason, $comment, $backtrace_offset, array("FILE" => $file_path));
		}
		return $this;
	}


	/**
	 * @param string $dir_path
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function dirExists($dir_path, $comment = "", $backtrace_offset = 2){
		$this->isString($dir_path, $comment, $backtrace_offset + 1);
		if(!file_exists($dir_path) || !@is_dir($dir_path)){
			$reason = "Directory '{DIR}' does not exist";
			$this->throwException($reason, $comment, $backtrace_offset, array("DIR" => $dir_path));
		}
		return $this;
	}

	/**
	 * @param string $dir_path
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function dirNotExists($dir_path, $comment = "", $backtrace_offset = 2){
		$this->isString($dir_path, $comment, $backtrace_offset + 1);
		if(file_exists($dir_path) && @is_dir($dir_path)){
			$reason = "Directory '{DIR}' should NOT exist";
			$this->throwException($reason, $comment, $backtrace_offset, array("DIR" => $dir_path));
		}
		return $this;
	}

	/**
	 * @param string $dir_path
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function dirIsReadable($dir_path, $comment = "", $backtrace_offset = 2){
		$this->dirExists($dir_path, $comment, $backtrace_offset);
		if(!@is_readable($dir_path)){
			$reason = "Directory '{DIR}' is not readable";
			$this->throwException($reason, $comment, $backtrace_offset, array("DIR" => $dir_path));
		}
		return $this;
	}

	/**
	 * @param string $dir_path
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function dirIsNotReadable($dir_path, $comment = "", $backtrace_offset = 2){
		$this->isString($dir_path, $comment, $backtrace_offset);
		if(file_exists($dir_path) && @is_dir($dir_path) && @is_readable($dir_path)){
			$reason = "Directory '{DIR}' should NOT be readable";
			$this->throwException($reason, $comment, $backtrace_offset, array("DIR" => $dir_path));
		}
		return $this;
	}

	/**
	 * @param string $dir_path
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function dirIsWritable($dir_path, $comment = "", $backtrace_offset = 2){
		$this->dirExists($dir_path, $comment, $backtrace_offset);
		if(!@is_writable($dir_path)){
			$reason = "Directory '{DIR}' is not writable";
			$this->throwException($reason, $comment, $backtrace_offset, array("DIR" => $dir_path));
		}
		return $this;
	}

	/**
	 * @param string $dir_path
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function dirIsNotWritable($dir_path, $comment = "", $backtrace_offset = 2){
		$this->isString($dir_path, $comment, $backtrace_offset);
		if(file_exists($dir_path) && @is_dir($dir_path) && @is_writable($dir_path)){
			$reason = "Directory '{DIR}' should NOT be writable";
			$this->throwException($reason, $comment, $backtrace_offset, array("DIR" => $dir_path));
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param array $array
	 * @param bool $strict_compare [optional]
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isInArray($value, array $array, $strict_compare = false, $comment = "", $backtrace_offset = 2){
		if(!in_array($value, $array, $strict_compare)){
			$this->throwException("Value not found in array", $comment, $backtrace_offset);
		}
		return $this;
	}

	/**
	 * @param mixed $value
	 * @param array $array
	 * @param bool $strict_compare [optional]
	 * @param string $comment [optional]
	 * @param int $backtrace_offset [optional]
	 *
	 * @throws Debug_Assert_Exception
	 * @return Debug_Assert
	 */
	public function isNotInArray($value, array $array, $strict_compare = false, $comment = "", $backtrace_offset = 2){
		if(in_array($value, $array, $strict_compare)){
			$this->throwException("Value should NOT be in array", $comment, $backtrace_offset);
		}
		return $this;
	}
}