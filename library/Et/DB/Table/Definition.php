<?php
namespace Et;
class DB_Table_Definition extends Object implements \Iterator,\Countable,\ArrayAccess{
	
	const PRIMARY_KEY_NAME = "PRIMARY";

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
	 * @var DB_Table_Key
	 */
	protected $primary_key;

	/**
	 * @var DB_Table_Key[]
	 */
	protected $unique_keys = array();

	/**
	 * @var DB_Table_Key[]
	 */
	protected $indexes = array();

	/**
	 * @var array
	 */
	protected $backend_options = array();

	/**
	 * @param $table_name
	 * @param DB_Table_Column_Definition[] $columns
	 * @param array $primary_key_columns
	 * @param string $comment [optional]
	 * @throws DB_Exception
	 */
	function __construct($table_name, array $columns, array $primary_key_columns, $comment = ""){
		
		DB::checkTableName($table_name);
		$this->table_name = $table_name;

		Debug_Assert::isArrayOfInstances($columns, 'Et\DB_Table_Column_Definition');
		foreach($columns as $column_definition){
			$column_name = $column_definition->getColumnName(false);
			$this->columns[$column_name] = $column_definition;
			$this->column_types[$column_name] = $column_definition->getType();
		}
		
		$primary_key = new DB_Table_Key($this->table_name, $primary_key_columns, static::PRIMARY_KEY_NAME);
		$this->checkKeyColumns($primary_key);
		$this->primary_key = $primary_key;
		
		$this->comment = trim($comment);
	}

	/**
	 * @param DB_Table_Key $key
	 * @param null|string $key_name
	 * @throws DB_Exception
	 */
	protected function checkKeyColumns(DB_Table_Key $key, $key_name = null){
		if(!$key_name){
			$key_name = $key->getKeyName();
		}
				
		$pk_cols = $key->getColumnNames();
		foreach($pk_cols as $name){
			if(!isset($this->columns[$name])){
				throw new DB_Exception(
					"'{$key_name}' key column '{$name}' not found in definition of table '{$this->getTableName()}'",
					DB_Exception::CODE_INVALID_KEY
				);
			}
		}
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
	 * @return DB_Table_Column_Definition[]
	 */
	function getPrimaryKeyColumns(){
		return $this->getKeyColumns($this->getPrimaryKey());
	}

	/**
	 * @param DB_Table_Key $key
	 * @return DB_Table_Column_Definition[]
	 */
	protected function getKeyColumns(DB_Table_Key $key){
		$cols = $key->getColumnNames();
		$output = array();
		foreach($cols as $name){
			$output[$name] = $this->columns[$name];
		}
		return $output;
	}

	/**
	 * @return array
	 */
	function getPrimaryKeyColumnNames(){
		return $this->getPrimaryKey()->getColumnNames();
	}

	/**
	 * @return DB_Table_Key
	 */
	function getPrimaryKey(){
		return $this->primary_key;
	}
	
	
	
	

	/**
	 * @return DB_Table_Key[]
	 */
	function getIndexes(){
		return $this->indexes;
	}

	/**
	 * @param string $index_name
	 * @return bool|DB_Table_Key
	 */
	function getIndex($index_name){
		return isset($this->indexes[$index_name])
				? $this->indexes[$index_name]
				: false;
	}

	/**
	 * @param string $index_name
	 * @return bool|DB_Table_Column_Definition[]
	 */
	function getIndexColumns($index_name){
		$index = $this->getIndex($index_name);
		if(!$index){
			return false;
		}
		return $this->getKeyColumns($index);
	}

	/**
	 * @param string $index_name
	 * @return bool|array
	 */
	function getIndexColumnNames($index_name){
		$index = $this->getIndex($index_name);
		if(!$index){
			return false;
		}
		return $index->getColumnNames();
	}

	/**
	 * @param array $column_names
	 * @param null|string $index_name [optional]
	 * @return DB_Table_Key
	 */
	function setIndex(array $column_names, $index_name = null){
		$key = new DB_Table_Key($this->table_name, $column_names, $index_name);
		$this->checkKeyColumns($key);
		$this->indexes[$key->getKeyName()] = $key;
		return $key;
	}



	
	

	/**
	 * @return DB_Table_Key[]
	 */
	function getUniqueKeys(){
		return $this->unique_keys;
	}

	/**
	 * @param string $key_name
	 * @return bool|DB_Table_Key
	 */
	function getUniqueKey($key_name){
		return isset($this->unique_keys[$key_name])
			? $this->unique_keys[$key_name]
			: false;
	}

	/**
	 * @param string $column_name
	 * @return DB_Table_Key[]
	 */
	function getKeysContainingColumn($column_name){
		if(!isset($this->columns[$column_name])){
			return array();
		}

		$keys = array();
		if(in_array($column_name, $this->getPrimaryKey()->getColumnNames())){
			$keys[] = $this->getPrimaryKey();
		}

		foreach($this->unique_keys as $key){
			if(in_array($column_name, $key->getColumnNames())){
				$keys[] = $key;
			}
		}

		foreach($this->indexes as $key){
			if(in_array($column_name, $key->getColumnNames())){
				$keys[] = $key;
			}
		}

		return $keys;
	}


	/**
	 * @param string $unique_key_name
	 * @return bool|DB_Table_Column_Definition[]
	 */
	function getUniqueKeyColumns($unique_key_name){
		$unique_key = $this->getUniqueKey($unique_key_name);
		if(!$unique_key){
			return false;
		}
		return $this->getKeyColumns($unique_key);
	}

	/**
	 * @param string $unique_key_name
	 * @return bool|array
	 */
	function getUniqueKeyColumnNames($unique_key_name){
		$unique_key = $this->getUniqueKey($unique_key_name);
		if(!$unique_key){
			return false;
		}
		return $unique_key->getColumnNames();
	}

	/**
	 * @param array $column_names
	 * @param null|string $unique_key_name [optional]
	 * @return DB_Table_Key
	 */
	function setUniqueKey(array $column_names, $unique_key_name = null){
		$key = new DB_Table_Key($this->table_name, $column_names, $unique_key_name);
		$this->checkKeyColumns($key);
		$this->unique_keys[$key->getKeyName()] = $key;
		return $key;
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