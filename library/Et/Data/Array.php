<?php
namespace Et;
et_require("Object");
class Data_Array extends Object implements \ArrayAccess, \Countable, \Iterator, \JsonSerializable {

	const DEFAULT_PATH_SEPARATOR = "/";
	const QUOTE_DOUBLE = ENT_COMPAT;
	const QUOTE_SINGLE = ENT_QUOTES;
	const QUOTE_NONE = ENT_NOQUOTES;

	/**
	 * @var string
	 */
	protected $path_separator = self::DEFAULT_PATH_SEPARATOR;

	/**
	 * Input data
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Create array wrapper
	 *
	 * @param array|null $data [optional]
	 */
	public function __construct(array $data = null) {
		if($data){
			$this->setData($data);
		}
	}

	/**
	 * @param string $path_separator
	 */
	public function setPathSeparator($path_separator) {
		$this->path_separator = substr($path_separator, 0, 1);
	}

	/**
	 * @return string
	 */
	public function getPathSeparator() {
		return $this->path_separator;
	}
	

	/**
	 * @param Data_Array_Source_Abstract $source
	 * @throws Data_Array_Exception
	 * @return Data_Array
	 */
	public static function createFromSource(Data_Array_Source_Abstract $source){
		/** @var $array Data_Array */
		$array = new static();
		$array->loadFromSource($source);
		return $array;
	}

	/**
	 * @param Data_Array_Source_Abstract $source
	 * @throws Data_Array_Exception
	 */
	public function loadFromSource(Data_Array_Source_Abstract $source){
		try {
			$data = $source->loadData();	
		} catch(Data_Array_Source_Exception $e){
			throw new Data_Array_Exception(
				"Failed to load array data source - {$e->getMessage()}",
				Data_Array_Exception::CODE_FAILED_TO_LOAD_DATA,
				null,
				$e
			);
		}
		
		$this->setData($data);
	}

	/**
	 * @param Data_Array_Source_Abstract $source
	 * @throws Data_Array_Exception
	 */
	public function storeToSource(Data_Array_Source_Abstract $source){
		try {
			$source->storeData($this);
		} catch(Data_Array_Source_Exception $e){
			throw new Data_Array_Exception(
				"Failed to load array data source - {$e->getMessage()}",
				Data_Array_Exception::CODE_FAILED_TO_LOAD_DATA,
				null,
				$e
			);
		}
	}
	

	/**
	 * Merge current data with another
	 *
	 * @param array $data
	 */
	public function mergeData(array $data){
		if(!$this->data){
			$this->data = $data;
		} else {
			$this->data = array_merge($this->data, $data);
		}
	}

	/**
	 * Overwrite current data with new
	 *
	 * @param array $data
	 */
	public function setData(array $data){
		$this->data = $data;
	}

	/**
	 * Remove all data
	 */
	public function clearData(){
		$this->data = array();
	}

	/**
	 * @return bool
	 */
	function hasData(){
		return (bool)$this->data;
	}

	/**
	 * @return array
	 */
	function getData(){
		return $this->data;
	}

	/**
	 * @return array [reference]
	 */
	function &getDataReference(){
		return $this->data;
	}

	/**
	 * Get data array keys
	 *
	 * @return array
	 */
	public function getDataKeys(){
		return array_keys($this->data);
	}
	
	/**
	 * Is data/path value set?
	 *
	 * @param string $path
	 * @return bool
	 */
	public function getValueExists($path){
		$this->getReference($path, $found);
		return $found;
	}

	/**
	 * Is data value a string?
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function isValueString($path){
		return is_string($this->getRawValue($path));
	}

	/**
	 * @param string $path
	 * @return bool
	 */
	public function isValueScalar($path){
		$value = $this->getReference($path, $found);
		if(!$found){
			return false;
		}
		return $value === null || is_scalar($value);
	}

