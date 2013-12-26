<?php
namespace Et;
class DB_Query_Relations_SimpleRelation extends Object {

	/**
	 * @var string
	 */
	protected $related_table_name;

	/**
	 * @var array
	 */
	protected $join_on_columns = array();

	/**
	 * @var string
	 */
	protected $join_type;

	/**
	 * @var DB_Query
	 */
	protected $query;

	/**
	 * @param DB_Query $query
	 * @param string $related_table_name
	 * @param array $join_on_columns [related_column_name => other_column_name]
	 * @param null|string $join_type [optional] NULL = by default query join type
	 */
	function __construct(DB_Query $query, $related_table_name, array $join_on_columns, $join_type = null){
		$this->query = $query;
		if(!$join_type){
			$join_type = $query->getDefaultJoinType();
		} else {
			$query->checkJoinType($join_type);
		}
		$this->join_type = $join_type;

		$query->addTableToQuery($related_table_name);
		if($related_table_name == DB_Query::MAIN_TABLE_ALIAS){
			$related_table_name = $query->getMainTableName();
		}
		$this->related_table_name = $related_table_name;

		$this->setJoinOnColumns($query, $join_on_columns);
	}

	/**
	 * @param DB_Query $query
	 * @param array $join_on_columns
	 * @throws DB_Query_Exception
	 */
	protected function setJoinOnColumns(DB_Query $query, array $join_on_columns){
		if(!$join_on_columns){
			throw new DB_Query_Exception(
				"No columns defined for relation with {$this->related_table_name}",
				DB_Query_Exception::CODE_INVALID_COLUMN_NAME
			);
		}

		foreach($join_on_columns as $related_column_name => $other_column_name){
			list($other_column_name, $other_table_name) = $query->resolveColumnAndTable($other_column_name);
			$query->checkColumnName($related_column_name);
			if(strpos($related_column_name, ".") === false){
				$related_table_name = $this->related_table_name;
			} else {
				list($related_column_name, $related_table_name) = $query->resolveColumnAndTable($related_column_name);
			}
			$query->addTableToQuery($related_table_name);
			$query->addTableToQuery($other_table_name);
			$this->join_on_columns["{$related_table_name}.{$related_column_name}"] = "{$other_table_name}.{$other_column_name}";
		}
	}

	/**
	 * @return array
	 */
	public function getJoinOnColumns() {
		return $this->join_on_columns;
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
	 * @return DB_Query
	 */
	function getQuery(){
		return $this->query;
	}
}