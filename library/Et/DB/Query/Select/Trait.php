<?php
namespace Et;
trait DB_Query_Select_Trait {

	/**
	 * @var string
	 */
	protected $select_as = "";

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
		if(!$select_as){
			$this->select_as = "";
			return;
		}
		DB_Query::checkColumnName($select_as);
		$this->select_as = $select_as;
	}
}