<?php
namespace Et;
class DB_Query_Select extends Object implements \Iterator,\Countable {

	/**
	 * @var DB_Query_Select_AllColumns[]|DB_Query_Select_Column[]|DB_Query_Select_Function[]|DB_Query_Select_Expression[]|DB_Query_Select_SubQuery[]
	 */
	protected $expressions = array();

	/**
	 * @var DB_Query
	 */
	protected $query;

	/**
	 * @param DB_Query $query
	 * @param array $expressions [optional]
	 */
	function __construct(DB_Query $query, array $expressions = array()){
		$this->query = $query;
		if($expressions){
			$this->setExpressions($expressions);
		}
	}

	/**
	 * @return DB_Query
	 */
	function getQuery(){
		return $this->query;

	}

	/**
	 * @return bool
	 */
	function isEmpty(){
		return !$this->expressions;
	}

	/**
	 * @return int
	 */
	function getExpressionsCount(){
		return count($this->expressions);
	}

	/**
	 * @return static|DB_Query_Select
	 */
	function removeExpressions(){
		$this->expressions = array();
		return $this;
	}

	/**
	 * @param array $expressions
	 * @param bool $merge [optional]
	 * @return static
	 * @throws DB_Query_Exception
	 */
	function setExpressions(array $expressions, $merge = true){
		if(!$merge){
			$this->expressions = array();
		}

		foreach($expressions as $k => $expression){
			if(is_numeric($k)){
				$select_as = null;
			} else {
				$select_as = $k;
			}

			// expression
			if($expression instanceof DB_Expression){
				$this->addExpression($expression, null, $select_as);
				continue;
			}

			// sub query
			if($expression instanceof DB_Query){
				$this->addSubQuery($expression, $select_as);
				continue;
			}
			
			$expression = trim($expression);

			// column
			if(preg_match('~^\w+(?:\.\w+)?$~', $expression)){
				$this->addColumn($expression, null, $select_as);
				continue;
			}

			// all table columns
			if(preg_match('~^(\w+\.\*|\*)$~', $expression)){
				if(strpos($expression, ".") !== false){
					list($table_name) = explode(".", $expression);
					$this->addAllColumns($table_name);
				} else {
					$this->addAllColumns();
				}
				continue;
			}
						
			// COUNT
			if(preg_match('~COUNT\((\*|\w+(?:\.\w+)?)\)~is', $expression, $m)){
				list(, $expression) = $m;
				$this->addCount($expression, null, $select_as);
				continue;
			}

			// anything else
			throw new DB_Query_Exception(
				"Failed to determine expression type for " . get_class($this) . "::setExpressions() for expression '{$expression}'",
				DB_Query_Exception::CODE_INVALID_EXPRESSION
			);
		}
		
		return $this;
	}

	/**
	 * @return DB_Query_Select_AllColumns[]|DB_Query_Select_Column[]|DB_Query_Select_Function[]|DB_Query_Select_Expression[]|DB_Query_Select_SubQuery[]
	 */
	function getExpressions(){
		return $this->expressions;
	}

	/**
	 * @param string $column_name
	 * @param null|string $table_name [optional]
	 * @param null|string $select_as [optional]
	 * @return static
	 */
	function addColumn($column_name, $table_name = null, $select_as = null){
		$column = new DB_Query_Select_Column($this->getQuery(), $column_name, $table_name, $select_as);
		$this->expressions[] = $column;
		return $this;
	}


	/**
	 * @param string $function_name
	 * @param array|DB_Table_Column[]|DB_Expression[]|DB_Query[] $function_arguments [optional]
	 * @param null|string $select_as
	 * @return static
	 */
	function addFunction($function_name, array $function_arguments = array(), $select_as = null){
		$this->expressions[] = new DB_Query_Select_Function($this->getQuery(), $function_name, $function_arguments, $select_as);
		return $this;
	}

	/**
	 * @param array $columns
	 * @param null|string $table_name [optional]
	 * @return static
	 */
	function addColumns(array $columns, $table_name = null){
		foreach($columns as $k => $column){
			$this->addColumn($column, $table_name, is_numeric($k) ? null : $k);
		}
		return $this;
	}

	/**
	 * @param string $column_name [optional] Use '*' for "all columns"
	 * @param null|string $table_name [optional]
	 * @param null|string $select_as [optional]
	 * @return static
	 */
	function addCount($column_name = "*", $table_name = null, $select_as = null){
		if($column_name != "*"){
			$column_name = $this->query->column($column_name, $table_name);
		}
		$this->expressions[] = new DB_Query_Select_Function_COUNT($this->getQuery(), $column_name, $select_as);
		return $this;
	}

	/**
	 * @param null|string $table_name [optional]
	 * @return static
	 */
	function addAllColumns($table_name = null){
		$column = new DB_Query_Select_AllColumns($this->getQuery(), $table_name);
		$this->expressions[] = $column;
		return $this;
	}

	/**
	 * @param string|DB_Expression $expression
	 * @param null|string $table_name [optional]
	 * @param null|string $select_as [optional]
	 * @return static
	 */
	function addExpression($expression, $table_name = null, $select_as = null){
		$column = new DB_Query_Select_Expression($this->getQuery(), $expression, $select_as, $table_name);
		$this->expressions[] = $column;
		return $this;
	}

	/**
	 * @param DB_Query $sub_query
	 * @param null|string $select_as [optional]
	 * @return static
	 */
	function addSubQuery(DB_Query $sub_query, $select_as = null){
		$column = new DB_Query_Select_SubQuery($this->getQuery(), $sub_query, $select_as);
		$this->expressions[] = $column;
		return $this;
	}




	/**
	 * @param DB_Adapter_Abstract $db [optional]
	 * @param int $offset [optional]
	 * @return string
	 */
	function toSQL(DB_Adapter_Abstract $db = null, $offset = 0){
		$padding = str_repeat("\t", $offset);
		if($this->isEmpty()){
			return "{$padding}*";
		}
		$expressions = $this->getExpressions();
		$output = array();

		foreach($expressions as $expression){
			$output[] = $expression->toSQL($db);
		}

		return $padding . implode(",\n{$padding}", $output);
	}


	/**
	 * @return DB_Query_Select_AllColumns|DB_Query_Select_Column|DB_Query_Select_Count|DB_Query_Select_Expression|DB_Query_Select_SubQuery
	 */
	public function current() {
		return current($this->expressions);
	}


	public function next() {
		next($this->expressions);
	}

	/**
	 * @return string|null
	 */
	public function key() {
		return key($this->expressions);
	}

	/**
	 * @return bool
	 */
	public function valid() {
		return key($this->expressions) !== null;
	}


	public function rewind() {
		reset($this->expressions);
	}

	/**
	 * @return int
	 */
	public function count() {
		return $this->getExpressionsCount();
	}

	/**
	 * @return string
	 */
	function __toString(){
		if($this->isEmpty()){
			return "*";
		}

		$expressions = $this->getExpressions();
		$output = array();

		foreach($expressions as $expression){
			$output[] = (string)$expression;
		}

		return implode(",\n", $output);
	}
}