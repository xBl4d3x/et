<?php
namespace Et;
et_require_class('Object');
class DB_Query_Relations_ComplexRelation extends DB_Query_Where {

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
	 * @param array $join_expressions [related_column_name => expression]
	 * @param null|string $join_type [optional] NULL = by default query join type
	 * @throws DB_Query_Exception
	 */
	function __construct(DB_Query $query, $related_table_name, array $join_expressions, $join_type = null){
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

		if(!$join_expressions){
			throw new DB_Query_Exception(
				"No expressions defined for relation with {$this->related_table_name}",
				DB_Query_Exception::CODE_INVALID_COLUMN_NAME
			);
		}

		$expressions = array();
		foreach($join_expressions as $related_column_name => $expression){
			if(is_numeric($related_column_name)){
				continue;
			}
			if(strpos($related_column_name, ".") === false){
				$related_column_name = "{$related_table_name}.{$related_column_name}";
			}
			$expressions[$related_column_name] = $expression;
		}

		parent::__construct($query, $expressions);
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
	 * @param DB_Adapter_Abstract $db [optional]
	 * @param int $offset [optional]
	 * @return string
	 */
	function toSQL(DB_Adapter_Abstract $db = null, $offset = 0){
		if(!$db){
			$db = $this->getQuery()->getDB();
		}
		$padding = str_repeat("\t", $offset);
		$output = "{$padding}{$this->getJoinType()} JOIN {$db->quoteTableName($this->getRelatedTableName())} ON (\n";
		$output .= parent::toSQL($db, $offset + 1) . "\n";
		$output .= "{$padding})";
		return $output;
	}

	/**
	 * @return string
	 */
	function __toString(){
		$output = "{$this->getJoinType()} JOIN {$this->getRelatedTableName()} ON (\n\t";

		$output .= str_replace("\n", "\n\t", parent::__toString()) . "\n";
		$output .= ")";
		return $output;
	}


}