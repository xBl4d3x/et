<?php
namespace Et;
class DB_Query_Select_SubQuery extends Object {

	/**
	 * @var DB_Query
	 */
	protected $sub_query;

	/**
	 * @var string
	 */
	protected $select_as = "";

	/**
	 * @param DB_Query $query
	 * @param DB_Query $sub_query
	 * @param null|string $select_as [optional]
	 */
	function __construct(DB_Query $query, DB_Query $sub_query, $select_as = null){
		$this->sub_query = $sub_query;

		if($select_as !== null){
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
	 * @return DB_Query
	 */
	function getSubQuery(){
		return $this->sub_query;
	}
}