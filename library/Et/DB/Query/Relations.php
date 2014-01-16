<?php
namespace Et;
class DB_Query_Relations extends Object implements \Iterator,\Countable {

	/**
	 * @var DB_Query_Relations_Relation[]
	 */
	protected $relations = array();

	/**
	 * @var DB_Query
	 */
	protected $query;

	/**
	 * @param DB_Query $query
	 */
	function __construct(DB_Query $query){
		$this->query = $query;
	}

	/**
	 * @return DB_Query
	 */
	function getQuery(){
		return $this->query;

	}


	/**
	 * @param string $related_table_name
	 * @param string|null $join_type [optional]
	 * @param array $compare_statements [optional]
	 *
	 * @return \Et\DB_Query_Relations_Relation
	 */
	function addRelation($related_table_name, array $compare_statements = array(), $join_type = null){
		if(!$join_type){
			$join_type = DB_Query::JOIN_LEFT;
		}
		$this->relations[$related_table_name] = new DB_Query_Relations_Relation($this->query, $related_table_name, $join_type, $compare_statements);
		return $this->relations[$related_table_name];
	}

	/**
	 * @param string $related_table_name
	 * @return bool|DB_Query_Relations_Relation
	 */
	function getRelation($related_table_name){
		return isset($this->relations[$related_table_name])
				? $this->relations[$related_table_name]
				: false;
	}

	/**
	 * @param string $related_table_name
	 * @param array $join_on_columns array like (column_name => related_table_column_name)
	 * @param null|string $join_type [optional]
	 *
	 * @return \Et\DB_Query_Relations_Relation
	 */
	function addSimpleRelation($related_table_name, array $join_on_columns, $join_type = null){
		if(!$join_type){
			$join_type = DB_Query::JOIN_LEFT;
		}
		$this->relations[$related_table_name] = new DB_Query_Relations_Relation($this->query, $related_table_name, $join_type);
		$this->relations[$related_table_name]->addRelatedColumnsEqual($join_on_columns);
		return $this->relations[$related_table_name];
	}


	/**
	 * @return DB_Query_Relations_Relation[]
	 */
	function getRelations(){
		return $this->relations;
	}

	/**
	 * @return int
	 */
	function getRelationsCount(){
		return count($this->relations);
	}

	/**
	 * @return bool
	 */
	function isEmpty(){
		return !$this->relations;
	}

	/**
	 * @return DB_Query_Relations_Relation|bool
	 */
	public function current() {
		return current($this->relations);
	}


	public function next() {
		next($this->relations);
	}

	/**
	 * @return string|null
	 */
	public function key() {
		return key($this->relations);
	}

	/**
	 * @return bool
	 */
	public function valid() {
		return key($this->relations) !== null;
	}


	public function rewind() {
		reset($this->relations);
	}

	/**
	 * @return int
	 */
	public function count() {
		return $this->getRelationsCount();
	}


}