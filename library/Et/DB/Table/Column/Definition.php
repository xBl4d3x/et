<?php
namespace Et;
class DB_Table_Column_Definition extends DB_Table_Column {

	const TYPE_STRING = "String";
	const TYPE_BOOL = "Bool";
	const TYPE_INT = "Int";
	const TYPE_FLOAT = "Float";
	const TYPE_LOCALE = "Locale";
	const TYPE_DATETIME = "DateTime";
	const TYPE_DATE = "Date";
	const TYPE_SERIALIZED = "Serialized";
	const TYPE_BINARY_DATA = "BinaryData";

	const SERIALIZATION_METHOD_SERIALIZE = "serialize";
	const SERIALIZATION_METHOD_JSON = "json";

	/**
	 * @var array
	 */
	protected static $_allowed_column_types = array(
		self::TYPE_STRING,
		self::TYPE_BOOL,
		self::TYPE_INT,
		self::TYPE_FLOAT,
		self::TYPE_LOCALE,
		self::TYPE_DATETIME,
		self::TYPE_DATE,
		self::TYPE_SERIALIZED,
		self::TYPE_BINARY_DATA,
	);

	const DEF_TYPE = "type";
	const DEF_SIZE = "size";
	const DEF_ALLOW_NULL = "allow_null";
	const DEF_UNSIGNED = "unsigned";
	const DEF_SERIALIZATION_METHOD = "serialization_method";
	const DEF_COMMENT = "comment";
	const DEF_DEFAULT_VALUE = "default_value";

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var int
	 */
	protected $size = null;

	/**
	 * @var bool
	 */
	protected $allow_null = false;

	/**
	 * @var bool
	 */
	protected $unsigned = false;

	/**
	 * @var null|mixed
	 */
	protected $default_value = null;

	/**
	 * @var string
	 */
	protected $serialization_method = self::SERIALIZATION_METHOD_SERIALIZE;

	/**
	 * @var string
	 */
	protected $comment = "";

	/**
	 * @param string $column_name
	 * @param string $column_type
	 * @param null|int $column_size [optional]
	 * @param null|string $table_name [optional]
	 * @param array $parameters [optional]
	 */
	function __construct($column_name, $column_type, $column_size = null, $table_name = null, array $parameters = array()){
		parent::__construct($column_name, $table_name);
		$this->setType($column_type, $column_size);
		$this->setParameters($parameters);
	}


	/**
	 * @param array $parameters
	 */
	protected function setParameters(array $parameters){
		foreach($parameters as $parameter => $value){
			$this->setParameter($parameter, $value);
		}
	}


	/**
	 * @param string $parameter
	 * @param mixed $value
	 * @throws DB_Exception
	 */
	protected function setParameter($parameter, $value){
		switch($parameter){
			case self::DEF_SERIALIZATION_METHOD:
				$this->checkSerializationMethod($value);
				$this->serialization_method = $value;
				return;


			case self::DEF_SIZE:
				$this->size = max(1, (int)$value);
				return;

			case self::DEF_ALLOW_NULL:
			case self::DEF_UNSIGNED:
				$this->{$parameter} = (bool)$value;
				return;

			case self::DEF_COMMENT:
				$this->comment = trim($value);
				return;

			case self::DEF_DEFAULT_VALUE:
				$this->setDefaultValue($value);
				return;
		}
	}

	/**
	 * @param string $method
	 * @throws DB_Exception
	 */
	protected function checkSerializationMethod($method){
		$allowed = array(
			self::SERIALIZATION_METHOD_JSON,
			self::SERIALIZATION_METHOD_SERIALIZE,
		);
		if(!in_array($method, $allowed)){
			throw new DB_Exception(
				"Invalid serialization method '{$method}' - must be one of '" . implode("', '", $allowed) . "'",
				DB_Exception::CODE_INVALID_COLUMN_DEFINITION
			);
		}

	}

	/**
	 * @return boolean
	 */
	public function isBinary() {
		return $this->type == self::TYPE_BINARY_DATA ||
			($this->type == self::TYPE_SERIALIZED && $this->serialization_method == self::SERIALIZATION_METHOD_SERIALIZE);
	}




