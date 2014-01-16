<?php
namespace Et;
class DB_Query_OrderBy extends Object implements \Iterator,\Countable,\ArrayAccess {

	/**
	 * @var DB_Query_OrderBy_Column[]
	 */
	protected $order_by_columns = array();

	/**
	 * @var DB_Query
	 */
	protected $query;

	/**
	 * @param DB_Query $query
	 * @param array $statements [optional] array like: [column_name => ASC, ... ] or [1 => ASC, .... ]
	 */
	function __construct(DB_Query $query, array $statements = array()){
		$this->query = $query;
		if($statements){
			$this->setOrderByColumns($statements);
		}
	}

	/**
	 * @return DB_Query
	 */
	function getQuery(){
		return $this->query;

	}

	/**
	 * @return static|DB_Query_OrderBy
	 */
	function resetOrderBy(){
		$this->order_by_columns = array();
		return $this;
	}

	/**
	 * @param array $statements Array like
	 * [column_name => ASC, ... ]
	 * [1 => ASC, .... ]
	 * [table_name.column_name => ASC .... ]
	 * [_none_.result_column_name => ASC ]
	 * @return static
	 */
	function setOrderByColumns(array $statements){
		$this->order_by_columns = array();
		foreach($statements as $k => $v){
			if(is_numeric($k)){
				$this->addOrderByColumnNumber($k, $v);
			} else {
				$this->addOrderByColumn($k, $v);
			}
		}
		return $this;
	}

	/**
	 * @param string $column_name
	 * @param null|string $order_how  [optional]
	 * @return static
	 */
	function addOrderByColumn($column_name, $order_how = null){
		$column = new DB_Query_OrderBy_Column($this->getQuery(), $column_name, $order_how);
		$this->order_by_columns[(string)$column] = $column;
		return $this;
	}

	/**
	 * @param string $column_name
	 * @param null|string $order_how  [optional]
	 * @return static
	 */
	function addOrderByResultColumn($column_name, $order_how = null){
		$column = new DB_Query_OrderBy_Column($this->getQuery(), DB_Query::SELECTED_COLUMN_ALIAS . ".{$column_name}", $order_how);
		$this->order_by_columns[(string)$column] = $column;
		return $this;
	}

	/**
	 * @param array $columns
	 * @return static
	 */
	function addOrderByColumns(array $columns){
		foreach($columns as $column => $how){
			$this->addOrderByColumn($column, $how);
		}
		return $this;
	}

	/**
	 * @param int $column_number
	 * @param null|string $order_how [optional]
	 * @return static
	 */
	function addOrderByColumnNumber($column_number, $order_how = null){
		$column = new DB_Query_OrderBy_Column($this->getQuery(), (int)$column_number, $order_how);
		$this->order_by_columns[(int)$column_number] = $column;
		return $this;
	}



	/**
	 * @return bool
	 */
	function isEmpty(){
		return !$this->order_by_columns;
	}

	/**
	 * @return int
	 */
	function getColumnsCount(){
		return count($this->order_by_columns);
	}

	/**
	 * @return DB_Query_OrderBy_Column[]
	 */
	function getOrderByColumns(){
		return $this->order_by_columns;
	}


	/**
	 * @return DB_Query_OrderBy_Column
	 */
	public function current() {
		return current($this->order_by_columns);
	}


	public function next() {
		next($this->order_by_columns);
	}

	/**
	 * @return string|null
	 */
	public function key() {
		return key($this->order_by_columns);
	}

	/**
	 * @return bool
	 */
	public function valid() {
		return key($this->order_by_columns) !== null;
	}


	public function rewind() {
		reset($this->order_by_columns);
	}

	/**
	 * @return int
	 */
	public function count() {
		return $this->getColumnsCount();
	}


	/**
	 * @param string|int $column_name_or_number
	 * @return bool
	 */
	public function offsetExists($column_name_or_number) {
		return isset($this->order_by_columns[$column_name_or_number]);
	}

	/**
	 * @param string $column_name_or_number
	 * @return bool|DB_Query_OrderBy_Column
	 */
	public function offsetGet($column_name_or_number) {
		return isset($this->order_by_columns[$column_name_or_number])
				? $this->order_by_columns[$column_name_or_number]
				: false;
	}

	/**
	 * @param string $column_name_or_number
	 * @param string $order_how
	 */
	public function offsetSet($column_name_or_number, $order_how) {
		if(is_numeric($column_name_or_number)){
			$this->addOrderByColumnNumber($column_name_or_number, $order_how);
		} else {
			$this->addOrderByColumn($column_name_or_number, $order_how);
		}
	}

	/**
	 * @param string $column_name_or_number
	 */
	public function offsetUnset($column_name_or_number) {
		if(isset($this->order_by_columns[$column_name_or_number])){
			unset($this->order_by_columns[$column_name_or_number]);
		}
	}
}