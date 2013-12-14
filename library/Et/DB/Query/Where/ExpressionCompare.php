<?php
namespace Et;
et_require_class('Object');
class DB_Query_Where_ExpressionCompare extends Object {

	use DB_Query_Where_CompareTrait;

	/**
	 * @var DB_Expression
	 */
	protected $expression;

	/**
	 * @param DB_Query $query
	 * @param string|DB_Expression $expression
	 * @param string $compare_operator [optional]
	 * @param mixed|null|array|\Iterator|DB_Query $value [optional]
	 * @param null|string $table_name [optional]
	 * @throws DB_Query_Exception
	 */
	function __construct(DB_Query $query, $expression, $compare_operator = null, $value = null, $table_name = null){
		if($table_name){
			$query->addTableToQuery($table_name);
		}
		if(!$expression instanceof DB_Expression){
			$expression = new DB_Expression((string)$expression);
		}
		$this->expression = $expression;
		if($compare_operator !== null){
			$this->setupValue($compare_operator, $value);
		}
	}

	/**
	 * @return DB_Expression
	 */
	function getExpression(){
		return $this->expression;
	}

	/**
	 * @param DB_Adapter_Abstract $db
	 * @return string
	 */
	function toSQL(DB_Adapter_Abstract $db = null){
		if($this->compare_operator !== null){
			return (string)$this->getExpression() . " " . $this->getComparePartAsSQL($db);
		} else {
			return (string)$this->getExpression();
		}
	}

	/**
	 * @return string
	 */
	function __toString(){
		if($this->compare_operator !== null){
			return (string)$this->getExpression() . " " . $this->getComparePartAsString();
		} else {
			return (string)$this->getExpression();
		}

	}

}