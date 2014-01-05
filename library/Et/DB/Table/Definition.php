<?php
namespace Et;
class DB_Table_Definition extends Object implements \Iterator,\Countable,\ArrayAccess{

	/**
	 * @var string
	 */
	protected $table_name;

	/**
	 * @var string
	 */
	protected $comment = "";

	/**
	 * @var DB_Table_Column_Definition[]
	 */
	protected $columns = array();

	/**
	 * @var array
	 */
	protected $column_types = array();

	/**
	 * @var array
	 */
	protected $primary_key_columns = array();

	/**
	 * @var array[]
	 */
	protected $unique_keys_columns = array();

	/**
	 * @var array[]
	 */
	protected $indexes_columns = array();

	/**
	 * @param $table_name
	 * @param DB_Table_Column_Definition[] $columns
	 * @param string $comment [optional]
	 */
	function __construct($table_name, array $columns, $comment = ""){
		Debug_Assert::isArrayOfInstances($columns, 'Et\DB_Table_Column_Definition');
		DB::checkTableName($table_name);
		$this->table_name = $table_name;
		foreach($columns as $column_definition){
			$column_name = $column_definition->getColumnName(false);
			$this->columns[$column_name] = $column_definition;
			$this->column_types[$column_name] = $column_definition->getType();
		}
		$this->comment = trim($comment);
	}

	/**
	 * @param array $column_names
	 * @throws DB_Exception
	 */
	protected function checkColumnNames(array $column_names){
		foreach($column_names as $column){
			if(!isset($this->columns[$column])){
				throw new DB_Exception(
					"Table '{$this->table_name}' has no '{$column}' column",
					DB_Exception::CODE_INVALID_COLUMN_NAME
				);
			}
		}
	}
	
	/**
	 * @param array $column_names
	 * @throws DB_Exception
	 */
	function setPrimaryKeyColumns(array $column_names){
		$this->checkColumnNames($column_names);
		$this->primary_key_columns = array_keys($column_names);
	}

	/**
	 * @return array
	 */
	function getPrimaryKeyColumnNames(){
		return $this->primary_key_columns;
	}

	/**
	 * @return DB_Table_Column_Definition[]
	 */
	function getPrimaryKeyColumns(){
		$output = array();
		foreach($this->primary_key_columns as $column){
			$output[$column] = $this->columns[$column];
		}
		return $output;
	}

	/**
	 * @param array $column_names
	 * @param null|string $key_name [optional]
	 * @return DB_Table_Key
	 */
	protected function getKeyFromColumns(array $column_names, $key_name = null){
		$this->checkColumnNames($column_names);
		$types = array();
		foreach($column_names as $name){
			$column = $this->columns[$name];
			$type = $column->getType();
			switch($type){
				case DB_Table_Column_Definition::TYPE_BOOL:
				case DB_Table_Column_Definition::TYPE_STRING:
				case DB_Table_Column_Definition::TYPE_FLOAT:
				case DB_Table_Column_Definition::TYPE_DATE:
				case DB_Table_Column_Definition::TYPE_LOCALE:
				case DB_Table_Column_Definition::TYPE_DATETIME:
					$types[$name] = $type;
					break;

				default:
					$types[$name] = DB_Table_Key::TYPE_STRING;
			}
		}
		return new DB_Table_Key($this->table_name, $column_names, $key_name, $types);
	}

	/**
	 * @return DB_Table_Key
	 */
	function getPrimaryKey(){
		return $this->getKeyFromColumns($this->getPrimaryKeyColumnNames(), "PRIMARY");
	}

	/**
	 * @return array
	 */
	function getIndexesNames(){
		return array_keys($this->indexes_columns);
	}

	/**
	 * @return array[]
	 */
	function getIndexesColumnNames(){
		return $this->indexes_columns;
	}

	/**
	 * @param array $column_names
	 * @param null|string $index_name [optional]
	 */
	function setIndexColumns(array $column_names, $index_name = null){
		$this->checkColumnNames($column_names);
		if(!$index_name){
			$index_name = implode("_", $column_names);
		}
		$this->indexes_columns[$index_name] = $column_names;
	}

	/**
	 * @param string $index_name
	 * @return array|bool
	 */
	function getIndexColumnNames($index_name){
		return isset($this->indexes_columns[$index_name])
				? $this->indexes_columns[$index_name]
				: false;
	}

	/**
	 * @param string $index_name
	 * @return DB_Table_Column_Definition[]|bool
	 */
	function getIndexColumns($index_name){
		if(!isset($this->indexes_columns[$index_name])){
			return false;
		}
		$columns = array();
		foreach($this->indexes_columns[$index_name] as $name){
			$columns[$name] = $this->columns[$name];
		}
		return $columns;
	}