	/**
	 * Is data value boolean?
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function isValueBool($path){
		return is_bool($this->getRawValue($path));
	}

	/**
	 * Is data value integer?
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function isNumericValue($path){
		return is_numeric($this->getRawValue($path));
	}

	/**
	 * Is data value integer?
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function isValueInt($path){
		return is_int($this->getRawValue($path));
	}

	/**
	 * Is data value float?
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function isValueFloat($path){
		return is_float($this->getRawValue($path));
	}

	/**
	 * Is data value array?
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function isValueArray($path){
		return is_array($this->getRawValue($path));
	}

	/**
	 * Is data value object?
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function isValueObject($path){
		return is_object($this->getRawValue($path));
	}

	/**
	 * Is data value callable?
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function isValueCallable($path){
		return is_callable($this->getRawValue($path));
	}

	/**
	 * Is data value instance of $class_name?
	 *
	 * @param string $path
	 * @param string $class_name
	 *
	 * @return bool
	 */
	public function isValueInstanceOf($path, $class_name){
		return $this->getRawValue($path) instanceof $class_name;
	}

	/**
	 * Get raw value from data/path
	 *
	 * If $path begins with '/' character, it's considered to be path to nested data
	 * Example:
	 * Path "/a/b/c" points to $this->data["a"]["b"]["c"]
	 *
	 * @param string $path
	 * @param mixed $default_value [optional] Default: NULL
	 * @param bool $found [reference]
	 * 
	 * @return mixed
	 */
	public function getRawValue($path, $default_value = null, &$found = null){
		$value = $this->getReference($path, $found);
		if(!$found){
			return $default_value;
		}
		return $value;
	}

	/**
	 * Get reference to given data by key/path
	 *
	 * @param string $path
	 * @param bool $found [reference][optional]
	 * @return mixed|null
	 */
	public function &getReference($path, &$found = false){
		$found = false;
		if(!is_numeric($path)){
			$path = (string)$path;
		}

		if($path && $path[0] === $this->path_separator){
			return $this->getPathReference($path, $found);
		}

		if(isset($this->data[$path])){
			$found = true;
			return $this->data[$path];
		}

		$not_found = null;
		return $not_found;
		
	}

	/**
	 * Get reference to path
	 *
	 * @param string $path
	 * @param bool $found [reference]
	 * @return mixed|null
	 */
	protected function &getPathReference($path, &$found = false){

		$found = false;
		$parts = explode($this->path_separator, trim($path, $this->path_separator));

		$null = null;
		if(!$parts){
			return $null;
		}

		$last_part = array_pop($parts);
		$pointer = &$this->data;
		foreach($parts as $part){

			if(!is_array($pointer)){
				return $null;
			}

			if(!isset($pointer[$part])){
				return $null;
			}

			$pointer = &$pointer[$part];
		}


		if(!is_array($pointer)){
			return $null;
		}

		if(!isset($pointer[$last_part])){
			return $null;
		}

		$found = true;
		return $pointer[$last_part];
	}

	/**
	 * Set data value by given key/path
	 *
	 * @param string $path
	 * @param mixed $value
	 * @return bool
	 */
	public function setValue($path, $value){
		if(!is_numeric($path)){
			$path = (string)$path;
		}

		if($path && $path[0] === $this->path_separator){
			return $this->setPath($path, $value);
		} else {
			$this->data[$path] = $value;
			return true;
		}
	}

	/**
	 * Set data value by given path
	 *
	 * @param string $path
	 * @param mixed $value
	 *
	 * @return bool
	 */
	protected function setPath($path, $value){

		$path = trim($path, $this->path_separator);
		if($path === ""){
			$this->data[$path] = $value;
			return true;
		}

		$parts = explode($this->path_separator, $path);
		if(!$parts){
			return false;
		}

		if(!isset($parts[1])){
			$this->data[$parts[0]] = $value;
			return true;
		}

		$last_key = array_pop($parts);

		$pointer = &$this->data;

		foreach($parts as $part){

			if(!is_array($pointer)){
				return false;
			}

			if(isset($pointer[$part])){
				$pointer = &$pointer[$part];
			} else {
				$pointer[$part] = array();
				$pointer = &$pointer[$part];
			}
		}

		if(!is_array($pointer)){
			return false;
		}

		$pointer[$last_key] = $value;
		return true;
	}



