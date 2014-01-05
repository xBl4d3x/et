<?php
namespace Et;
class DB_Query_GroupBy extends Object implements \Iterator,\Countable {

	/**
	 * @var DB_Query_Column[]
	 */
	protected $group_by_columns = array();

	/**
	 * @var DB_Query
	 */
	protected $query;

	/**
	 * @param DB_Query $query
	 * @param array $group_by_columns [optional] array like: [column_name, ... ]
	 */
	function __construct(DB_Query $query, array $group_by_columns = array()){
		$this->query = $query;
		if($group_by_columns){
			$this->setGroupByColumns($group_by_columns);
		}
	}

	/**
	 * @return DB_Query
	 */
	function getQuery(){
		return $this->query;

	}

	/**
	 * @return static
	 */
	function removeGroupByColumns(){
		$this->group_by_columns = array();
		return $this;
	}

	/**
	 * @param array $group_by_columns Array like [column_name => ASC, ... ] or [1 => ASC, .... ]
	 * @param bool $merge [optional]
	 * @return static
	 */
	function setGroupByColumns(array $group_by_columns, $merge = true){
		if(!$merge){
			$this->group_by_columns = array();
		}

		foreach($group_by_columns as $c){
			$this->group_by_columns[] = new DB_Query_Column($this->getQuery(), $c);
		}

		return $this;
	}

	/**
	 * @param string $column_name
	 * @param null|string $table_name [optional]
	 * @return static
	 */
	function groupByColumn($column_name, $table_name = null){
		$column = new DB_Query_Column($this->getQuery(), $column_name, $table_name);
		$this->group_by_columns[(string)$column] = $column;
		return $this;
	}

	/**
	 * @return bool
	 */
	function isEmpty(){
		return !$this->group_by_columns;
	}

	/**
	 * @return int
	 */
	function getColumnsCount(){
		return count($this->group_by_columns);
	}

	/**
	 * @return DB_Query_Column[]
	 */
	function getGroupByColumns(){
		return $this->group_by_columns;
	}



	/**
	 * @return static
	 */
	public function current() {
		return current($this->group_by_columns);
	}


	public function next() {
		next($this->group_by_columns);
	}

	/**
	 * @return string|null
	 */
	public function key() {
		return key($this->group_by_columns);
	}

	/**
	 * @return bool
	 */
	public function valid() {
		return key($this->group_by_columns) !== null;
	}


	public function rewind() {
		reset($this->group_by_columns);
	}

	/**
	 * @return int
	 */
	public function count() {
		return $this->getColumnsCount();
	}
}