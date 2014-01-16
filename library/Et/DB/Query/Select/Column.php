<?php
namespace Et;
class DB_Query_Select_Column extends DB_Query_Column {

	use DB_Query_Select_Trait;

	/**
	 * @param DB_Query $query
	 * @param string $column_name
	 * @param null|string $select_as [optional]
	 */
	function __construct(DB_Query $query, $column_name, $select_as = null){
		parent::__construct($query, $column_name);
		$this->setSelectAs($select_as);
	}
}