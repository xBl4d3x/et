<?php
namespace Et;
class DB_Query_Select_AllColumns extends Object {

	/**
	 * @var string
	 */
	protected $table_name;

	/**
	 * @param DB_Query $query
	 * @param null|string $table_name [optional]
	 */
	function __construct(DB_Query $query, $table_name = null){
		if($table_name){
			$table_name = $query->resolveTableName($table_name);
			$query->addTableToQuery($table_name);
			$this->table_name = $table_name;

		}
	}

	/**
	 * @return null|string
	 */
	function getTableName(){
		return $this->table_name;
	}
}