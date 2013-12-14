<?php
namespace Et;
et_require_class('Object');
class DB_Query_OrderBy extends Object implements \Iterator,\Countable {

	/**
	 * @var DB_Query_OrderBy_Column[]|DB_Query_OrderBy_Expression[]|DB_Query_OrderBy_ColumnNumber[]
	 */
	protected $order_by_expressions = array();

	/**
	 * @var DB_Query
	 */
	protected $query;

	/**
	 * @param DB_Query $query
	 * @param array $order_by_expressions [optional] array like: [column_name => ASC, ... ] or [1 => ASC, .... ]
	 */
	function __construct(DB_Query $query, array $order_by_expressions = array()){
		$this->query = $query;
		if($order_by_expressions){
			$this->setOrderByExpressions($order_by_expressions);
		}
	}

	/**
	 * @return DB_Query
	 */
	function getQuery(){
		return $this->query;

	}

	/**
	 * @return static|DB_Query_OrderBy
	 */
	function removeExpressions(){
		$this->order_by_expressions = array();
		return $this;
	}

	/**
	 * @param array $order_by_expressions Array like [column_name => ASC, ... ] or [1 => ASC, .... ]
	 * @param bool $merge [optional]
	 * @return static
	 */
	function setOrderByExpressions(array $order_by_expressions, $merge = true){
		if(!$merge){
			$this->order_by_expressions = array();
		}

		foreach($order_by_expressions as $k => $v){
			if(is_numeric($k)){
				$this->addOrderByColumnNumber($k, $v);
			} else {
				$this->addOrderByColumn($k, $v);
			}
		}

		return $this;
	}

	/**
	 * @param string $column_name
	 * @param null|string $order_how  [optional]
	 * @param null|string $table_name [optional]
	 * @return static
	 */
	function setPrimaryOrderByColumn($column_name, $order_how = null, $table_name = null){
		$column = new DB_Query_OrderBy_Column($this->getQuery(), $column_name, $table_name, $order_how);
		if(!$this->order_by_expressions){
			$this->order_by_expressions[(string)$column] = $column;
		} else{
			$this->order_by_expressions = array((string)$column => $column) + $this->order_by_expressions;
		}

		return $this;
	}

	/**
	 * @param string $column_name
	 * @param null|string $order_how  [optional]
	 * @param null|string $table_name [optional]
	 * @return static
	 */
	function addOrderByColumn($column_name, $order_how = null, $table_name = null){
		$column = new DB_Query_OrderBy_Column($this->getQuery(), $column_name, $table_name, $order_how);
		$this->order_by_expressions[(string)$column] = $column;
		return $this;
	}

	/**
	 * @param array $columns
	 * @param null|string $table_name [optional]
	 * @return static
	 */
	function addOrderByColumns(array $columns, $table_name = null){
		foreach($columns as $column => $how){
			$this->addOrderByColumn($column, $how, $table_name);
		}
		return $this;
	}

	/**
	 * @param int $column_number
	 * @param null|string $order_how [optional]
	 * @return static
	 */
	function addOrderByColumnNumber($column_number, $order_how = null){
		$column = new DB_Query_OrderBy_ColumnNumber($this->getQuery(), $column_number, $order_how);
		$this->order_by_expressions[$column->getColumnNumber()] = $column;
		return $this;
	}

	/**
	 * @param string|DB_Expression $expression
	 * @param null|string $order_how  [optional]
	 * @param null|string $table_name [optional]
	 * @return static
	 */
	function addOrderByExpression($expression, $order_how = null, $table_name = null){
		$column = new DB_Query_OrderBy_Expression($this->getQuery(), $expression, $order_how, $table_name);
		$this->order_by_expressions[(string)$expression] = $column;
		return $this;
	}

	/**
	 * @return bool
	 */
	function isEmpty(){
		return !$this->order_by_expressions;
	}

	/**
	 * @return int
	 */
	function getExpressionsCount(){
		return count($this->order_by_expressions);
	}

	/**
	 * @return DB_Query_OrderBy_Column[]|DB_Query_OrderBy_ColumnNumber[]|DB_Query_OrderBy_Expression[]
	 */
	function getOrderByExpressions(){
		return $this->order_by_expressions;
	}

	/**
	 * @param DB_Adapter_Abstract $db [optional]
	 * @param int $offset [optional]
	 * @return string
	 */
	function toSQL(DB_Adapter_Abstract $db = null, $offset = 0){
		if($this->isEmpty()){
			return "";
		}
		$expressions = $this->getOrderByExpressions();
		$padding = str_repeat("\t", $offset);
		$output = array();

		foreach($expressions as $expression){
			$output[] = $expression->toSQL($db);
		}

		return $padding . implode(",\n{$padding}", $output);
	}

	function __toString(){
		if($this->isEmpty()){
			return "";
		}
		$expressions = $this->getOrderByExpressions();
		$output = array();

		foreach($expressions as $expression){
			$output[] = (string)$expression;
		}

		return implode(",\n", $output);
	}


	/**
	 * @return DB_Query_OrderBy_Column|DB_Query_OrderBy_Expression|DB_Query_OrderBy_ColumnNumber|bool
	 */
	public function current() {
		return current($this->order_by_expressions);
	}


	public function next() {
		next($this->order_by_expressions);
	}

	/**
	 * @return string|null
	 */
	public function key() {
		return key($this->order_by_expressions);
	}

	/**
	 * @return bool
	 */
	public function valid() {
		return key($this->order_by_expressions) !== null;
	}


	public function rewind() {
		reset($this->order_by_expressions);
	}

	/**
	 * @return int
	 */
	public function count() {
		return $this->getExpressionsCount();
	}
}