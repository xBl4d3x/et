<?php
namespace Et;
class DB_Query_Column extends DB_Table_Column {


	/**
	 * @param DB_Query $query
	 * @param string $column_name
	 * @param null|string $table_name [optional]
	 */
	function __construct(DB_Query $query, $column_name, $table_name = null){
		list($column_name, $table_name) = $query->resolveColumnAndTable($column_name, $table_name);

		$this->column_name = $column_name;
		$this->table_name = $table_name;

		$query->addTableToQuery($table_name);
	}

	/**
	 * @param DB_Adapter_Abstract $db [optional]
	 * @return string
	 */
	function toSQL(DB_Adapter_Abstract $db){
		return $db->quoteColumnName("{$this->getTableName()}.{$this->getColumnName()}");
	}
}