	/**
	 * Unset value from data/path
	 *
	 * @param string $path
	 * @return bool
	 */
	public function removeValue($path){

		if(!$this->getValueExists($path)){
			return false;
		}

		if($path && $path[0] === $this->path_separator){
			return $this->removePath($path);
		} else {
			unset($this->data[$path]);
		}

		return true;

	}

	/**
	 * Unset value from path
	 *
	 * @param string $path
	 * @return bool
	 */
	protected function removePath($path){
		$path = trim($path, $this->path_separator);
		if($path === ""){

			if(!isset($this->data[$path])){
				return false;
			}

			unset($this->data[$path]);
			return true;
		}

		$parts = explode($this->path_separator, $path);
		if(!$parts){
			return false;
		}

		if(!isset($parts[1])){
			if(!isset($this->data[$parts[0]])){
				return false;
			}

			unset($this->data[$parts[0]]);
			return true;
		}

		$last_part = array_pop($parts);

		$pointer = &$this->getPathReference($this->path_separator . implode($this->path_separator, $parts));
		if(is_array($pointer) && isset($pointer[$last_part])){
			unset($pointer[$last_part]);
			return true;
		}

		return false;
	}

	/**
	 * Get data value as int or (int)$default_value if not exists
	 *
	 * @param string $path
	 * @param int $default_value [optional] Default: 0
	 *
	 * @throws Data_Array_Exception when trying to access array or object
	 * @return int
	 */
	public function getInt($path, $default_value = 0){
		return (int)$this->getScalar($path, $default_value);
	}

	/**
	 * Get data value as float or (float)$default_value if not exists
	 *
	 * @param string $path
	 * @param float $default_value [optional] Default: 0.0
	 * @param int $precision[optional] Round output to $precision places. Default: NULL = skip round
	 *
	 * @throws Data_Array_Exception when trying to access array or object
	 * @return float
	 */
	public function getFloat($path, $default_value = 0.0, $precision = null){
		$value = (float)$this->getScalar($path, $default_value);
		if($precision !== null){
			return round($value, max(0, (int)$precision));
		}
		return $value;
	}

	/**
	 * Get data value as bool or (bool)$default_value if not exists
	 *
	 * @param string $path
	 * @param bool $default_value[optional] Default: FALSE
	 * @return bool
	 */
	public function getBool($path, $default_value = false){
		return (bool)$this->getRawValue($path, $default_value);
	}

	/**
	 * Get raw value if it's scalar or throw Data_Array_Exception exception
	 *
	 * @param string $path
	 * @param mixed $default_value [optional] Default: NULL
	 *
	 * @throws Data_Array_Exception when trying to access array or object
	 * @return mixed
	 */
	public function getScalar($path, $default_value = null){
		$v = $this->getRawValue($path, $default_value);
		if(!is_scalar($v) && $v !== null){

			throw new Data_Array_Exception(
				"Value at path '{$path}' must be scalar, not " . gettype($v),
				Data_Array_Exception::CODE_INVALID_VALUE,
				array(
				     "path" => $path,
				     "raw value" => $v
				)

			);

		}
		return $v;
	}

	/**
	 * Get data value as string or (string)$default_value if not exists
	 *
	 * @param string $path
	 * @param string $default_value [optional] Default: '' (empty string)
	 *
	 * @throws Data_Array_Exception when trying to access array or object
	 * @return string
	 */
	public function getString($path, $default_value = ''){

		$v = $this->getRawValue($path, $default_value);

		if(is_bool($v)){
			$v = (int)$v;
		}

		if(is_scalar($v) || $v === null){
			return (string)$v;
		}

		if(is_object($v)){

			if(method_exists($v, "__toString")){
				return (string)$v;
			} elseif(method_exists($v, "toString")){
				return (string)call_user_func(array($v, "toString"));
			}

		}

		throw new Data_Array_Exception(
			"Value at path '".(string)$path."' must be scalar or NULL, not " . gettype($v),
			Data_Array_Exception::CODE_INVALID_VALUE,
			array(
			     "path" => $path,
			     "raw value" => $v
			)

		);
	}

