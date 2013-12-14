<?php
namespace Et;
et_require_class('DB_Query_Select_Column');
class DB_Query_Select_Count extends DB_Query_Select_Column {

	const COUNT_ALL = "*";

	/**
	 * @var string
	 */
	protected $select_as = "";

	/**
	 * @param DB_Query $query
	 * @param string $column_name [optional]
	 * @param null|string $table_name [optional]
	 * @param null|string $select_as [optional]
	 */
	function __construct(DB_Query $query, $column_name = self::COUNT_ALL, $table_name = null, $select_as = null){
		$is_all = strpos($column_name, self::COUNT_ALL) !== false;
		if($table_name){
			$table_name = $query->resolveTableName($table_name);
		}
		$table_name_set = $table_name || strpos($column_name, ".") !== false;
		if($is_all){
			$column_name = str_replace(self::COUNT_ALL, "___dummy___", $column_name);
		}
		parent::__construct($query, $column_name, $table_name, $select_as);
		if($is_all){
			$this->column_name = self::COUNT_ALL;
			if(!$table_name_set){
				$this->table_name = "";
			}
		}
	}

	/**
	 * @return bool
	 */
	function isCountAll(){
		return $this->column_name == self::COUNT_ALL;
	}

	/**
	 * @param DB_Adapter_Abstract $db
	 * @return string
	 */
	function toSQL(DB_Adapter_Abstract $db){

		if($this->isCountAll()){
			if($this->getTableName()){
				$output = "COUNT({$db->quoteTableName($this->getTableName())}.*)";
			} else {
				$output = "COUNT(*)";
			}
		} else {
			$output = "COUNT(" . $db->quoteColumnName("{$this->getColumnName()}.{$this->getTableName()}") . ")";
		}

		$select_as = $this->getSelectAs();

		if($select_as){
			$output .= " AS {$db->quoteColumnName($select_as)}";
		}

		return $output;
	}

	/**
	 * @return string
	 */
	function __toString(){
		$output = "";
		if($this->isCountAll()){
			if($this->getTableName()){
				$output = "COUNT({$this->getTableName()}.*)";
			} else {
				$output = "COUNT(*)";
			}
		}
		if(!$output){
			$output = "COUNT({$this->getTableName()}.{$this->getColumnName()})";
		}

		if($this->getSelectAs()){
			$output .= " AS " . $this->getSelectAs();
		}

		return $output;
	}
}