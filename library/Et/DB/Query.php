<?php
namespace Et;
class DB_Query extends Object {

	const MAIN_TABLE_ALIAS = "this";

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
	 * @var bool
	 */
	protected $check_relations_before_build = true;

	/**
	 * @var bool
	 */
	protected $allow_single_table_build_simplification = true;

	/**
	 * @var string
	 */
	protected $default_join_type = self::JOIN_TYPE_LEFT;

	/**
	 * @var array
	 */
	protected $tables_in_query = array();

	/**
	 * @var DB_Query_Select
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
		$this->getWhere()->setExpressions($expressions, false);
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
		$this->getHaving()->setExpressions($expressions, false);
		return $this;
	}

	/**
	 * @return static|DB_Query_Select
	 */
	function getSelect(){
		if(!$this->select){
			$this->select = new DB_Query_Select($this);
		}	
		return $this->select;
	}

	/**
	 * @param array $expressions
	 * @return static|DB_Query
	 */
	function select(array $expressions = array()){
		$this->getSelect()->setExpressions($expressions, false);
		return $this;
	}

	/**
	 * @param array $columns
	 * @param null|string $table_name [optional]
	 * @return static|DB_Query
	 */
	function selectColumns(array $columns, $table_name = null){
		$this->getSelect()->removeExpressions()->addColumns($columns, $table_name);
		return $this;
	}

	/**
	 * @param string $column_name [optional] Use '*' for "all columns"
	 * @param null|string $table_name [optional]
	 * @param null|string $select_as [optional]
	 * @return static|DB_Query
	 */
	function selectCount($column_name = "*", $table_name = null, $select_as = null){
		$this->getSelect()->removeExpressions()->addCount($column_name, $table_name, $select_as);
		return $this;
	}

	/**
	 * @param null|string $table_name [optional]
	 * @return static|DB_Query
	 */
	function selectAll($table_name = null){
		$this->getSelect()->removeExpressions()->addAllColumns($table_name);
		return $this;
	}

	/**
	 * @return static|DB_Query_OrderBy
	 */
	function getOrderBy(){
		if(!$this->order_by){
			$this->order_by = new DB_Query_OrderBy($this);
		}
		return $this->order_by;
	}

	/**
	 * @param array $order_by_expressions [optional]
	 * @return static|DB_Query
	 */
	function orderBy(array $order_by_expressions = array()){
		$this->getOrderBy()->setOrderByExpressions($order_by_expressions, false);
		return $this;
	}

	/**
	 * @param string $column_name
	 * @param null|string $order_how [optional]
	 * @param null|string $table_name [optional]
	 * @return static|DB_Query
	 */
	function orderByColumn($column_name, $order_how = null, $table_name = null){
		$this->getOrderBy()->removeExpressions()->addOrderByColumn($column_name, $order_how, $table_name);
		return $this;
	}

	/**
	 * @param array $columns
	 * @param null|string $table_name [optional]
	 * @return static|DB_Query
	 */
	function orderByColumns(array $columns, $table_name = null){
		$this->getOrderBy()->removeExpressions()->addOrderByColumns($columns, $table_name);
		return $this;
	}

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
	 * @return static|DB_Query_GroupBy
	 */
	function getGroupBy(){
		if(!$this->group_by){
			$this->group_by = new DB_Query_GroupBy($this);
		}
		return $this->group_by;
	}

	/**
	 * @param array $group_by_columns
	 * @return static|DB_Query
	 */
	function groupBy(array $group_by_columns = array()){
		$this->getGroupBy()->setGroupByColumns($group_by_columns, false);
		return $this;
	}

	/**
	 * @param string $column_name
	 * @param null|string $table_name [optional]
	 * @return static|DB_Query
	 */
	function groupByColumn($column_name, $table_name = null){
		$this->getGroupBy()->removeGroupByColumns()->groupByColumn($column_name, $table_name);
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
	function column($column_name, $table_name = null){
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
	public function setAllowSingleTableBuildSimplification($allow_build_simplification) {
		$this->allow_single_table_build_simplification = (bool)$allow_build_simplification;
	}

	/**
	 * @return boolean
	 */
	public function getAllowSingleTableBuildSimplification() {
		return $this->allow_single_table_build_simplification;
	}



}