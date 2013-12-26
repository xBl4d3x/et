<?php
namespace Et;
class DB_Query_OrderBy_ColumnNumber extends Object {

	/**
	 * @var string
	 */
	protected $order_how = DB_Query::ORDER_ASC;

	/**
	 * @var int
	 */
	protected $column_number;

	/**
	 * @param int $column_number
	 * @param null|string $order_how [optional]
	 */
	function __construct($column_number, $order_how = null){
		$this->column_number = max(0, (int)$column_number);
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
	 * @return int
	 */
	public function getColumnNumber() {
		return $this->column_number;
	}
}