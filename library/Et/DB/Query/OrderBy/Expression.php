<?php
namespace Et;
class DB_Query_OrderBy_Expression extends Object {


	/**
	 * @var DB_Expression
	 */
	protected $expression;

	/**
	 * @var string
	 */
	protected $order_how = DB_Query::ORDER_ASC;

	/**
	 * @param DB_Query $query
	 * @param string|DB_Expression $expression
	 * @param null|string $order_how [optional]
	 * @param null|string $table_name [optional]
	 */
	function __construct(DB_Query $query, $expression, $order_how = null, $table_name = null){
		$this->expression = new DB_Expression((string)$expression);

		if($table_name){
			$query->addTableToQuery($table_name);
		}

		if($order_how){
			$this->setOrderHow($order_how);
		}
	}

	/**
	 * @return string
	 */
	public function getOrderHow() {
		return $this->order_how;
	}

	/**
	 * @param string $order_how
	 */
	public function setOrderHow($order_how) {
		DB_Query::checkOrderHow($order_how);
		$this->order_how = $order_how;
	}

	/**
	 * @return DB_Expression
	 */
	function getExpression(){
		return $this->expression;
	}

}