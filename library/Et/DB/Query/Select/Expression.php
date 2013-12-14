<?php
namespace Et;
et_require_class('Object');
class DB_Query_Select_Expression extends Object {

	/**
	 * @var DB_Expression
	 */
	protected $expression;

	/**
	 * @var string
	 */
	protected $select_as = "";

	/**
	 * @param DB_Query $query
	 * @param string|DB_Expression $expression
	 * @param null|string $select_as [optional]
	 * @param null|string $table_name [optional]
	 */
	function __construct(DB_Query $query, $expression, $select_as = null, $table_name = null){
		$this->expression = new DB_Expression((string)$expression);

		if($table_name){
			$query->addTableToQuery($table_name);
		}

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
	 * @return DB_Expression
	 */
	function getExpression(){
		return $this->expression;
	}

	/**
	 * @param DB_Adapter_Abstract $db
	 * @return string
	 */
	function toSQL(DB_Adapter_Abstract $db){
		$output = (string)$this->getExpression();
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
		return (string)$this->getExpression() . ($this->getSelectAs() ? " AS {$this->getSelectAs()}" : "");
	}
}