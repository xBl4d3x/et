<?php
namespace Et;
class DB_Table_Column extends Object {

	/**
	 * @var string
	 */
	protected $column_name;

	/**
	 * @var string
	 */
	protected $table_name;

	/**
	 * @param string $column_name
	 * @param null|string $table_name [optional]
	 */
	function __construct($column_name, $table_name = null){

		DB::checkColumnName($column_name);
		if(strpos($column_name, ".") !== false){
			list($table_name, $column_name) = explode(".", $column_name, 2);
		} elseif($table_name) {
			DB::checkTableName($table_name);
		}

		$this->column_name = $column_name;
		$this->table_name = $table_name;
	}

	/**
	 * @param bool $including_table_name [optional]
	 * @return string
	 */
	public function getColumnName($including_table_name = false) {
		if(!$including_table_name || !$this->table_name){
			return $this->column_name;
		}
		return "{$this->table_name}.{$this->column_name}";
	}

	/**
	 * @return string|null
	 */
	public function getTableName() {
		return $this->table_name;
	}

	/**
	 * @return string
	 */
	function __toString(){
		return $this->getColumnName(true);
	}

}