<?php
namespace Et;
class DB_Query extends Object implements \ArrayAccess {

	const MAIN_TABLE_ALIAS = "_main_";
	const SELECTED_COLUMN_ALIAS = "_selected_";

	const ALL_COLUMNS = "*";

	const ORDER_ASC = "ASC";
	const ORDER_DESC = "DESC";

	const JOIN_INNER = "INNER";
	const JOIN_OUTER = "OUTER";
	const JOIN_LEFT = "LEFT";
	const JOIN_LEFT_OUTER = "LEFT OUTER";
	const JOIN_RIGHT = "RIGHT";
	const JOIN_RIGHT_OUTER = "RIGHT OUTER";

	const OP_AND = "AND";
	const OP_OR = "OR";
	const OP_AND_NOT = "AND NOT";
	const OP_OR_NOT = "OR NOT";

	const CMP_EQUALS = "=";
	const CMP_NOT_EQUALS = "<>";
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
	 * @var array
	 */
	protected static $supported_join_types = array(
		self::JOIN_INNER => self::JOIN_INNER,
		self::JOIN_OUTER => self::JOIN_OUTER,
		self::JOIN_LEFT => self::JOIN_LEFT,
		self::JOIN_LEFT_OUTER => self::JOIN_LEFT_OUTER,
		self::JOIN_RIGHT => self::JOIN_RIGHT,
		self::JOIN_RIGHT_OUTER => self::JOIN_RIGHT_OUTER
	);

	/**
	 * @var array
	 */
	protected static $supported_logical_operators = array(
		DB_Query::OP_AND => DB_Query::OP_AND,
		DB_Query::OP_OR => DB_Query::OP_OR,
		DB_Query::OP_AND_NOT => DB_Query::OP_AND_NOT,
		DB_Query::OP_OR_NOT => DB_Query::OP_OR_NOT,
	);

	/**
	 * @var array
	 */
	protected static $supported_compare_operators = array(
		DB_Query::CMP_EQUALS => DB_Query::CMP_EQUALS,
		DB_Query::CMP_NOT_EQUALS => DB_Query::CMP_NOT_EQUALS,
		DB_Query::CMP_IS_GREATER => DB_Query::CMP_IS_GREATER,
		DB_Query::CMP_IS_GREATER_OR_EQUAL => DB_Query::CMP_IS_GREATER_OR_EQUAL,
		DB_Query::CMP_IS_LOWER => DB_Query::CMP_IS_LOWER,
		DB_Query::CMP_IS_LOWER_OR_EQUAL => DB_Query::CMP_IS_LOWER_OR_EQUAL,
		DB_Query::CMP_IS_NULL => DB_Query::CMP_IS_NULL,
		DB_Query::CMP_IS_NOT_NULL => DB_Query::CMP_IS_NOT_NULL,
		DB_Query::CMP_LIKE => DB_Query::CMP_LIKE,
		DB_Query::CMP_NOT_LIKE => DB_Query::CMP_NOT_LIKE,
		DB_Query::CMP_IN => DB_Query::CMP_IN,
		DB_Query::CMP_NOT_IN => DB_Query::CMP_NOT_IN,
	);


	/**
	 * @var string
	 */
	protected $main_table_name;

	/**
	 * @var array
	 */
	protected $tables_in_query = array();

	/**
	 * @var DB_Query_Select|DB_Query_Select_AllColumns[]|DB_Query_Select_Column[]|DB_Query_Select_Function[]|DB_Query_Select_Expression[]|DB_Query_Select_Query[]
	 */
	protected $select;

	/**
	 * @var DB_Query_Relations
	 */
	protected $relations;

	/**
	 * @var DB_Query_Compare
	 */
	protected $where;

	/**
	 * @var DB_Query_Compare
	 */
	protected $having;

	/**
	 * @var DB_Query_GroupBy
	 */
	protected $group_by;

	/**
	 * @var DB_Query_OrderBy
	 */
	protected $order_by;

	/**
	 * @var int
	 */
	protected $limit = 0;

	/**
	 * @var int
	 */
	protected $offset = 0;
	
	

