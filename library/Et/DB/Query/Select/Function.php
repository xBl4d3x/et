<?php
namespace Et;
class DB_Query_Select_Function extends DB_Query_Function {

	use DB_Query_Select_Trait;

	/**
	 * @param DB_Query $query
	 * @param string $function_name
	 * @param array|DB_Query_Column[] $function_arguments [optional]
	 * @param null|string $select_as [optional]
	 */
	function __construct(DB_Query $query, $function_name, array $function_arguments = array(), $select_as = null){
		parent::__construct($query, $function_name, $function_arguments);
		$this->setSelectAs($select_as);
	}

}