	/**
	 * @param $column_type
	 * @param null|int $column_size [optional]
	 */
	protected function setType($column_type, $column_size = null){
		$this->checkColumnType($column_type);
		$this->type = $column_type;
		$column_size = max(0, (int)$column_size);
		if(!$column_size){
			if($column_type == self::TYPE_INT){
				$column_size = 11;
			} elseif($column_type == self::TYPE_STRING){
				$column_size = 255;
			}
		}
		$this->size = $column_size;
	}


	/**
	 * @param string $type
	 * @throws DB_Exception
	 */
	protected function checkColumnType($type){
		if(!in_array($type, static::$_allowed_column_types)){
			throw new DB_Exception(
				"Invalid column type '{$type}' - must be one of '" . implode("', '", static::$_allowed_column_types) . "'",
				DB_Exception::CODE_INVALID_COLUMN_DEFINITION
			);
		}
	}

	/**
	 * @param mixed|null $default_value
	 */
	function setDefaultValue($default_value = null){
		$this->default_value = $default_value;
	}

	/**
	 * @return mixed|null
	 */
	function getDefaultValue(){
		return $this->default_value;
	}

	/**
	 * @return boolean
	 */
	public function getAllowNull() {
		return $this->allow_null;
	}

	/**
	 * @return int
	 */
	public function getSize() {
		return $this->size;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getComment() {
		return $this->comment;
	}

	/**
	 * @return string
	 */
	public function getSerializationMethod() {
		return $this->serialization_method;
	}

	/**
	 * @return boolean
	 */
	public function isUnsigned() {
		return $this->unsigned;
	}

	/**
	 * @param mixed $value
	 * @return float|int|mixed|null|string
	 */
	function getValueForDB($value){
		if($value === null && $this->getAllowNull()){
			return $value;
		}

		switch($this->getType()){
			case self::TYPE_BOOL:
				return $value ? 1 : 0;

			case self::TYPE_INT:
				return (int)$value;

			case self::TYPE_FLOAT:
				return (float)$value;

			case self::TYPE_SERIALIZED:
				return $this->serializeValue($value);

			case self::TYPE_DATE:
				return Locales_Date::getInstance($value)->format(Locales_Date::FORMAT_DATE);

			case self::TYPE_DATETIME:
				return Locales_DateTime::getInstance($value)->format(Locales_DateTime::FORMAT_MYSQL);

			case self::TYPE_BINARY_DATA:
				return (string)$value;

			case self::TYPE_STRING:
			case self::TYPE_LOCALE:
			default:
				return trim($value);
		}
	}

	/**
	 * @param mixed $value
	 * @return bool|Locales_Date|Locales_DateTime|Locales_Locale|float|int|mixed|null|string
	 */
	function getValueFromDB($value){
		if($value === null && $this->getAllowNull()){
			return null;
		}

		switch($this->getType()){
			case self::TYPE_BOOL:
				return (bool)$value;

			case self::TYPE_INT:
				return (int)$value;

			case self::TYPE_FLOAT:
				return (float)$value;

			case self::TYPE_SERIALIZED:
				return $this->unserializeValue($value);

			case self::TYPE_DATE:
				if(trim($value) === ""){
					return null;
				}
				return Locales_Date::getInstance($value);

			case self::TYPE_DATETIME:
				if(trim($value) === ""){
					return null;
				}
				return Locales_DateTime::getInstance($value);

			case self::TYPE_LOCALE:
				if(trim($value) === ""){
					return null;
				}
				return Locales::getLocale($value);


			case self::TYPE_STRING:
			case self::TYPE_BINARY_DATA:
			default:
				return (string)$value;
		}

	}

	/**
	 * @return float|int|mixed|null|string
	 */
	function getDefaultValueForDB(){
		$default_value = $this->getDefaultValue();
		return $this->getValueForDB($default_value);
	}


	/**
	 * @param string $value
	 * @return string
	 * @throws DB_Exception
	 */
	public function serializeValue($value){

		return $this->serialization_method == self::SERIALIZATION_METHOD_JSON
				? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
				: serialize($value);

	}

	/**
	 * @param string $value
	 * @return mixed|null
	 * @throws DB_Exception
	 */
	public function unserializeValue($value){

		if($value === null || $value === "" || !is_scalar($value)){
			return null;
		}

		return $this->serialization_method == self::SERIALIZATION_METHOD_JSON
				? json_decode($value, true)
				: unserialize($value);
	}

}