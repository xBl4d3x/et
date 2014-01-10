<?php
namespace Et;
class DB_Query_Select_SubQuery extends Object {

	/**
	 * @var DB_Query
	 */
	protected $sub_query;

	use DB_Query_Select_Trait;

	/**
	 * @param DB_Query $query
	 * @param DB_Query $sub_query
	 * @param null|string $select_as [optional]
	 */
	function __construct(DB_Query $query, DB_Query $sub_query, $select_as = null){
		$this->sub_query = $sub_query;
		$this->setSelectAs($select_as);
	}

	/**
	 * @return DB_Query
	 */
	function getSubQuery(){
		return $this->sub_query;
	}
}