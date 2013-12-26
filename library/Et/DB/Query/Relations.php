<?php
namespace Et;
class DB_Query_Relations extends Object implements \Iterator,\Countable {

	/**
	 * @var DB_Query_Relations_ComplexRelation[]|DB_Query_Relations_SimpleRelation[]
	 */
	protected $relations = array();

	/**
	 * @var DB_Query
	 */
	protected $query;

	/**
	 * @param DB_Query $query
	 * @param array $relations [optional]
	 */
	function __construct(DB_Query $query, array $relations = array()){
		$this->query = $query;
		if($relations){
			$this->setRelations($relations);
		}
	}

	/**
	 * @return DB_Query
	 */
	function getQuery(){
		return $this->query;

	}

	/**
	 * @param array $tables_relations
	 * @param bool $merge [optional]
	 * @return static
	 */
	function setRelations(array $tables_relations, $merge = true){
		if(!$merge){
			$this->relations = array();
		}

		foreach($tables_relations as $table_name => $relations){
			$is_simple = false;
			foreach($relations as $k => $v){
				if(is_numeric($k) || is_array($v)){
					$is_simple = false;
					break;
				}

				if(!$v instanceof DB_Query_Column && !preg_match('~^\w+(:?\.\w+)?$~', $v)){
					$is_simple = false;
				}
			}

			if($is_simple){
				$this->addSimpleRelation($table_name, $relations);
			} else {
				$this->addComplexRelation($table_name, $relations);
			}
		}


		return $this;
	}

	/**
	 * @return DB_Query_Relations_ComplexRelation[]|DB_Query_Relations_SimpleRelation[]
	 */
	function getRelations(){
		return $this->relations;
	}

	/**
	 * @param string $related_table_name
	 * @param array $join_on_columns [related_column_name => other_column_name]
	 * @param null|string $join_type [optional] NULL = by default query join type
	 * @return static
	 */
	function addSimpleRelation($related_table_name, array $join_on_columns, $join_type = null){
		$relation = new DB_Query_Relations_SimpleRelation($this->getQuery(), $related_table_name, $join_on_columns, $join_type);
		$this->relations[] = $relation;
		return $this;
	}

	/**
	 * @param string $related_table_name
	 * @param array $join_expressions [related_column_name => other_column_name]
	 * @param null|string $join_type [optional] NULL = by default query join type
	 * @return static
	 */
	function addComplexRelation($related_table_name, array $join_expressions, $join_type = null){
		$relation = new DB_Query_Relations_ComplexRelation($this->getQuery(), $related_table_name, $join_expressions, $join_type);
		$this->relations[] = $relation;
		return $this;
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
	 * @return DB_Query_Relations_ComplexRelation|DB_Query_Relations_SimpleRelation
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