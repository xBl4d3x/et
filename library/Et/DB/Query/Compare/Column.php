<?php
namespace Et;
class DB_Query_Compare_Column extends DB_Query_Column {

	use DB_Query_Compare_Trait;

	/**
	 * @param DB_Query $query
	 * @param string $column_name
	 * @param string $compare_operator
	 * @param mixed|null|array|\Iterator|DB_Query $value [optional]
	 * @throws DB_Query_Exception
	 */
	function __construct(DB_Query $query, $column_name, $compare_operator, $value = null){
		parent::__construct($query, $column_name);
		$this->setupValue($query, $compare_operator, $value);
	}
}