	/**
	 * Get HTML-safe string (using htmlspecialchars())
	 *
	 * @param string $path
	 * @param string $default_value [optional] Default: ''
	 * @param int $quote_style [optional] Quotes encoding style = one of Et\Data_Array::QUOTE_*
	 *
	 * @throws Data_Array_Exception when trying to access array or object
	 * @return string
	 */
	public function getHtmlSafeString($path, $default_value = '', $quote_style = self::QUOTE_DOUBLE){
		return htmlspecialchars(
			$this->getString($path, $default_value),
			$quote_style
		);
	}


	/**
	 * Get HTML-safe value
	 * If the value is string, htmlspecialchars() is applied
	 * other data types are returned as they are (FALSE, TRUE, NULL, numbers, arrays and objects)
	 *
	 * If $path begins with '/' character, it's considered to be path to nested data
	 * Example:
	 * Path "/a/b/c" points to $this->data["a"]["b"]["c"]
	 *
	 * @param string $path
	 * @param mixed $default_value [optional] Default: NULL
	 * @param bool $encode_single_quotes [optional] Encode also single quotes? Default: FALSE
	 *
	 * @throws Data_Array_Exception
	 * @return mixed
	 */
	public function getHtmlSafeMixed($path, $default_value = null, $encode_single_quotes = false){

		$raw_value = $this->getRawValue($path, $default_value);
		if(is_string($raw_value)){
			return htmlspecialchars(
				$raw_value,
				$encode_single_quotes
					? ENT_QUOTES
					: ENT_COMPAT
			);
		}

		return $raw_value;
	}

	/**
	 * @param string $path
	 * @param mixed $default_value [optional]
	 * @return mixed
	 */
	public function getValue($path, $default_value = null){
		return $this->getRawValue($path, $default_value);
	}

	/**
	 * Set data value using '->' access
	 *
	 * @param string $path
	 * @param mixed $value
	 */
	public function __set($path, $value) {
		$this->setValue($path, $value);
	}

	/**
	 * Check if data value exist (isset) using '->' access
	 *
	 * @param string $path
	 * @return bool
	 */
	public function __isset($path) {
		return $this->getValueExists($path);
	}

	/**
	 * Remove data if value exist (isset) using '->' access
	 *
	 * @param string $path
	 * @return void
	 */
	public function __unset($path) {
		$this->removeValue($path);
	}

	/**
	 * Disable '->' access to data
	 *
	 * @param string|int $path
	 *
	 * @throws Data_Array_Exception
	 * @return mixed
	 */
	public function __get($path) {
		return $this->getValue($path);
	}

	/**
	 * Set data value using array access
	 *
	 * @param string $path
	 * @param mixed $value
	 */
	public function offsetSet($path, $value) {
		if($path === null){
			$this->data[] = $value;
			return;
		}
		$this->setValue($path, $value);
	}

	/**
	 * Check if data value exist (isset) using array access
	 *
	 * @param string $path
	 * @return bool
	 */
	public function offsetExists($path) {
		return $this->getValueExists($path);
	}

	/**
	 * Remove data if value exist (isset) using array access
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public function offsetUnset($path) {
		$this->removeValue($path);
	}

	/**
	 * Disable array access to data
	 *
	 * @param string|int $path
	 *
	 * @throws Data_Array_Exception
	 * @return mixed
	 */
	public function offsetGet($path) {
		return $this->getValue($path);
	}

	/**
	 * Get count of data
	 *
	 * @return int
	 */
	public function count(){
		return count($this->data);
	}

	/**
	 * @return mixed|bool
	 */
	public function current() {
		return current($this->data);
	}
	
	public function next() {
		next($this->data);
	}

	/**
	 * @return mixed|null
	 */
	public function key() {
		return key($this->data);
	}

	/**
	 * @return bool
	 */
	public function valid() {
		return key($this->data) !== null;
	}

	public function rewind() {
		reset($this->data);
	}

	/**
	 * @return array|mixed
	 */
	public function jsonSerialize() {
		return $this->data;
	}
}