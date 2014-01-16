<?php
namespace Et;
class DB_Query_Column extends DB_Table_Column {


	/**
	 * @param DB_Query $query
	 * @param string $column_name
	 */
	function __construct(DB_Query $query, $column_name){
		list($column_name, $table_name) = $query->resolveColumnAndTable($column_name);
		$this->column_name = $column_name;
		$this->table_name = $table_name;

		if($table_name){
			$query->addTableToQuery($table_name);
		}
	}
}