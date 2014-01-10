<?php
namespace Et;
class DB_Query_Select_Expression extends Object {

	use DB_Query_Select_Trait;

	/**
	 * @var DB_Expression
	 */
	protected $expression;

	/**
	 * @param DB_Query $query
	 * @param string|DB_Expression $expression
	 * @param null|string $select_as [optional]
	 * @param null|string $table_name [optional]
	 */
	function __construct(DB_Query $query, $expression, $select_as = null, $table_name = null){
		if(!$expression instanceof DB_Expression){
			$expression = new DB_Expression((string)$expression);
		}
		$this->expression = $expression;

		if($table_name){
			$query->addTableToQuery($table_name);
		}

		$this->setSelectAs($select_as);
	}

	/**
	 * @return DB_Expression
	 */
	function getExpression(){
		return $this->expression;
	}
}