<?php
namespace Et;
class DB_Query_GroupBy extends Object implements \Iterator,\Countable,\ArrayAccess {

	/**
	 * @var DB_Query_Column[]
	 */
	protected $columns = array();

	/**
	 * @var DB_Query
	 */
	protected $query;

	/**
	 * @param DB_Query $query
	 * @param array $columns [optional]
	 */
	function __construct(DB_Query $query, array $columns = array()){
		$this->query = $query;
		if($columns){
			$this->setGroupByColumns($columns);
		}
	}

	/**
	 * @return DB_Query
	 */
	function getQuery(){
		return $this->query;

	}

	/**
	 * @return DB_Query_GroupBy|static
	 */
	function resetColumns(){
		$this->columns = array();
		return $this;
	}

	/**
	 * @param array $columns 
	 * @return static
	 */
	function setGroupByColumns(array $columns){
		$this->columns = array();
		foreach($columns as $v){
			$this->addGroupByColumn($v);
		}
		return $this;
	}

	/**
	 * @param string $column_name
	 * @return static
	 */
	function addGroupByColumn($column_name){
		$column = $this->query->getColumn($column_name);
		$this->columns[(string)$column] = $column;
		return $this;
	}

	/**
	 * @param string $column_name
	 * @return static
	 */
	function addGroupByResultColumn($column_name){
		return $this->addGroupByColumn(DB_Query::SELECTED_COLUMN_ALIAS . ".{$column_name}");
	}

	/**
	 * @return bool
	 */
	function isEmpty(){
		return !$this->columns;
	}

	/**
	 * @return int
	 */
	function getColumnsCount(){
		return count($this->columns);
	}

	/**
	 * @return DB_Query_Column[]
	 */
	function getColumns(){
		return $this->columns;
	}


	/**
	 * @return DB_Query_Column
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
	 * @return int
	 */
	public function count() {
		return $this->getColumnsCount();
	}


	/**
	 * @param string|int $column_name
	 * @return bool
	 */
	public function offsetExists($column_name) {
		return isset($this->columns[$column_name]);
	}

	/**
	 * @param string $column_name
	 * @return bool|DB_Query_Column
	 */
	public function offsetGet($column_name) {
		return isset($this->columns[$column_name])
			? $this->columns[$column_name]
			: false;
	}

	/**
	 * @param string $k
	 * @param string $column_name
	 */
	public function offsetSet($k, $column_name) {
		$this->addGroupByColumn($column_name);
	}

	/**
	 * @param string $column_name
	 */
	public function offsetUnset($column_name) {
		if(isset($this->columns[$column_name])){
			unset($this->columns[$column_name]);
		}
	}
}