	/**
	 * @param string $index_name
	 * @return bool|DB_Table_Key
	 */
	function getIndexKey($index_name){
		$names = $this->getIndexColumnNames($index_name);
		if(!$names){
			return false;
		}
		return $this->getKeyFromColumns($names, $index_name);
	}


	/**
	 * @return array
	 */
	function getUniqueKeysNames(){
		return array_keys($this->unique_keys_columns);
	}

	/**
	 * @return array[]
	 */
	function getUniqueKeysColumnNames(){
		return $this->unique_keys_columns;
	}

	/**
	 * @param array $column_names
	 * @param null|string $unique_key_name [optional]
	 */
	function setUniqueKeyColumns(array $column_names, $unique_key_name = null){
		$this->checkColumnNames($column_names);
		if(!$unique_key_name){
			$unique_key_name = implode("_", $column_names);
		}
		$this->unique_keys_columns[$unique_key_name] = $column_names;
	}

	/**
	 * @param string $unique_key_name
	 * @return array|bool
	 */
	function getUniqueKeyColumnNames($unique_key_name){
		return isset($this->unique_keys_columns[$unique_key_name])
			? $this->unique_keys_columns[$unique_key_name]
			: false;
	}

	/**
	 * @param string $unique_key_name
	 * @return DB_Table_Column_Definition[]|bool
	 */
	function getUniqueKeyColumns($unique_key_name){
		if(!isset($this->unique_keys_columns[$unique_key_name])){
			return false;
		}
		$columns = array();
		foreach($this->unique_keys_columns[$unique_key_name] as $name){
			$columns[$name] = $this->columns[$name];
		}
		return $columns;
	}

	/**
	 * @param string $unique_key_name
	 * @return bool|DB_Table_Key
	 */
	function getUniqueKey($unique_key_name){
		$names = $this->getUniqueKeyColumnNames($unique_key_name);
		if(!$names){
			return false;
		}
		return $this->getKeyFromColumns($names, $unique_key_name);
	}
	
	

	/**
	 * @return string
	 */
	public function getComment() {
		return $this->comment;
	}


	/**
	 * @return \Et\DB_Table_Column_Definition[]
	 */
	public function getColumns() {
		return $this->columns;
	}


	/**
	 * @return string
	 */
	public function getTableName() {
		return $this->table_name;
	}	

	/**
	 * @param string $column_name
	 * @return bool|DB_Table_Column_Definition
	 */
	function getColumn($column_name){
		return isset($this->columns[$column_name])
				? $this->columns[$column_name]
				: false;
	}

	/**
	 * @param string $column_name
	 * @return bool
	 */
	function getColumnExists($column_name){
		return isset($this->columns[$column_name]);
	}

	/**
	 * @param string $column_name
	 * @throws DB_Exception
	 */
	function checkColumnExists($column_name){
		if(!isset($this->columns[$column_name])){
			throw new DB_Exception(
				"Column '{$column_name}' does not exist",
				DB_Exception::CODE_INVALID_COLUMN_NAME
			);
		}
	}

	/**
	 * @return array
	 */
	function getColumnNames(){
		return array_keys($this->columns);
	}

	/**
	 * @return array
	 */
	function getColumnTypes(){
		return $this->column_types;
	}

	/**
	 * @param string $column_name
	 * @return bool|string
	 */
	function getColumnType($column_name){
		return isset($this->column_types[$column_name])
			? $this->column_types[$column_name]
			: false;
	}

	/**
	 * @return int
	 */
	function getColumnsCount(){
		return count($this->columns);
	}

	/**
	 * @return mixed
	 */
	public function current() {
		return current($this->columns);
	}

	public function next() {
		next($this->columns);
	}

	/**
	 * @return string|null
	 */
	public function key() {
		return key($this->columns);
	}

	/**
	 * @return bool
	 */
	public function valid() {
		return key($this->columns) !== null;
	}

	public function rewind() {
		reset($this->columns);
	}

	/**
	 * @param string $column_name
	 * @return bool
	 */
	public function offsetExists($column_name) {
		return isset($this->columns[$column_name]);
	}

	/**
	 * @param string $column_name
	 * @return bool|DB_Table_Column_Definition|mixed
	 */
	public function offsetGet($column_name) {
		return $this->getColumn($column_name);
	}


	/**
	 * @param string $column_name
	 * @param mixed $value
	 * @throws DB_Exception
	 */
	public function offsetSet($column_name, $value) {
		throw new DB_Exception(
			"Cannot modify columns definitions",
			DB_Exception::CODE_NOT_SUPPORTED
		);
	}

	/**
	 * @param string $column_name
	 * @throws DB_Exception
	 */
	public function offsetUnset($column_name) {
		throw new DB_Exception(
			"Cannot modify columns definitions",
			DB_Exception::CODE_NOT_SUPPORTED
		);
	}

	/**
	 * @return int
	 */
	public function count() {
		return $this->getColumnsCount();
	}
}