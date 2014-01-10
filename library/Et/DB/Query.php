<?php
namespace Et;
class DB_Query extends Object implements \ArrayAccess {

	const MAIN_TABLE_ALIAS = "this";

	const ALL_COLUMNS = "*";

	const ORDER_ASC = "ASC";
	const ORDER_DESC = "DESC";

	const JOIN_TYPE_INNER = "INNER";
	const JOIN_TYPE_OUTER = "OUTER";
	const JOIN_TYPE_LEFT = "LEFT";
	const JOIN_TYPE_LEFT_OUTER = "LEFT OUTER";
	const JOIN_TYPE_RIGHT = "RIGHT";
	const JOIN_TYPE_RIGHT_OUTER = "RIGHT OUTER";

	/**
	 * @var string
	 */
	protected $main_table_name;

	/**
	 * Check if all relations between tables are defined (if FALSE, multiple tables without relation are possible to use in query)
	 *
	 * @var bool
	 */
	protected $check_relations_before_build = true;

	/**
	 * Remove main table name from query if no other table is present
	 *
	 * @var bool
	 */
	protected $allow_query_simplification = true;

	/**
	 * @var string
	 */
	protected $default_join_type = self::JOIN_TYPE_LEFT;

	/**
	 * @var array
	 */
	protected $tables_in_query = array();

	/**
	 * @var DB_Query_Select|DB_Query_Select_Column_All[]|DB_Query_Select_Column[]|DB_Query_Select_Function[]|DB_Query_Select_Expression[]|DB_Query_Select_SubQuery[]
	 */
	protected $select;

	/**
	 * @var DB_Query_Relations
	 */
	protected $relations;

	/**
	 * @var DB_Query_Where
	 */
	protected $where;

	/**
	 * @var DB_Query_Having
	 */
	protected $having;

	/**
	 * @var array
	 */
	protected $group_by = array();