	/**
	 * @param string $main_table_name
	 * @throws DB_Query_Exception
	 */
	function __construct($main_table_name){
		$this->main_table_name = (string)$main_table_name;
		if(!$this->main_table_name || $this->isTableAlias($this->main_table_name)){
			throw new DB_Query_Exception(
				"Invalid main table name in query",
				DB_Query_Exception::CODE_INVALID_TABLE_NAME
			);
		}
		$this->checkTableName($this->main_table_name);
		$this->tables_in_query[$this->main_table_name] = $this->main_table_name;
	}

	/**
	 * @param string $table_name
	 * @return bool
	 */
	protected static function isTableAlias($table_name){
		return in_array(
			$table_name, 
			array(
				static::MAIN_TABLE_ALIAS, 
				static::SELECTED_COLUMN_ALIAS
			)
		);
	}


	/**
	 * @param string $main_table_name
	 * @param array $select_statements [optional]
	 * @param array $where_statements [optional]
	 * @param array $order_by [optional]
	 * @param int $limit [optional]
	 * @param int $offset [optional]
	 * @return DB_Query|static
	 */
	public static function getInstance($main_table_name,
	                                   array $select_statements = array(),
	                                   array $where_statements = array(),
	                                   array $order_by = array(),
										$limit = 0,
										$offset = 0

	) {
		/** @var $query DB_Query */
		$query = new static($main_table_name);
		if($select_statements){
			$query->select($select_statements);
		}
		if($where_statements){
			$query->where($where_statements);
		}
		if($order_by){
			$query->orderBy($order_by);
		}
		$query->limit($limit, $offset);
		return $query;
	}

	/**
	 * @param string $table_name
	 * @return static|DB_Query
	 */
	function addTableToQuery($table_name){
		$table_name = (string)$table_name;
		if(isset($this->tables_in_query[$table_name]) || $this->isTableAlias($table_name)){
			return $this;
		}
		$this->checkTableName($table_name);
		$this->tables_in_query[$table_name] = $table_name;
		return $this;
	}

	/**
	 * @param string $column_name
	 * @return string
	 */
	public function getMainColumnName($column_name){
		return "{$this->getMainTableName()}.{$column_name}";
	}

	/**
	 * @param array $column_names
	 * @return array
	 */
	public function getMainColumnNames(array $column_names){
		foreach($column_names as &$v){
			$v = $this->getMainColumnName($v);
		}
		return $column_names;
	}

	/**
	 * @param string $table_name
	 * @return string
	 */
	public function resolveTableName($table_name){
		if(!$table_name){
			return $this->getMainTableName();
		}
		
		if($table_name == self::SELECTED_COLUMN_ALIAS){
			return null;
		}

		$table_name = (string)$table_name;

		if($table_name == static::MAIN_TABLE_ALIAS){
			return $this->getMainTableName();
		}
		
		if(isset($this->tables_in_query[$table_name])){
			return $table_name;
		}

		$this->checkTableName($table_name);
		return $table_name;
	}

	/**
	 * @return string
	 */
	public function getMainTableName() {
		return $this->main_table_name;
	}

	/**
	 * @return array
	 */
	public function getTablesInQuery() {
		return $this->tables_in_query;
	}

	/**
	 * @return int
	 */
	public function getTablesInQueryCount(){
		return count($this->tables_in_query);
	}
	
	// WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE
	// WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE
	// WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE
	// WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE
	// WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE

	/**
	 * @return DB_Query_Compare
	 */
	function getWhere(){
		if(!$this->where){
			$this->where = new DB_Query_Compare($this);
		}
		return $this->where;
	}

	/**
	 * @param array $statements
	 * @return static|DB_Query
	 */
	function where(array $statements){
		$this->getWhere()->setStatements($statements);
		return $this;
	}

	/**
	 * @param string $column
	 * @param mixed $value
	 *
	 * @return static|DB_Query
	 */
	function whereColumnEquals($column, $value){

		$this->getWhere()
			->resetStatements()
			->addColumnEquals($column, $value);

		return $this;
	}
	
	/**
	 * @param array $columns
	 *
	 * @return static|DB_Query
	 */
	function whereColumnsEqual(array $columns){
		$this->getWhere()
			->resetStatements()
			->addColumnsEqual($columns);

		return $this;
	}

	// WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE
	// WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE
	// WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE
	// WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE
	// WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE WHERE



	// HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING
	// HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING
	// HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING
	// HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING
	// HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING

