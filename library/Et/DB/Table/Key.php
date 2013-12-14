<?php
namespace Et;
class DB_Table_Key extends Object implements \Countable,\ArrayAccess,\Iterator {

	const DEFAULT_KEY_PARTS_DELIMITER = "#";

	const TYPE_SCALAR = "Scalar";
	const TYPE_INT = "Int";
	const TYPE_BOOL = "Bool";
	const TYPE_FLOAT = "Float";
	const TYPE_STRING = "String";
	const TYPE_LOCALE = "Locale";
	const TYPE_DATE = "Date";
	const TYPE_DATETIME = "DateTime";

	/**
	 * @var string
	 */
	protected $key_name;

	/**
	 * @var string
	 */
	protected $table_name;

	/**
	 * @var array
	 */
	protected $column_types = array();

	/**
	 * @var array
	 */
	protected $column_values = array();

	/**
	 * @var string
	 */
	protected $key_parts_delimiter = self::DEFAULT_KEY_PARTS_DELIMITER;

	/**
	 * @param string $table_name
	 * @param array $columns_names_or_values
	 * @param string $key_name [optional]
	 * @param array $column_types [optional]
	 * @throws DB_Exception
	 */
	function __construct($table_name, array $columns_names_or_values, $key_name = "", array $column_types = array()) {
		$this->table_name = (string)$table_name;

		if(!$columns_names_or_values){
			throw new DB_Exception(
				"Key must contain at least 1 column",
				DB_Exception::CODE_INVALID_KEY
			);
		}

		foreach($columns_names_or_values as $i => $column){
			if(is_numeric($i)){
				$column_name = (string)$column;
				$value = null;
			} else {
				$column_name = (string)$i;
				$value = $column;
			}

			$this->column_values[$column_name] = null;
			$this->column_types[$column_name] = isset($column_types[$column_name])
												? $column_types[$column_name]
												: self::TYPE_SCALAR;

			if($value !== null){
				$this->setColumnValue($column_name, $value);
			}
		}

		$key_name = trim($key_name);
		if(!$key_name){
			$key_name = implode("_", array_keys($this->column_values));
		}
		$this->key_name = $key_name;
	}

	/**
	 * @param string $key_parts_delimiter
	 */
	public function setKeyPartsDelimiter($key_parts_delimiter) {
		$this->key_parts_delimiter = (string)$key_parts_delimiter;
	}

	/**
	 * @return string
	 */
	public function getKeyPartsDelimiter() {
		return $this->key_parts_delimiter;
	}



	/**
	 * @return string
	 */
	function getKeyName(){
		return $this->key_name;
	}

	/**
	 * @return array
	 */
	function getColumnTypes(){
		return $this->column_types;
	}

	/**
	 * @param string $column_name
	 * @return bool
	 */
	function getColumnType($column_name){
		return isset($this->column_types[$column_name])
				? $this->column_types[$column_name]
				: false;
	}

	/**
	 * @return bool
	 */
	function isMultiPartKey(){
		return count($this->column_values) > 1;
	}

	/**
	 * @return string
	 */
	public function getTableName() {
		return $this->table_name;
	}

	/**
	 * @return array
	 */
	function getColumnNames(){
		return array_keys($this->column_values);
	}

	/**
	 * @param bool $get_as_string [optional]
	 * @return array
	 */
	function getColumnValues($get_as_string = false){
		if($get_as_string){
			return $this->toString();
		}
		return $this->column_values;
	}

	/**
	 * @return bool
	 */
	function hasAllColumnValues(){
		foreach($this->column_values as $value){
			if($value === null){
				return false;
			}
		}
		return true;
	}

	/**
	 * @param string $column
	 * @return mixed|null
	 */
	function getColumnValue($column){
		$column = (string)$column;
		$value = isset($this->column_values[$column])
			? $this->column_values[$column]
			: null;

		if($value === null){
			return $value;
		}

		switch($this->getColumnType($column)){
			case self::TYPE_BOOL:
				return (bool)$value;

			case self::TYPE_INT:
				return (int)$value;

			case self::TYPE_FLOAT:
				return (float)$value;

			case self::TYPE_STRING:
				return (string)$value;

			case self::TYPE_LOCALE:
				return Locales::getLocale($value);

			case self::TYPE_DATE:
				return Locales_Date::getInstance($value);

			case self::TYPE_DATETIME:
				return Locales_DateTime::getInstance($value);

			default:
				return $value;
		}

	}

