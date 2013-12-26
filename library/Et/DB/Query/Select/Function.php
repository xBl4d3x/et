<?php
namespace Et;
class DB_Query_Select_Function extends DB_Query_Function {

	/**
	 * @var string
	 */
	protected $select_as;

	/**
	 * @param DB_Query $query
	 * @param string $function_name
	 * @param array|DB_Query_Column[] $function_arguments [optional]
	 * @param null|string $select_as [optional]
	 */
	function __construct(DB_Query $query, $function_name, array $function_arguments = array(), $select_as = null){
		parent::__construct($query, $function_name, $function_arguments);
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