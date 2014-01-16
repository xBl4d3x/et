<?php
namespace Et;
class DB_Query_Relations_Relation extends DB_Query_Compare {

	/**
	 * @var string
	 */
	protected $related_table_name;

	/**
	 * @var string
	 */
	protected $join_type;

	/**
	 * @param DB_Query $query
	 * @param string $related_table_name
	 * @param string $join_type
	 * @param array $statements [optional]
	 */
	function __construct(DB_Query $query, $related_table_name, $join_type, array $statements = array()){
		parent::__construct($query, $statements);
		$this->related_table_name = $query->resolveTableName($related_table_name);
		$query->addTableToQuery($this->related_table_name);
		$query->checkJoinType($join_type);
		$this->join_type = $join_type;

	}

	/**
	 * @return string
	 */
	public function getJoinType() {
		return $this->join_type;
	}

	/**
	 * @return string
	 */
	public function getRelatedTableName() {
		return $this->related_table_name;
	}


	/**
	 * @param string $column_name
	 * @param string $compare_operator
	 * @param string $related_column_name
	 * @return DB_Query_Relations_Relation|static
	 */
	function addRelatedColumnCompare($column_name, $compare_operator, $related_column_name){

		if(strpos((string)$column_name, ".") === false){
			$column_name = $this->query->getMainTableName() . ".{$column_name}";
		}

		if(strpos((string)$related_column_name, ".") === false){
			$related_column_name = $this->getRelatedTableName() . ".{$related_column_name}";
		}

		return $this->addColumnCompare(
			$column_name,
			$compare_operator,
			$related_column_name
		);
	}

	/**
	 * @param string $column_name
	 * @param string $related_column_name
	 * @return DB_Query_Relations_Relation|static
	 */
	function addRelatedColumnEquals($column_name, $related_column_name){
		return $this->addRelatedColumnCompare(
			$column_name,
			DB_Query::CMP_EQUALS,
			$related_column_name
		);
	}

	/**
	 * @param array $columns array(column_name => related_column_name)
	 * @return DB_Query_Relations_Relation|static
	 */
	function addRelatedColumnsEqual(array $columns){
		foreach($columns as $column_name => $related_column_name){
			$this->addRelatedColumnEquals($column_name, $related_column_name);
		}
		return $this;
	}

	/**
	 * @param array|DB_Query_Compare $statements
	 * @throws DB_Query_Exception
	 * @return static|DB_Query_Compare
	 */
	function addNestedStatements($statements){
		if(!$statements instanceof DB_Query_Compare){

			if(!is_array($statements)){
				throw new DB_Query_Exception(
					"Nested statements must be array or instance of Et\\DB_Query_Compare",
					DB_Query_Exception::CODE_NOT_PERMITTED
				);
			}

			if(!$statements){
				return $this;
			}
			$where = new static($this->getQuery(), $this->getRelatedTableName(), $this->getJoinType(), $statements);

		} else {

			if($statements->getQuery() !== $this->query){
				throw new DB_Query_Exception(
					"Trying to pass expression from different query - not allowed",
					DB_Query_Exception::CODE_NOT_PERMITTED
				);
			}

			$where = $statements;
		}

		$this->_addANDIfNecessary();
		$this->statements[] = $where;
		return $this;

	}

}