	/**
	 * @return DB_Query_Compare
	 */
	function getHaving(){
		if(!$this->having){
			$this->having = new DB_Query_Compare($this);
		}
		return $this->having;
	}

	/**
	 * @param array $expressions
	 * @return static|DB_Query
	 */
	function having(array $expressions){
		$this->getHaving()->setStatements($expressions, false);
		return $this;
	}

	/**
	 * @param string $column
	 * @param mixed $value
	 *
	 * @return static|DB_Query
	 */
	function havingColumnEquals($column, $value){

		$this->getHaving()
			->resetStatements()
			->addColumnEquals($column, $value);

		return $this;
	}

	/**
	 * @param array $columns
	 *
	 * @return static|DB_Query
	 */
	function havingColumnsEqual(array $columns){
		$this->getHaving()
			->resetStatements()
			->addColumnsEqual($columns);

		return $this;
	}

	// HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING
	// HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING
	// HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING
	// HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING
	// HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING HAVING
	
	
	


	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT
	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT
	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT
	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT
	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT
	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT


	/**
	 * @return DB_Query_Select|DB_Query_Select_AllColumns[]|DB_Query_Select_Column[]|DB_Query_Select_Function[]|DB_Query_Select_Expression[]|DB_Query_Select_Query[]
	 */
	function getSelect(){
		if(!$this->select){
			$this->select = $this->_getEmptySelect();
		}	
		return $this->select;
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
	 * @return static|DB_Query
	 */
	function select(array $statements = array()){
		$this->getSelect()->setStatements($statements, false);
		return $this;
	}

	/**
	 * @return DB_Query_Select
	 */
	protected function _getEmptySelect(){
		return new DB_Query_Select($this);
	}

	/**
	 * SELECT column_name | table_name.column_name AS select_as
	 *
	 * @param string|DB_Query_Column $column_name
	 * @param null|string $select_as [optional]
	 * @return static|DB_Query
	 */
	function selectColumn($column_name, $select_as = null){
		$this->select = $this->_getEmptySelect()->addColumn($column_name, $select_as);
		return $this;
	}

	/**
	 * Array like:
	 * array(column1, table1.column2 ... )
	 * -> SELECT column1, table1.column2, ...
	 * OR
	 * array(select_as1 => column1, select_as2 => table1.column2, ...)
	 * -> SELECT column1 AS select_as1, table1.column2 AS select_as2
	 *
	 * @param array $columns
	 * @return static|DB_Query
	 */
	function selectColumns(array $columns){
		$this->select = $this->_getEmptySelect()->addColumns($columns);
		return $this;
	}

	/**
	 * SELECT COUNT(*) | COUNT(column_name) | COUNT(table_name.column_name) AS select_as
	 *
	 * @param string $column_name [optional] Use '*' for "all columns"
	 * @param null|string $select_as [optional]
	 * @return static|DB_Query
	 */
	function selectCount($column_name = self::ALL_COLUMNS, $select_as = null){
		$this->select = $this->_getEmptySelect()->addCount($column_name, $select_as);
		return $this;
	}

	/**
	 * SELECT function_name(arg1, arg2, ... )
	 *
	 * @param string $function_name
	 * @param array|DB_Table_Column[]|DB_Expression[]|DB_Query[] $function_arguments [optional]
	 * @param null|string $select_as
	 * @return static|DB_Query
	 */
	function selectFunction($function_name, array $function_arguments = array(), $select_as = null){
		$this->select = $this->_getEmptySelect()->addFunction($function_name, $function_arguments, $select_as);
		return $this;
	}


	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT
	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT
	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT
	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT
	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT
	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT




	// ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY
	// ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY
	// ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY
	// ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY
	// ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY


	/**
	 * @return DB_Query_OrderBy
	 */
	function getOrderBy(){
		if(!$this->order_by){
			$this->order_by = new DB_Query_OrderBy($this);
		}
		return $this->order_by;
	}

	/**
	 * Array like:
	 * array(column_name => ASC | DESC)
	 * or
	 * array(column_index => ASC | DESC)
	 *
	 * -> ORDER BY column1 ASC, column2 DESC ...
	 *
	 * @param array $column_names_or_indexes [optional]
	 * @return DB_Query|static
	 */
	function orderBy(array $column_names_or_indexes = array()){
		$this->getOrderBy()->setOrderByColumns($column_names_or_indexes);
		return $this;
	}



	/**
	 * @param string $column_name_or_index
	 * @param null|string $order_how [optional]
	 * @return DB_Query|static
	 */
	function orderByColumn($column_name_or_index, $order_how = self::ORDER_ASC){
		$order_by = $this->getOrderBy()->resetOrderBy();
		if(is_numeric($column_name_or_index)){
			$order_by->addOrderByColumnNumber($column_name_or_index, $order_how);
		} else {
			$order_by->addOrderByColumn($column_name_or_index, $order_how);
		}
		return $this;
	}


	// ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY
	// ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY
	// ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY
	// ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY
	// ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY ORDER BY








	/**
	 * @return static|DB_Query_Relations
	 */
	function getRelations(){
		if(!$this->relations){
			$this->relations = new DB_Query_Relations($this);
		}
		return $this->relations;
	}

	/**
	 * @param string $related_table_name
	 * @param array $compare_statements [optional]
	 * @param string $join_type [optional]
	 * @return \Et\DB_Query_Relations_Relation
	 */
	function join($related_table_name, array $compare_statements = array(), $join_type = null){
		return $this->getRelations()->addRelation($related_table_name, $compare_statements, $join_type);
	}

	/**
	 * @param string $related_table_name
	 * @param array $related_columns array like (column_name => related_table_column_name)
	 * @param null|string $join_type [optional]
	 * @return \Et\DB_Query_Relations_Relation
	 */
	function joinOnColumns($related_table_name, array $related_columns, $join_type = null){
		return $this->getRelations()->addSimpleRelation($related_table_name, $related_columns, $join_type);
	}



	// GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY
	// GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY
	// GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY
	// GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY
	// GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY


	/**
	 * @return \Et\DB_Query_GroupBy
	 */
	function getGroupBy(){
		if(!$this->group_by){
			$this->group_by = new DB_Query_GroupBy($this);
		}
		return $this->group_by;
	}

	/**
	 * @param array $group_by_columns
	 * @return DB_Query|static
	 */
	function groupBy(array $group_by_columns = array()){
		$this->getGroupBy()->resetColumns()->setGroupByColumns($group_by_columns);
		return $this;
	}

	/**
	 * @param string $column_name
	 * @return static|DB_Query
	 */
	function groupByColumn($column_name){
		$this->getGroupBy()->resetColumns()->addGroupByColumn($column_name);
		return $this;
	}


	// GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY
	// GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY
	// GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY
	// GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY
	// GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY GROUP BY







	/**
	 * @param int $limit
	 * @param int $offset
	 * @return static|DB_Query
	 */
	function limit($limit = 0, $offset = 0){
		$this->setLimit($limit);
		$this->setOffset($offset);
		return $this;
	}

	/**
	 * @param int $page
	 * @param int $items_per_page
	 * @return DB_Query|static
	 */
	function setPage($page, $items_per_page){
		$page = max(1, (int)$page);
		$items_per_page = max(1, (int)$items_per_page);
		return $this->limit($items_per_page, ($page - 1) * $items_per_page);
	}

	/**
	 * @param int|null $limit
	 * @return static|DB_Query
	 */
	function setLimit($limit){
		$this->limit = max(0, (int)$limit);
		return $this;
	}

	/**
	 * @return int|null
	 */
	function getLimit(){
		return $this->limit;
	}

	/**
	 * @param int|null $offset
	 * @return static|DB_Query
	 */
	function setOffset($offset){
		$this->offset = max(0, (int)$offset);;
		return $this;
	}

	/**
	 * @return int
	 */
	function getOffset(){
		return $this->offset;
	}


	/**
	 * @param string $column_name
	 * @throws DB_Query_Exception
	 */
	public static function checkColumnName($column_name){
		if(!preg_match('~^\w+(?:\.\w+)?$~', (string)$column_name)){
			throw new DB_Query_Exception(
				"Invalid column name format",
				DB_Query_Exception::CODE_INVALID_COLUMN_NAME
			);
		}
	}

	/**
	 * @param string $table_name
	 * @throws DB_Query_Exception
	 */
	public static function checkTableName($table_name){
		if(!preg_match('~^\w+$~', (string)$table_name)){
			throw new DB_Query_Exception(
				"Invalid table name format",
				DB_Query_Exception::CODE_INVALID_TABLE_NAME
			);
		}
	}

	/**
	 * @param string $order_how
	 * @throws DB_Query_Exception
	 */
	public static function checkOrderHow($order_how){
		$order_how = strtoupper($order_how);
		if(!in_array($order_how, array(self::ORDER_ASC, self::ORDER_DESC))){
			throw new DB_Query_Exception(
				"Invalid sort type - must be ASC or DESC",
				DB_Query_Exception::CODE_INVALID_ORDER_BY_TYPE
			);
		}
	}


	/**
	 * @return array
	 */
	public static function getSupportedCompareOperators() {
		return static::$supported_compare_operators;
	}

	/**
	 * @return array
	 */
	public static function getSupportedLogicalOperators() {
		return static::$supported_logical_operators;
	}



	/**
	 * @param string $operator
	 * @throws DB_Query_Exception
	 */
	public static function checkCompareOperator($operator){
		$operator = (string)$operator;
		if(!isset(static::$supported_compare_operators[$operator])){
			throw new DB_Query_Exception(
				"Operator '{$operator}' is not supported. Supported operators: '" . implode("', '", static::$supported_compare_operators) . "'",
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
		if(!isset(static::$supported_logical_operators[$operator])){
			throw new DB_Query_Exception(
				"Operator '{$operator}' is not supported. Supported operators: '" . implode("', '", static::$supported_logical_operators) . "'",
				DB_Query_Exception::CODE_INVALID_OPERATOR
			);
		}
	}


	/**
	 * @param string $column_name
	 * @return array [column_name, table_name]
	 * @throws DB_Query_Exception
	 */
	public function resolveColumnAndTable($column_name){
		$column_name = (string)$column_name;
		$this->checkColumnName($column_name);
		if(strpos($column_name, ".") !== false){
			list($table_name, $column_name) = explode(".", $column_name, 2);
			if($table_name){
				$table_name = $this->resolveTableName($table_name);
			}
		} else {
			$table_name = $this->getMainTableName();
		}
		return array($column_name, $table_name);
	}

	/**
	 * @param string $join_type
	 * @throws DB_Query_Exception
	 */
	public static function checkJoinType($join_type){
		$join_type = strtoupper($join_type);
		if(!isset(static::$supported_join_types[$join_type])){
			throw new DB_Query_Exception(
				"Invalid join type - must be one of '" . implode("', '", static::$supported_join_types) . "'",
				DB_Query_Exception::CODE_INVALID_JOIN_TYPE
			);
		}
	}


	/**
	 * @param string $column_name
	 * @return DB_Query_Column
	 */
	function getColumn($column_name){
		return new DB_Query_Column($this, $column_name);
	}

	/**
	 * @throws DB_Query_Exception
	 */
	function checkRelations(){
		$unresolved_tables = $this->tables_in_query;
		unset($unresolved_tables[$this->getMainTableName()]);
		if(!$unresolved_tables){
			return;
		}

		if(!$this->relations || $this->relations->isEmpty()){
			throw new DB_Query_Exception(
				"No relations defined, cannot resolve relations with following tables: " . implode(", ", $unresolved_tables),
				DB_Query_Exception::CODE_UNRESOLVED_RELATIONS
			);
		}

		/** @var $relation DB_Query_Relations_SimpleRelation */
		foreach($this->relations as $relation){
			if(isset($unresolved_tables[$relation->getRelatedTableName()])){
				unset($unresolved_tables[$relation->getRelatedTableName()]);
			}
		}

		if($unresolved_tables){
			throw new DB_Query_Exception(
				"Cannot resolve relations with following tables: " . implode(", ", $unresolved_tables),
				DB_Query_Exception::CODE_UNRESOLVED_RELATIONS
			);
		}

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
	 * Support for creating WHERE query using array access interface
	 *
	 * @see DB_Query_Compare::setStatements()
	 *
	 * @param string|int|null $column_function_null
	 * @param mixed|string|array $value
	 */
	public function offsetSet($column_function_null, $value) {
		$where = $this->getWhere();
		if($column_function_null === null){
			$where[] = $value;
		} else {
			$where[$column_function_null] = $value;
		}
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