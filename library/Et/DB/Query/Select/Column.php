<?php
namespace Et;
class DB_Query_Select_Column extends DB_Query_Column {

	use DB_Query_Select_Trait;

	/**
	 * @param DB_Query $query
	 * @param string $column_name
	 * @param null|string $table_name [optional]
	 * @param null|string $select_as [optional]
	 */
	function __construct(DB_Query $query, $column_name, $table_name = null, $select_as = null){
		parent::__construct($query, $column_name, $table_name);
		$this->setSelectAs($select_as);
	}

}