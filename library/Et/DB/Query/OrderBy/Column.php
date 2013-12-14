<?php
namespace Et;
et_require_class('Object');
class DB_Query_OrderBy_Column extends DB_Query_Column {

	/**
	 * @var string
	 */
	protected $order_how = DB_Query::ORDER_ASC;

	/**
	 * @param DB_Query $query
	 * @param string $column_name
	 * @param null|string $table_name [optional]
	 * @param null|string $order_how [optional]
	 */
	function __construct(DB_Query $query, $column_name, $table_name = null, $order_how = null){

		parent::__construct($query, $column_name, $table_name);
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
	 * @param DB_Adapter_Abstract $db [optional]
	 * @return string
	 */
	function toSQL(DB_Adapter_Abstract $db = null){
		return parent::toSQL($db) . " {$this->getOrderHow()}";
	}

	/**
	 * @return string
	 */
	function __toString(){
		return parent::__toString() . " {$this->getOrderHow()}";
	}
}