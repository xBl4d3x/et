<?php
namespace Et;
et_require_class('Object');
class DB_Query_Where extends Object implements \Countable,\Iterator {
	
	const OP_AND = "AND";
	const OP_OR = "OR";
	const OP_AND_NOT = "AND NOT";
	const OP_OR_NOT = "OR NOT";
	
	const CMP_EQUALS = "=";
	const CMP_NOT_EQUALS = "!=";
	const CMP_IS_GREATER = ">";
	const CMP_IS_GREATER_OR_EQUAL = ">=";
	const CMP_IS_LOWER = "<";
	const CMP_IS_LOWER_OR_EQUAL = "<=";
	const CMP_IS_NULL = "IS NULL";
	const CMP_IS_NOT_NULL = "IS NOT NULL";
	const CMP_LIKE = "LIKE";
	const CMP_NOT_LIKE = "NOT LIKE";
	const CMP_IN = "IN";
	const CMP_NOT_IN = "NOT IN";

	/**
	 * @var DB_Query_Where_ColumnCompare[]|DB_Query_Where_ExpressionCompare[]|DB_Query_Where[]|string[]
	 */
	protected $expressions = array();

	/**
	 * @var mixed
	 */
	protected $last_expression = null;

	/**
	 * @var array
	 */
	protected static $allowed_logical_operators = array(
		self::OP_AND,
		self::OP_OR,
		self::OP_AND_NOT,
		self::OP_OR_NOT,
	);

	/**
	 * @var array
	 */
	protected static $allowed_compare_operators = array(
		self::CMP_EQUALS,
		self::CMP_NOT_EQUALS,
		self::CMP_IS_GREATER,
		self::CMP_IS_GREATER_OR_EQUAL,
		self::CMP_IS_LOWER,
		self::CMP_IS_LOWER_OR_EQUAL,
		self::CMP_IS_NULL,
		self::CMP_IS_NOT_NULL,
		self::CMP_LIKE,
		self::CMP_NOT_LIKE,
		self::CMP_IN,
		self::CMP_NOT_IN,
	);

	/**
	 * @var DB_Query
	 */
	protected $query;


	/**
	 * @param string $operator
	 * @throws DB_Query_Exception
	 */
	public static function checkCompareOperator($operator){
		$operator = (string)$operator;
		if(!in_array($operator, static::$allowed_compare_operators)){
			throw new DB_Query_Exception(
				"Operator '{$operator}' is not supported. Supported operators: '" . implode("', '", static::$allowed_compare_operators) . "'",
				DB_Query_Exception::CODE_INVALID_OPERATOR
			);
		}
	}

	/**
	 * @param string $operator
	 * @throws DB_Query_Exception
	 */
	public static function checkLogicalOperator($operator){
		$operator = (string)$operator;
		if(!in_array($operator, static::$allowed_logical_operators)){
			throw new DB_Query_Exception(
				"Operator '{$operator}' is not supported. Supported operators: '" . implode("', '", static::$allowed_logical_operators) . "'",
				DB_Query_Exception::CODE_INVALID_OPERATOR
			);
		}
	}

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
	 * @param array $expressions
	 * @param bool $merge [optional]
	 * @throws DB_Query_Exception
	 */
	function setExpressions(array $expressions, $merge = true){
		if(!$merge){
			$this->expressions = array();
		}
		
		foreach($expressions as $k => $v){
			if(!is_array($v)){

				if(is_numeric($k)){
					if(is_scalar($v)){
						$this->addOperator($v);
					}
					continue;
				}

				$this->addColumnCompare($k, self::CMP_EQUALS, $v);
				continue;
			}
			
			if(!isset($v[0])){
				throw new DB_Query_Exception(
					"Invalid expression with key '{$k}' - may not be empty array",
					DB_Query_Exception::CODE_INVALID_EXPRESSION
				);
			}

			if(is_array($v[0])){
				foreach($v as $vv){
					$this->addNestedExpressions($vv);	
				}
				continue;
			}
			
			$column = array_shift($v);
			if(!$v){
				throw new DB_Query_Exception(
					"Invalid expression with key '{$k}' - missing compare operator",
					DB_Query_Exception::CODE_INVALID_EXPRESSION
				);
			}
			
			$operator = array_shift($v);
			$value = $v ? array_shift($v) : null;
			$this->addColumnCompare($column, $operator, $value);
		}
	}

