<?php
namespace Et;
class DB_Query_Compare extends Object implements \Countable,\Iterator, \ArrayAccess {



	/**
	 * @var DB_Query_Compare_Column[]|DB_Query_Compare_Function[]|DB_Query_Compare_Expression[]|DB_Query_Compare[]|string[]
	 */
	protected $statements = array();



	/**
	 * @var DB_Query
	 */
	protected $query;

	/**
	 * @param DB_Query $query
	 * @param array $statements [optional]
	 */
	function __construct(DB_Query $query, array $statements = array()){
		$this->query = $query;
		if($statements){
			$this->setStatements($statements);
		}
	}

	/**
	 * @param string $column_name
	 * @return DB_Query_Column
	 */
	protected function getColumn($column_name){
		return $this->query->getColumn((string)$column_name);
	}


	/**
	 * If key is numeric or null:
	 *      if value is string -> add operator (AND, OR ... )
	 *      if value is array  -> add nested query ( AND ( ... statements ... ) OR )
	 *
	 * If key is string
	 *      if key has function format ( FUNCTION_NAME() ):
	 *          array like (compare_operator, compare_statement) is expected
	 *
	 *      if key has column name format ( column_name or table_name.column_name ):
	 *          if value is not array -> column_name = value (or column IS NULL)
	 *          else array like (compare_operator, compare_statement) is expected
	 *
	 * - AND operator is automatically inserted between statements if no other added before
	 * Example:
	 * array(
	 *      "name" => "John,
	 *      "surname" => array("IN", array("Doe", "Nodoe")),
	 *      array(
	 *          "email" => "john.doe@domain.tld",
	 *          "OR",
	 *          "email" => "john.nodoe@domain.tld",
	 *      )
	 * )
	 *
	 * @param array $statements
	 * @return static|DB_Query_Compare
	 * @throws DB_Query_Exception
	 */
	function setStatements(array $statements){
		$this->statements = array();

		foreach($statements as $k => $v){
			$this->_resolveAndAddStatement($k, $v);
		}

		return $this;
	}

	/**
	 * 
	 * @param int|string $k
	 * @param mixed|array $v
	 * @throws DB_Query_Exception
	 */
	protected function _resolveAndAddStatement(&$k, &$v){
		
		if(is_numeric($k) || $k === null){
			// operator
			if(is_string($v)){
				$this->addOperator($v);
				return;
			}
			
			if(!is_array($v)){
				throw new DB_Query_Exception(
					"Invalid statement with key '{$k}' - must be string (operator) or array (nested statements)",
					DB_Query_Exception::CODE_INVALID_EXPRESSION
				);
			}
			$this->addNestedStatements($v);
			return;
		}
		
		// function - LENGTH() => array(arguments, compare_operator, compared_statement)
		if(preg_match('~^(\w+)\(\)$~', $k, $m)){
			if(!is_array($v)){
				$this->addFunctionEquals($m[1], array(), $v);
				return;
			} else {
				$arguments = array_shift($v);
				$operator = array_shift($v);
				$value = $v ? array_shift($v) : null;
				$this->addFunctionCompare($m[1], $arguments, $operator, $value);
				return;
			}
		}
		
		// column
		$column = $this->getColumn($k);

		// column => value
		if(!is_array($v)){
			if($v === null){
				$this->addColumnCompare($k, DB_Query::CMP_IS_NULL);
			} else {
				$this->addColumnCompare($k, DB_Query::CMP_EQUALS, $v);
			}
			return;
		}
		

		if(!isset($v[0])){
			throw new DB_Query_Exception(
				"Invalid statement for column '{$k}' - missing compare operator",
				DB_Query_Exception::CODE_INVALID_EXPRESSION
			);
		}

		$operator = array_shift($v);
		$value = $v ? array_shift($v) : null;

		$this->addColumnCompare($column, $operator, $value);
	}

