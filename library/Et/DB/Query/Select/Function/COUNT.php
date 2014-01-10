<?php
namespace Et;
class DB_Query_Select_Function_COUNT extends DB_Query_Select_Function {

	/**
	 * @param DB_Query $query
	 * @param null|string|DB_Table_Column $count_column_name
	 * @param null|string $select_as
	 */
	function __construct(DB_Query $query, $count_column_name = null, $select_as = null){

		if(!$count_column_name instanceof DB_Query_Column){

			if($count_column_name && $count_column_name != DB_Query::ALL_COLUMNS){

				$count_column_name = $query->getColumn($count_column_name);

			} else {

				$count_column_name = new DB_Expression(DB_Query::ALL_COLUMNS);

			}

		}

		parent::__construct($query, "COUNT", array($count_column_name), $select_as);
	}
}