	/**
	 * @return static
	 */
	function removeExpressions(){
		$this->expressions = array();
		return $this;
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
	 * @param string $column_name
	 * @param string $compare_operator
	 * @param null|mixed $value [optional]
	 * @param null|string $table_name [optional]
	 * @return static
	 */
	function addColumnCompare($column_name, $compare_operator, $value = null, $table_name = null){
		$column = new DB_Query_Where_ColumnCompare($this->getQuery(), $column_name, $compare_operator, $value, $table_name);
		if($this->last_expression && !is_scalar($this->last_expression)){
			$this->addAND();
		}
		$this->expressions[] = $column;
		$this->last_expression = $column;
		return $this;
	}

	/**
	 * @param string $column_name
	 * @param mixed $value
	 * @param null|string $table_name [optional]
	 * @return static
	 */
	function addColumnEquals($column_name, $value, $table_name = null){
		return $this->addColumnCompare($column_name, static::CMP_EQUALS, $value, $table_name);
	}

	/**
	 * @param array $columns_values
	 * @param null|string $table_name [optional]
	 * @return static
	 */
	function addColumnsEqual(array $columns_values, $table_name = null){
		foreach($columns_values as $column => $value){
			$this->addColumnEquals($column, $value, $table_name);
		}
		return $this;
	}

	/**
	 * @param string|DB_Expression $expression
	 * @param string $compare_operator [optional]
	 * @param null|mixed $value [optional]
	 * @param null|string $table_name [optional]
	 * @return static
	 */
	function addExpressionCompare($expression, $compare_operator = null, $value = null, $table_name = null){
		$column = new DB_Query_Where_ExpressionCompare($this->getQuery(), $expression, $compare_operator, $value, $table_name);
		if($this->last_expression && !is_scalar($this->last_expression)){
			$this->addAND();
		}
		$this->expressions[] = $column;
		$this->last_expression = $column;
		return $this;
	}

	/**
	 * @param array|DB_Query_Where $expressions
	 * @throws DB_Query_Exception
	 * @return static
	 */
	function addNestedExpressions($expressions){
		if(!$expressions instanceof DB_Query_Where){
			$where = new DB_Query_Where($this->getQuery(), $expressions);
		} else {
			if($expressions->getQuery() !== $this->query){
				throw new DB_Query_Exception(
					"Trying to pass expression from different query - not allowed",
					DB_Query_Exception::CODE_NOT_PERMITTED
				);
			}
			$where = $expressions;
		}
		if($this->last_expression && !is_scalar($this->last_expression)){
			$this->addAND();
		}
		$this->expressions[] = $where;
		$this->last_expression = $where;
		return $this;

	}

	/**
	 * @param string $operator
	 * @return static
	 */
	function addOperator($operator){
		$this->checkLogicalOperator($operator);
		$this->expressions[] = $operator;
		$this->last_expression = $operator;
		return $this;
	}

	/**
	 * @return static
	 */
	function addAND(){
		return $this->addOperator(self::OP_AND);
	}

	/**
	 * @return static
	 */
	function addOR(){
		return $this->addOperator(self::OP_OR);
	}

	/**
	 * @return static
	 */
	function addAND_NOT(){
		return $this->addOperator(self::OP_AND_NOT);
	}

	/**
	 * @return static
	 */
	function addOR_NOT(){
		return $this->addOperator(self::OP_OR_NOT);
	}

	/**
	 * @return static[]|DB_Query_Where_ColumnCompare[]|DB_Query_Where_ExpressionCompare[]
	 */
	public function getExpressions() {
		return $this->expressions;
	}

	/**
	 * @return int
	 */
	function getExpressionsCount(){
		return count($this->expressions);
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
		
		$expressions = $this->getExpressions();
		$padding = str_repeat("\t", $offset);
		$output = array();
		$last_idx = -1;

		foreach($expressions as $expression){
			if(is_scalar($expression)){
				if($last_idx == -1){
					continue;
				}
				$output[$last_idx] .= " {$expression}";
				continue;
			}
			
			if($expression instanceof DB_Query_Where){
				$output[] = "(";
				$output[] = $expression->toSQL($db, $offset + 1);
				$output[] = ")";
				$last_idx = count($output) - 1;
				continue;
			}
			
			$output[] = $expression->toSQL($db);
			$last_idx++;
		}

		return $padding . implode("\n{$padding}", $output);
	}

	/**
	 * @return string
	 */
	function __toString(){
		if($this->isEmpty()){
			return "";
		}

		$expressions = $this->getExpressions();
		$output = array();
		$last_idx = -1;

		foreach($expressions as $expression){
			if(is_scalar($expression)){
				if($last_idx == -1){
					continue;
				}
				$output[$last_idx] .= " {$expression}";
				continue;
			}

			if($expression instanceof DB_Query_Where){
				$output[] = "(";
				$output[] = str_replace("\n", "\n\t", (string)$expression);
				$output[] = ")";
				$last_idx = count($output) - 1;
				continue;
			}

			$output[] = (string)$expression;
			$last_idx++;
		}

		return implode("\n", $output);
	}


	/**
	 * @return DB_Query_Where_ColumnCompare|DB_Query_Where_ExpressionCompare|DB_Query_Where|string
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


}