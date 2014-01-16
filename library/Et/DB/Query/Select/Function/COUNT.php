<?php
namespace Et;
class DB_Query_Select_Function_COUNT extends DB_Query_Select_Function {

	/**
	 * @param DB_Query $query
	 * @param null|string|DB_Table_Column $count_column
	 * @param null|string $select_as
	 */
	function __construct(DB_Query $query, $count_column = null, $select_as = null){

		$count_column = (string)$count_column;

		if(!$count_column || $count_column == DB_Query::ALL_COLUMNS){
			$arguments = array(new DB_Query_Select_AllColumns($query));
			parent::__construct($query, "COUNT", $arguments, $select_as);
			return;
		}


		if(strpos($count_column, ".") === false){
			$count_column = DB_Query::MAIN_TABLE_ALIAS . ".{$count_column}";
		}

		list($table_name, $column_name) = explode(".", $count_column, 2);
		if($column_name == DB_Query::ALL_COLUMNS){
			$arguments = array(new DB_Query_Select_AllColumns($query, $table_name));
			parent::__construct($query, "COUNT", $arguments, $select_as);
			return;
		}

		$arguments = array($query->getColumn("{$table_name}.{$column_name}"));
		parent::__construct($query, "COUNT", $arguments, $select_as);
	}
}