	/**
	 * Column names or numbers
	 *
	 * @var array
	 */
	protected $order_by = array();

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
		if(!$this->main_table_name || $this->main_table_name == static::MAIN_TABLE_ALIAS){
			throw new DB_Query_Exception(
				"Invalid main table name in query",
				DB_Query_Exception::CODE_INVALID_TABLE_NAME
			);
		}
		$this->checkTableName($this->main_table_name);
		$this->tables_in_query[$this->main_table_name] = $this->main_table_name;
	}


	/**
	 * @param string $main_table_name
	 * @param array $select_expressions [optional]
	 * @param array $where_expressions [optional]
	 * @param array $order_by [optional]
	 * @param null|int $limit [optional]
	 * @param null|int $offset [optional]
	 * @return static|DB_Query
	 */
	public static function getInstance($main_table_name,
	                                   array $select_expressions = array(),
	                                   array $where_expressions = array(),
	                                   array $order_by = array(),
										$limit = null,
										$offset = null

	) {
		/** @var $query DB_Query */
		$query = new static($main_table_name);
		if($select_expressions){
			$query->select($select_expressions);
		}
		if($where_expressions){
			$query->where($where_expressions);
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
		if(isset($this->tables_in_query[$table_name]) || $table_name == self::MAIN_TABLE_ALIAS){
			return $this;
		}
		$this->checkTableName($table_name);
		$this->tables_in_query[$table_name] = $table_name;
		return $this;
	}

	/**
	 * @param string $table_name
	 * @return string
	 */
	public function resolveTableName($table_name){
		if(!$table_name){
			return $this->getMainTableName();
		}

		$table_name = (string)$table_name;

		if($table_name == static::MAIN_TABLE_ALIAS || $table_name == $this->getMainTableName()){
			return $this->getMainTableName();
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
	 * @return static|DB_Query_Where
	 */
	function getWhere(){
		if(!$this->where){
			$this->where = new DB_Query_Where($this);
		}
		return $this->where;
	}

	/**
	 * @param array $expressions
	 * @return static|DB_Query
	 */
	function where(array $expressions){
		$this->getWhere()->setStatements($expressions, false);
		return $this;
	}

	/**
	 * @param string $column
	 * @param mixed $value
	 * @param null|string $table_name [optional]
	 *
	 * @return static|DB_Query
	 */
	function whereColumnEquals($column, $value, $table_name = null){
		$this->getWhere()->removeExpressions()->addColumnEquals($column, $value, $table_name);
		return $this;
	}

	/**
	 * @param array $columns
	 * @param null|string $table_name [optional]
	 *
	 * @return static|DB_Query
	 */
	function whereColumnsEqual(array $columns, $table_name = null){
		$this->getWhere()->removeExpressions()->addColumnsEqual($columns, $table_name);
		return $this;
	}

	/**
	 * @param DB_Table_Key $key
	 * @param array $other_columns_values [optional]
	 *
	 * @return static|DB_Query
	 * @throws DB_Query_Exception
	 */
	function whereKeyEquals(DB_Table_Key $key, array $other_columns_values = array()){
		if(!$key->hasAllColumnValues()){
			throw new DB_Query_Exception(
				"Cannot compare key with no or not all values defined",
				DB_Query_Exception::CODE_INVALID_EXPRESSION
			);
		}
		
		$columns_values = $key->getColumnValues();
		foreach($other_columns_values as $k => $v){
			$columns_values[$k] = $v;
		}
		return $this->whereColumnsEqual($columns_values, $key->getTableName());
	}

	/**
	 * @return static|DB_Query_Having
	 */
	function getHaving(){
		if(!$this->having){
			$this->having = new DB_Query_Having($this);
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


	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT
	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT
	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT
	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT
	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT
	// SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT SELECT


	/**
	 * @return DB_Query_Select|DB_Query_Select_Column_All[]|DB_Query_Select_Column[]|DB_Query_Select_Function[]|DB_Query_Select_Expression[]|DB_Query_Select_SubQuery[]
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
	 * @param null|string $table_name [optional]
	 * @param null|string $select_as [optional]
	 * @return static|DB_Query
	 */
	function selectColumn($column_name, $table_name = null, $select_as = null){
		$this->select = $this->_getEmptySelect()->addColumn($column_name, $table_name, $select_as);
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
	 * @param null|string $table_name [optional]
	 * @return static|DB_Query
	 */
	function selectColumns(array $columns, $table_name = null){
		$this->select = $this->_getEmptySelect()->addColumns($columns, $table_name);
		return $this;
	}

	/**
	 * SELECT COUNT(*) | COUNT(column_name) | COUNT(table_name.column_name) AS select_as
	 *
	 * @param string $column_name [optional] Use '*' for "all columns"
	 * @param null|string $table_name [optional]
	 * @param null|string $select_as [optional]
	 * @return static|DB_Query
	 */
	function selectCount($column_name = self::ALL_COLUMNS, $table_name = null, $select_as = null){
		$this->select = $this->_getEmptySelect()->addCount($column_name, $table_name, $select_as);
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

	/**
	 * SELECT * | table_name.* ....
	 *
	 * @param null|string $table_name [optional]
	 * @return static|DB_Query
	 */
	function selectAllColumns($table_name = null){
		$this->select = $this->_getEmptySelect()->addAllColumns($table_name);
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
	 * Array like:
	 * array(column_name => ASC | DESC)
	 * or
	 * array(column_index => ASC | DESC)
	 *
	 * @return array
	 */
	function getOrderBy(){
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
	 * @return static|DB_Query
	 */
	function orderBy(array $column_names_or_indexes = array()){

		$this->order_by = array();
		foreach($column_names_or_indexes as $column_name_or_number => $order_how){

			$order_how = strtoupper($order_how) == self::ORDER_DESC
				? self::ORDER_DESC
				: self::ORDER_ASC;

			if(is_int($column_name_or_number) || preg_match('~^\w+$~', $column_name_or_number)){
				$this->order_by[$column_name_or_number] = $order_how;
				return $this;
			}

			$column = $this->getColumn($column_name_or_number);
			$this->addTableToQuery($column->getTableName());
			$this->order_by[(string)$column] = $order_how;
		}

		return $this;
	}

	/**
	 * @param string $column_name_or_index
	 * @param null|string $order_how [optional]
	 * @param null|string $table_name [optional]
	 * @return static|DB_Query
	 */
	function orderByColumn($column_name_or_index, $order_how = null, $table_name = null){
		$order_how = strtoupper($order_how) == self::ORDER_DESC
					? self::ORDER_DESC
					: self::ORDER_ASC;

		if(is_int($column_name_or_index) || preg_match('~^\w+$~', $column_name_or_index)){
			$this->order_by = array($column_name_or_index => $order_how);
			return $this;
		}

		$column = $this->getColumn($column_name_or_index, $table_name);
		$this->addTableToQuery($column->getTableName());
		$this->order_by = array((string)$column => $order_how);

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
	 * @param array $tables_relations [optional]
	 * @return static|DB_Query
	 */
	function relations(array $tables_relations = array()){
		$this->getRelations()->setRelations($tables_relations, false);
		return $this;
	}

	/**
	 * @return array
	 */
	function getGroupBy(){
		return $this->group_by;
	}

	/**
	 * @param array $group_by_columns
	 * @return static|DB_Query
	 */
	function groupBy(array $group_by_columns = array()){
		$this->group_by = array();
		foreach($group_by_columns as $column_name){
			$column = $this->getColumn($column_name);
			$this->addTableToQuery($column->getTableName());
			$this->group_by[] = (string)$column;
		}
		return $this;
	}

	/**
	 * @param string $column_name
	 * @param null|string $table_name [optional]
	 * @return static|DB_Query
	 */
	function groupByColumn($column_name, $table_name = null){
		$column = $this->getColumn($column_name, $table_name);
		$this->addTableToQuery($column->getTableName());
		$this->group_by = array((string)$column);
		return $this;
	}

	/**
	 * @param string $related_table_name
	 * @param array $join_on_columns [related_column_name => other_column_name]
	 * @param null|string $join_type [optional] NULL = by default query join type
	 * @return static|DB_Query
	 */
	function addSimpleRelation($related_table_name, array $join_on_columns, $join_type = null){
		$this->getRelations()->addSimpleRelation($related_table_name, $join_on_columns, $join_type);
		return $this;
	}

	/**
	 * @param string $related_table_name
	 * @param array $join_expressions [related_column_name => other_column_name]
	 * @param null|string $join_type [optional] NULL = by default query join type
	 * @return static|DB_Query
	 */
	function addComplexRelation($related_table_name, array $join_expressions, $join_type = null){
		$this->getRelations()->addComplexRelation($related_table_name, $join_expressions, $join_type);
		return $this;
	}
	
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
	 * @param string $column_name
	 * @param null|string $table_name [optional]
	 * @return array [column_name, table_name]
	 * @throws DB_Query_Exception
	 */
	public function resolveColumnAndTable($column_name, $table_name = null){
		$column_name = (string)$column_name;
		$this->checkColumnName($column_name);
		if(strpos($column_name, ".") !== false){
			list($table_name, $column_name) = explode(".", $column_name, 2);
		}

		$table_name = $this->resolveTableName($table_name);
		return array($column_name, $table_name);
	}

	/**
	 * @param string $join_type
	 * @throws DB_Query_Exception
	 */
	public static function checkJoinType($join_type){
		$join_type = strtoupper($join_type);
		$allowed_joins = array(
			self::JOIN_TYPE_INNER,
			self::JOIN_TYPE_OUTER,
			self::JOIN_TYPE_LEFT,
			self::JOIN_TYPE_LEFT_OUTER,
			self::JOIN_TYPE_RIGHT,
			self::JOIN_TYPE_RIGHT_OUTER
		);
		
		if(!in_array($join_type, $allowed_joins)){
			throw new DB_Query_Exception(
				"Invalid join type - must be one of '" . implode("', '", $allowed_joins) . "'",
				DB_Query_Exception::CODE_INVALID_JOIN_TYPE
			);
		}
	}

	/**
	 * @param string $default_join_type
	 * @return static|DB_Query
	 */
	public function setDefaultJoinType($default_join_type) {
		$this->checkJoinType($default_join_type);
		$this->default_join_type = $default_join_type;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDefaultJoinType() {
		return $this->default_join_type;
	}

	/**
	 * @param string $column_name
	 * @param null|string $table_name [optional]
	 * @return static|DB_Query_Column
	 */
	function getColumn($column_name, $table_name = null){
		return new DB_Query_Column($this, $column_name, $table_name);
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
	 * Check if all tables have relations before trying to build query
	 *
	 * @param boolean $check_relations_before_build
	 */
	public function setCheckRelationsBeforeBuild($check_relations_before_build) {
		$this->check_relations_before_build = (bool)$check_relations_before_build;
	}

	/**
	 * @return boolean
	 */
	public function getCheckRelationsBeforeBuild() {
		return $this->check_relations_before_build;
	}

	/**
	 * Allow to remove main table name from built query when only 1 is present in query?
	 *
	 * @param boolean $allow_build_simplification
	 */
	public function setAllowQuerySimplification($allow_build_simplification) {
		$this->allow_query_simplification = (bool)$allow_build_simplification;
	}

	/**
	 * @return boolean
	 */
	public function getAllowQuerySimplification() {
		return $this->allow_query_simplification;
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