	/**
	 * @return static|DB_Query_Compare
	 */
	function resetStatements(){
		$this->statements = array();
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
		return !$this->statements;
	}

	/**
	 * @param string $column_name
	 * @param string $compare_operator
	 * @param null|mixed $value [optional]
	 * @return static|DB_Query_Compare
	 */
	function addColumnCompare($column_name, $compare_operator, $value = null){
		$column = new DB_Query_Compare_Column($this->getQuery(), $column_name, $compare_operator, $value);
		$this->_addANDIfNecessary();
		$this->statements[] = $column;
		return $this;
	}


	/**
	 * @param string $column_name
	 * @param string $compare_operator
	 * @param string $compare_to_column
	 * @return DB_Query_Compare|static
	 */
	function addColumnCompareColumn($column_name, $compare_operator, $compare_to_column){
		return $this->addColumnCompare(
			$column_name,
			$compare_operator,
			$compare_to_column
		);
	}

	/**
	 * @param string $column_name
	 * @param string $compare_to_column
	 * @return DB_Query_Compare|static
	 */
	function addColumnEqualsColumn($column_name, $compare_to_column){
		return $this->addColumnCompare(
			$column_name,
			DB_Query::CMP_EQUALS,
			$this->getColumn($compare_to_column)
		);
	}

	/**
	 * @param string $column_name
	 * @param mixed $value
	 * @param null|string $table_name [optional]
	 * @return static|DB_Query_Compare
	 */
	function addColumnEquals($column_name, $value, $table_name = null){
		return $this->addColumnCompare($column_name, DB_Query::CMP_EQUALS, $value, $table_name);
	}

	/**
	 * @param array $columns_values
	 * @param null|string $table_name [optional]
	 * @return static|DB_Query_Compare
	 */
	function addColumnsEqual(array $columns_values, $table_name = null){
		foreach($columns_values as $column => $value){
			$this->addColumnEquals($column, $value, $table_name);
		}
		return $this;
	}

	/**
	 * @param string $function_name
	 * @param array|DB_Table_Column[]|DB_Expression[]|DB_Query[] $function_arguments [optional]
	 * @param string $compare_operator
	 * @param mixed|null|array|\Iterator|DB_Query $value [optional]
	 * @return static|DB_Query_Compare
	 */
	function addFunctionCompare($function_name, array $function_arguments,  $compare_operator, $value = null){
		$function = new DB_Query_Compare_Function($this->getQuery(), $function_name, $function_arguments, $compare_operator, $value);
		$this->_addANDIfNecessary();
		$this->statements[] = $function;
		return $this;
	}

	/**
	 * @param string $function_name
	 * @param array|DB_Table_Column[]|DB_Expression[]|DB_Query[] $function_arguments [optional]
	 * @param mixed|null|array|\Iterator|DB_Query $value [optional]
	 * @return static|DB_Query_Compare
	 */
	function addFunctionEquals($function_name, array $function_arguments, $value = null){
		return $this->addFunctionCompare($function_name, $function_arguments, DB_Query::CMP_EQUALS, $value);
	}

	/**
	 * @param string|DB_Expression $expression
	 * @param string $compare_operator [optional]
	 * @param null|mixed $value [optional]
	 * @return static|DB_Query_Compare
	 */
	function addExpressionCompare($expression, $compare_operator = null, $value = null){
		$column = new DB_Query_Compare_Expression($this->getQuery(), $expression, $compare_operator, $value);
		$this->_addANDIfNecessary();
		$this->statements[] = $column;
		return $this;
	}