	/**
	 * @param string $column
	 * @throws DB_Exception
	 */
	function checkHasColumn($column){
		if(!$this->hasColumn($column)){
			throw new DB_Exception(
				"Column '{$column}' not found in key",
				DB_Exception::CODE_INVALID_COLUMN_NAME
			);
		}
	}

	/**
	 * @param string $column
	 * @return bool
	 */
	function hasColumn($column){
		return isset($this->column_types[$column]);
	}

	/**
	 * @param string $column
	 * @param int|bool|string $value
	 * @return static|DB_Table_Key
	 */
	function setColumnValue($column, $value){
		$column = (string)$column;
		$this->checkHasColumn($column);
		$this->column_values[$column] = $value;
		return $this;
	}

	/**
	 * @param array|string $column_values
	 * @return static|DB_Table_Key
	 */
	function setColumnValues($column_values){
		$this->resetValues();
		if(!is_array($column_values)){
			$column_values = explode($this->key_parts_delimiter, $column_values);
		}
		foreach($this->column_types as $column => $type){
			$this->column_values[$column] = isset($column_values[$column])
											? $column_values[$column]
											: null;
		}
		return $this;
	}

	/**
	 * @return string
	 */
	function __toString(){
		return $this->toString();
	}

	/**
	 * @return string
	 */
	function toString(){

		$output = array();
		foreach($this->column_types as $column => $type){
			$value = $this->column_values[$column];
			if($value === null){
				return "";
			}

			switch($type){
				case self::TYPE_BOOL:
				case self::TYPE_INT:
					$output[] = (int)$value;
					break;

				case self::TYPE_FLOAT:
					$output[] = (float)$value;
					break;

				case self::TYPE_DATE:
				case self::TYPE_DATETIME:
				case self::TYPE_LOCALE:
				case self::TYPE_STRING:
					$output[] = (string)$value;
					break;

				default:
					$output[] = (string)$value;
			}
		}

		return implode($this->key_parts_delimiter, $output);
	}

	/**
	 * @return static|DB_Table_Key
	 */
	function resetValues(){
		foreach($this->column_types as $column => $type){
			$this->column_values[$column] = null;
		}
		return $this;
	}

	/**
	 * @param DB_Query $query [optional]
	 * @return DB_Query
	 */
	function getSelectQuery(DB_Query $query = null){
		if(!$query){
			$query = new DB_Query($this->table_name);
		}
		$query->getSelect()->addColumns(array_keys($this->column_values), $this->table_name);
		return $query;
	}

	/**
	 * @param DB_Query $query [optional]
	 * @return DB_Query
	 */
	function getWhereQuery(DB_Query $query = null){
		if(!$query){
			$query = new DB_Query($this->table_name);
		}
		$query->getWhere()->addColumnsEqual($this->column_values, $this->table_name);
		return $query;
	}


	/**
	 * @return string|int|null
	 */
	public function current() {
		return current($this->column_values);
	}

	public function next() {
		next($this->column_values);
	}

	/**
	 * @return string|null
	 */
	public function key() {
		return key($this->column_values);
	}

	/**
	 * @return bool
	 */
	public function valid() {
		return key($this->column_values) !== null;
	}

	public function rewind() {
		reset($this->column_values);
	}

	/**
	 * @param string $column_name
	 * @return bool
	 */
	public function offsetExists($column_name) {
		return $this->hasColumn($column_name);
	}

	/**
	 * @param string $column_name
	 * @return string|int|null
	 */
	public function offsetGet($column_name) {
		return $this->getColumnValue($column_name);
	}


	public function offsetSet($column_name, $value) {
		$this->setColumnValue($column_name, $value);
	}


	public function offsetUnset($column_name) {
		if(!$this->hasColumn($column_name)){
			return;
		}
		$this->column_values[$column_name] = null;
	}

	/**
	 * @return int
	 */
	public function count() {
		return count($this->column_values);
	}

	/**
	 * @return static|DB_Table_Key
	 */
	public function getEmptyInstance(){
		$instance = $this->cloneInstance(false);
		$instance->resetValues();
		return $instance;
	}
}