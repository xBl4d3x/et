<?php
namespace Et;
class DB_Query_Compare_Function extends DB_Query_Function {

	use DB_Query_Compare_Trait;


	/**
	 * @param DB_Query $query
	 * @param string $function_name
	 * @param array|DB_Table_Column[]|DB_Expression[]|DB_Query[] $function_arguments [optional]
	 * @param string $compare_operator
	 * @param mixed|null|array|\Iterator|DB_Query $value [optional]
	 */
	function __construct(DB_Query $query, $function_name, array $function_arguments,  $compare_operator, $value = null){
		parent::__construct($query, $function_name, $function_arguments);
		$this->setupValue($compare_operator, $value);
	}

}