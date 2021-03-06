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
	const TYPE_BINARY_DATA = "BinaryData";

	const DEF_TYPE = "type";
	const DEF_BACKEND_OPTIONS = "backend_options";
	const DEF_SIZE = "size";
	const DEF_ALLOW_NULL = "allow_null";
	const DEF_UNSIGNED = "unsigned";
	const DEF_COMMENT = "comment";
	const DEF_DEFAULT_VALUE = "default_value";

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
		self::TYPE_BINARY_DATA,
	);

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
	protected $comment = "";

	/**
	 * @var array
	 */
	protected $backend_options = array();


	/**
	 * @param string $column_name
	 * @param string $column_type
	 * @param null|int $column_size [optional]
	 * @param array $parameters [optional]
	 */
	function __construct($column_name, $column_type, $column_size = null, array $parameters = array()){
		parent::__construct($column_name);
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
			case self::DEF_SIZE:
				$this->size = max(1, (int)$value);
				return;

			case self::DEF_ALLOW_NULL:
			case self::DEF_UNSIGNED:
				$this->{$parameter} = (bool)$value;
				return;

			case self::DEF_BACKEND_OPTIONS:
				$this->setBackendOptions($value);
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
	 * @return boolean
	 */
	public function isBinary() {
		return $this->type == self::TYPE_BINARY_DATA;
	}


	/**
	 * @param array $backend_options
	 */
	public function setBackendOptions(array $backend_options) {
		foreach($backend_options as $k => $v){
			$this->setBackendOption($k, $v);
		}
	}

	/**
	 * @param string $option
	 * @param mixed $value
	 */
	function setBackendOption($option, $value){
		$this->backend_options[$option] = $value;
	}

	/**
	 * @return array
	 */
	public function getBackendOptions() {
		return $this->backend_options;
	}

	/**
	 * @param string $option
	 * @param null|mixed $default_value [optional]
	 * @return mixed|null
	 */
	function getBackendOption($option, $default_value = null){
		return isset($this->backend_options[$option])
			? $this->backend_options[$option]
			: $default_value;
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
			switch($column_type){
				case self::TYPE_STRING:
					$column_size = 255;
					break;

				case self::TYPE_INT:
					$column_size = 11;
					break;

				case self::TYPE_LOCALE:
					$column_size = 5;
					break;

				default:
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
		if($default_value === null){
			$this->default_value = null;
			return;
		}

		switch($this->type){

			case self::TYPE_BOOL:
				$default_value = (bool)$default_value;
				break;

			case self::TYPE_INT:
				$default_value = (int)$default_value;
				break;

			case self::TYPE_FLOAT:
				$default_value = (float)$default_value;
				break;

			case self::TYPE_LOCALE:
				if($default_value === ""){
					break;
				}
				$default_value = Locales::getLocale($default_value);
				break;

			case self::TYPE_DATETIME:
				if($default_value === ""){
					break;
				}
				$default_value = Locales::getDateTime($default_value);
				break;

			case self::TYPE_DATE:
				if($default_value === ""){
					break;
				}
				$default_value = Locales::getDate($default_value);
				break;

			default:
				$default_value = (string)$default_value;


		}

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
		return $value;
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

			case self::TYPE_DATE:
				if(trim($value) === ""){
					return null;
				}
				return Locales::getDate($value);

			case self::TYPE_DATETIME:
				if(trim($value) === ""){
					return null;
				}
				return Locales::getDateTime($value);

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
}