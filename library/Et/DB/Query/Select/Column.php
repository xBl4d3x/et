<?php
namespace Et;
et_require_class('DB_Query_Column');
class DB_Query_Select_Column extends DB_Query_Column {

	/**
	 * @var string
	 */
	protected $select_as = "";

	/**
	 * @param DB_Query $query
	 * @param string $column_name
	 * @param null|string $table_name [optional]
	 * @param null|string $select_as [optional]
	 */
	function __construct(DB_Query $query, $column_name, $table_name = null, $select_as = null){

		parent::__construct($query, $column_name, $table_name);
		if($select_as){
			$this->setSelectAs($select_as);
		}
	}

	/**
	 * @return string
	 */
	public function getSelectAs() {
		return $this->select_as;
	}

	/**
	 * @param string $select_as
	 */
	protected function setSelectAs($select_as) {
		DB_Query::checkColumnName($select_as);
		$this->select_as = $select_as;
	}

	/**
	 * @param DB_Adapter_Abstract $db
	 * @return string
	 */
	function toSQL(DB_Adapter_Abstract $db){
		$output = parent::toSQL($db);
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
		return "{$this->getTableName()}.{$this->getColumnName()}" . ($this->getSelectAs() ? " AS {$this->getSelectAs()}" : "");
	}
}