	/**
	 * @param array|DB_Query_Compare $statements
	 * @throws DB_Query_Exception
	 * @return static|DB_Query_Compare
	 */
	function addNestedStatements($statements){
		if(!$statements instanceof DB_Query_Compare){

			if(!is_array($statements)){
				throw new DB_Query_Exception(
					"Nested statements must be array or instance of Et\\DB_Query_Compare",
					DB_Query_Exception::CODE_NOT_PERMITTED
				);
			}

			if(!$statements){
				return $this;
			}
			$where = new static($this->getQuery(), $statements);

		} else {

			if($statements->getQuery() !== $this->query){
				throw new DB_Query_Exception(
					"Trying to pass expression from different query - not allowed",
					DB_Query_Exception::CODE_NOT_PERMITTED
				);
			}

			$where = $statements;
		}

		$this->_addANDIfNecessary();
		$this->statements[] = $where;
		return $this;

	}

	/**
	 * @param string $operator
	 * @throws DB_Query_Exception
	 * @return static|DB_Query_Compare
	 */
	function addOperator($operator){
		$this->query->checkLogicalOperator($operator);
		if(!$this->statements || is_string(end($this->statements))){
			throw new DB_Query_Exception(
				"Cannot add operator '{$operator}' into query when there's no compare statement before",
				DB_Query_Exception::CODE_NOT_PERMITTED
			);
		}
		$this->statements[] = $operator;
		return $this;
	}
	
	protected function _addANDIfNecessary(){
		if(!$this->statements || is_string(end($this->statements))){
			return;
		}
		$this->statements[] = DB_Query::OP_AND;
	}

	/**
	 * @return static|DB_Query_Compare
	 */
	function addAND(){
		return $this->addOperator(DB_Query::OP_AND);
	}

	/**
	 * @return static|DB_Query_Compare
	 */
	function addOR(){
		return $this->addOperator(DB_Query::OP_OR);
	}

	/**
	 * @return static|DB_Query_Compare
	 */
	function addAND_NOT(){
		return $this->addOperator(DB_Query::OP_AND_NOT);
	}

	/**
	 * @return static|DB_Query_Compare
	 */
	function addOR_NOT(){
		return $this->addOperator(DB_Query::OP_OR_NOT);
	}

	/**
	 * @return static[]|DB_Query_Compare[]|DB_Query_Compare_Column[]|DB_Query_Compare_Expression[]
	 */
	public function getStatements() {
		return $this->statements;
	}

	/**
	 * @return int
	 */
	function getStatementsCount(){
		return count($this->statements);
	}


	/**
	 * @return DB_Query_Compare_Column|DB_Query_Compare_Expression|DB_Query_Compare|static|string
	 */
	public function current() {
		return current($this->statements);
	}


	public function next() {
		next($this->statements);
	}

	/**
	 * @return string|null
	 */
	public function key() {
		return key($this->statements);
	}

	/**
	 * @return bool
	 */
	public function valid() {
		return key($this->statements) !== null;
	}


	public function rewind() {
		reset($this->statements);
	}

	/**
	 * @return int
	 */
	public function count() {
		return $this->getStatementsCount();
	}


	/**
	 * @param mixed $offset
	 * @return bool|void
	 * @throws DB_Query_Exception
	 */
	public function offsetExists($offset) {
		throw new DB_Query_Exception(
			static::class . __METHOD__ . "() is not permitted",
			DB_Query_Exception::CODE_NOT_PERMITTED
		);
	}

	/**
	 * @param mixed $offset
	 * @return mixed|void
	 * @throws DB_Query_Exception
	 */
	public function offsetGet($offset) {
		throw new DB_Query_Exception(
			static::class . __METHOD__ . "() is not permitted",
			DB_Query_Exception::CODE_NOT_PERMITTED
		);
	}

	/**
	 * Allow compare using array access interface
	 * 
	 * @param mixed $key
	 * @param mixed $value
	 */
	public function offsetSet($key, $value) {
		$this->_resolveAndAddStatement($key, $value);
	}

	/**
	 * @param mixed $offset
	 * @throws DB_Query_Exception
	 */
	public function offsetUnset($offset) {
		throw new DB_Query_Exception(
			static::class . __METHOD__ . "() is not permitted",
			DB_Query_Exception::CODE_NOT_PERMITTED
		);
	}
}