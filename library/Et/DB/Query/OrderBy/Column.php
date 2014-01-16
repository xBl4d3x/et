<?php
namespace Et;
class DB_Query_OrderBy_Column extends DB_Query_Column {

	/**
	 * @var string
	 */
	protected $order_how = DB_Query::ORDER_ASC;

	/**
	 * @param DB_Query $query
	 * @param string $column_name
	 * @param null|string $order_how [optional]
	 */
	function __construct(DB_Query $query, $column_name, $order_how = null){
		if(is_numeric($column_name)){
			$this->column_name = (int)$column_name;
		} else {
			parent::__construct($query, $column_name);
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
}