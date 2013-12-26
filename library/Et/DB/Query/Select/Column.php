<?php
namespace Et;
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

}