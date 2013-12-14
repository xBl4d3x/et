<?php
namespace Et;
et_require_class('Object');
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

	/**
	 * @param DB_Adapter_Abstract $db
	 * @return string
	 */
	function toSQL(DB_Adapter_Abstract $db = null){
		return parent::toSQL($db) . " " . $this->getComparePartAsSQL($db);
	}

	/**
	 * @return string
	 */
	function __toString(){
		return parent::__toString() . " " . $this->getComparePartAsString();
	}
}