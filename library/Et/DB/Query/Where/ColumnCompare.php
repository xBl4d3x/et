<?php
namespace Et;
class DB_Query_Where_ColumnCompare extends DB_Query_Column {

	use DB_Query_Where_CompareTrait;

	/**
	 * @param DB_Query $query
	 * @param string $column_name
	 * @param string $compare_operator
	 * @param mixed|null|array|\Iterator|DB_Query $value [optional]
	 * @param null|string $table_name [optional]
	 * @throws DB_Query_Exception
	 */
	function __construct(DB_Query $query, $column_name, $compare_operator, $value = null, $table_name = null){
		parent::__construct($query, $column_name, $table_name);
		$this->setupValue($compare_operator, $value);
	}
}