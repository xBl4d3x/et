<?php
namespace Et;
class DB_Query_Select extends Object implements \Iterator,\Countable, \ArrayAccess {

	/**
	 * @var DB_Query_Select_Column_All[]|DB_Query_Select_Column[]|DB_Query_Select_Function[]|DB_Query_Select_Expression[]|DB_Query_Select_SubQuery[]
	 */
	protected $statements = array();

	/**
	 * @var DB_Query
	 */
	protected $query;

	/**
	 * @see DB_Query_Select::setStatements()
	 *
	 * @param DB_Query $query
	 * @param string[]|DB_Expression[]|DB_Query[]|DB_Query_Column[] $statements [optional]
	 */
	function __construct(DB_Query $query, array $statements = array()){
		$this->query = $query;
		if($statements){
			$this->setStatements($statements);
		}
	}

	/**
	 * @see DB_Query_Select::setStatements()
	 *
	 * @param DB_Query $query
	 * @param string[]|DB_Expression[]|DB_Query[]|DB_Query_Column[] $statements [optional]
	 * @return static|DB_Query_Select
	 */
	public static function getInstance(DB_Query $query, array $statements = array()){
		return new static($query, $statements);
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
	 * @return int
	 */
	function getStatementsCount(){
		return count($this->statements);
	}


	/**
	 * @return static|DB_Query_Select
	 */
	function resetStatements(){
		$this->statements = array();
		return $this;
	}

	/**
	 * Array values (statements) may be:
	 * - instance of DB_Expression
	 * - instance of DB_Query for nested query
	 * - column_name | table_name.column_name
	 * - * | table_name.*
	 * - COUNT(*) | COUNT(column_name) | COUNT(table_name.column_name)
	 *
	 * @param string[]|DB_Expression[]|DB_Query[]|DB_Query_Column[] $statements
	 * @return static|DB_Query_Select
	 * @throws DB_Query_Exception
	 */
	function setStatements(array $statements){
		$this->statements = array();
		foreach($statements as $k => $statement){
			if(is_numeric($k)){
				$select_as = null;
			} else {
				$select_as = $k;
			}

			// expression
			if($statement instanceof DB_Expression){
				$this->addExpression($statement, null, $select_as);
				continue;
			}

			// sub query
			if($statement instanceof DB_Query){
				$this->addSubQuery($statement, $select_as);
				continue;
			}
			
			$statement = trim($statement);

			// column
			if(preg_match('~^\w+(?:\.\w+)?$~', $statement)){
				$this->addColumn($statement, null, $select_as);
				continue;
			}

			// all table columns
			if($statement == "*" || preg_match('~^\w+\.\*$~', $statement)){
				if(strpos($statement, ".") !== false){
					list($table_name) = explode(".", $statement);
					$this->addAllColumns($table_name);
				} else {
					$this->addAllColumns();
				}
				continue;
			}
						
			// COUNT
			if(preg_match('~COUNT\((\*|\w+(?:\.\w+)?)\)~is', $statement, $m)){
				list(, $statement) = $m;
				$this->addCount($statement, null, $select_as);
				continue;
			}

			// anything else
			throw new DB_Query_Exception(
				"Failed to determine expression type for " . get_class($this) . "::setExpressions() for expression '{$statement}'",
				DB_Query_Exception::CODE_INVALID_EXPRESSION
			);
		}
		
		return $this;
	}

	/**
	 * Add statement - statement may be:
	 * - instance of DB_Expression
	 * - instance of DB_Query for nested query
	 * - column_name | table_name.column_name
	 * - * | table_name.*
	 * - COUNT(*) | COUNT(column_name) | COUNT(table_name.column_name)
	 *
	 * @param string|DB_Expression|DB_Query|DB_Query_Column $statement
	 * @param null|string $select_as [optional]
	 * @throws DB_Query_Exception
	 * @return static|DB_Query_Select
	 */
	public function addStatement($statement, $select_as = null){

		if($select_as !== null){
			$select_as = (string)$select_as;
		}

		// expression
		if($statement instanceof DB_Expression){
			$this->addExpression($statement, null, $select_as);
			return $this;
		}

		// sub query
		if($statement instanceof DB_Query){
			$this->addSubQuery($statement, $select_as);
			return $this;
		}

		$statement = trim($statement);

		// column
		if(preg_match('~^\w+(?:\.\w+)?$~', $statement)){
			$this->addColumn($statement, null, $select_as);
			return $this;
		}

		// all table columns
		if($statement == "*" || preg_match('~^\w+\.\*$~', $statement)){
			if(strpos($statement, ".") !== false){
				list($table_name) = explode(".", $statement);
				$this->addAllColumns($table_name);
			} else {
				$this->addAllColumns();
			}
			return $this;
		}

		// COUNT
		if(preg_match('~COUNT\((\*|\w+(?:\.\w+)?)\)~is', $statement, $m)){
			list(, $statement) = $m;
			$this->addCount($statement, null, $select_as);
			return $this;
		}

		// anything else
		throw new DB_Query_Exception(
			"Failed to determine expression type for " . get_class($this) . "::setExpressions() for expression '{$statement}'",
			DB_Query_Exception::CODE_INVALID_EXPRESSION
		);

	}


	/**
	 * @return DB_Query_Select_Column_All[]|DB_Query_Select_Column[]|DB_Query_Select_Function[]|DB_Query_Select_Expression[]|DB_Query_Select_SubQuery[]
	 */
	function getStatements(){
		return $this->statements;
	}

	/**
	 * @param string $column_name
	 * @param null|string $table_name [optional]
	 * @param null|string $select_as [optional]
	 * @return static|DB_Query_Select
	 */
	function addColumn($column_name, $table_name = null, $select_as = null){
		$column = new DB_Query_Select_Column($this->getQuery(), $column_name, $table_name, $select_as);
		$this->statements[] = $column;
		return $this;
	}


	/**
	 * @param string $function_name
	 * @param array|DB_Table_Column[]|DB_Expression[]|DB_Query[] $function_arguments [optional]
	 * @param null|string $select_as
	 * @return static|DB_Query_Select
	 */
	function addFunction($function_name, array $function_arguments = array(), $select_as = null){
		$this->statements[] = new DB_Query_Select_Function($this->getQuery(), $function_name, $function_arguments, $select_as);
		return $this;
	}

	/**
	 * @param array $columns
	 * @param null|string $table_name [optional]
	 * @return static|DB_Query_Select
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
	 * @return static|DB_Query_Select
	 */
	function addCount($column_name = DB_Query::ALL_COLUMNS, $table_name = null, $select_as = null){
		if($column_name != DB_Query::ALL_COLUMNS){
			$column_name = $this->query->getColumn($column_name, $table_name);
		}
		$this->statements[] = new DB_Query_Select_Function_COUNT($this->getQuery(), $column_name, $select_as);
		return $this;
	}

	/**
	 * @param null|string $table_name [optional]
	 * @return static|DB_Query_Select
	 */
	function addAllColumns($table_name = null){
		$statement = new DB_Query_Select_Column_All($this->getQuery(), $table_name);
		$this->statements[] = $statement;
		return $this;
	}

	/**
	 * @param string|DB_Expression $expression
	 * @param null|string $table_name [optional]
	 * @param null|string $select_as [optional]
	 * @return static|DB_Query_Select
	 */
	function addExpression($expression, $table_name = null, $select_as = null){
		$statement = new DB_Query_Select_Expression($this->getQuery(), $expression, $select_as, $table_name);
		$this->statements[] = $statement;
		return $this;
	}

	/**
	 * @param DB_Query $sub_query
	 * @param null|string $select_as [optional]
	 * @return static|DB_Query_Select
	 */
	function addSubQuery(DB_Query $sub_query, $select_as = null){
		$column = new DB_Query_Select_SubQuery($this->getQuery(), $sub_query, $select_as);
		$this->statements[] = $column;
		return $this;
	}


	/**
	 * @return DB_Query_Select_Column_All|DB_Query_Select_Column|DB_Query_Select_Expression|DB_Query_Select_SubQuery
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
	 * @param mixed $statement_exists
	 * @return bool
	 */
	public function offsetExists($statement_exists) {
		return isset($this->statements[$statement_exists]);
	}

	public function offsetGet($offset) {
		throw new DB_Query_Exception(
			static::class . __METHOD__ . "() is not permitted",
			DB_Query_Exception::CODE_NOT_PERMITTED
		);
	}

	/**
	 * @see DB_Query_Select::addStatement()
	 * @param string|null $select_as
	 * @param string|DB_Expression|DB_Query|DB_Query_Column $statement
	 */
	public function offsetSet($select_as, $statement) {
		$this->addStatement($statement, $select_as);
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