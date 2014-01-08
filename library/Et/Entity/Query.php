<?php
namespace Et;

abstract class Entity_Query extends Object {

	const PROPERTY_PREFIX = "PROPERTY::";
	const FUNCTION_PREFIX = "FUNCTION::";
	
	const SORT_ASC = "ASC";
	const SORT_DESC = "DESC";

	const OP_AND = "AND";
	const OP_OR = "OR";
	const OP_AND_NOT = "AND NOT";
	const OP_OR_NOT = "OR_NOT";

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

	const SEL_COUNT = "COUNT()";

	/**
	 * @var array
	 */
	protected $_supported_logical_operators = array(
		self::OP_AND,
		self::OP_OR,
		self::OP_AND_NOT,
		self::OP_OR_NOT
	);

	/**
	 * @var array
	 */
	protected $_supported_compare_operators = array(
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
	 * @var string|\Et\Entity_Abstract
	 */
	protected $entity_class_name;

	/**
	 * @var string
	 */
	protected $entity_class_ID;

	/**
	 * @var string
	 */
	protected $entity_name;

	/**
	 * @var array
	 */
	protected $entity_properties_names;

	/**
	 * Format:
	 * array(property_name => equals_to)
	 * array(property_name => array(compare_operator, compare_argument)
	 * array("AND" | "OR" | "AND NOT" | "OR")
	 * array(array( ... nested expressions ... ))
	 *
	 * @var array
	 */
	protected $where = array();

	/**
	 * Format:
	 * array(property_name)
	 * array(property_name => select_as)
	 * array(function_name()[, array(arg1, arg2, ..)[, select_as]])
	 *
	 * @var array[]
	 */
	protected $select = array();

	/**
	 * Format:
	 * array(property_name => "ASC"|"DESC")
	 * @var array
	 */
	protected $sort_by = array();

	/**
	 * @var int
	 */
	protected $limit = 0;

	/**
	 * @var int
	 */
	protected $offset = 0;

	/**
	 * @param \Et\Entity_Abstract|string $entity_class_name
	 */
	function __construct($entity_class_name){
		$entity_class_name = Entity::resolveEntityClassName($entity_class_name);
		$this->entity_class_name = $entity_class_name;
		$this->entity_name = $entity_class_name::getEntityName();
		$this->entity_class_ID = $entity_class_name::getClassID();
		$properties_names = $entity_class_name::getEntityDefinition()->getPropertiesNames();
		$this->entity_properties_names = array_combine($properties_names, $properties_names);
	}

	/**
	 * @return array
	 */
	public function getEntityPropertiesNames() {
		return $this->entity_properties_names;
	}


	/**
	 * @return \Et\Entity_Abstract|string
	 */
	public function getEntityClassName() {
		return $this->entity_class_name;
	}

	/**
	 * @return string
	 */
	public function getEntityClassID() {
		return $this->entity_class_ID;
	}
	

	/**
	 * @return string
	 */
	public function getEntityName() {
		return $this->entity_name;
	}

	/**
	 * @param int $limit
	 * @param int $offset
	 * @return static|Entity_Query
	 */
	function limit($limit = 0, $offset = 0){
		$this->setLimit($limit);
		$this->setOffset($offset);
		return $this;
	}

	/**
	 * @param int $page
	 * @param int $items_per_page
	 * @return Entity_Query|static
	 */
	function setPage($page, $items_per_page){
		$page = max(1, (int)$page);
		$items_per_page = max(1, (int)$items_per_page);
		return $this->limit($items_per_page, ($page - 1) * $items_per_page);
	}

	/**
	 * @param int|null $limit
	 * @return static|Entity_Query
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
	 * @return static|Entity_Query
	 */
	function setOffset($offset){
		$this->offset =  max(0, (int)$offset);
		return $this;
	}

	/**
	 * @return int
	 */
	function getOffset(){
		return $this->offset;
	}

	function select(array $expressions){

	}

	/**
	 * @return array
	 */
	public function getSupportedCompareOperators() {
		return $this->_supported_compare_operators;
	}

	/**
	 * @return array
	 */
	public function getSupportedLogicalOperators() {
		return $this->_supported_logical_operators;
	}


	/**
	 * @param string $operator
	 * @return bool
	 */
	public function isLogicalOperatorSupported($operator){
		return in_array($operator, $this->getSupportedLogicalOperators());
	}

	/**
	 * @param string $operator
	 * @return bool
	 */
	public function isCompareOperatorSupported($operator){
		return in_array($operator, $this->getSupportedCompareOperators());
	}

	/**
	 * @param string $operator
	 * @throws Entity_Query_Exception
	 */
	public function checkLogicalOperator($operator){
		if(!$this->isLogicalOperatorSupported($operator)){
			throw new Entity_Query_Exception(
				"Unsupported logical operator '{$operator}', supported operators: " . implode(", ", $this->getSupportedLogicalOperators()),
				Entity_Query_Exception::CODE_INVALID_OPERATOR
			);
		}
	}

	/**
	 * @param string $operator
	 * @throws Entity_Query_Exception
	 */
	public function checkCompareOperator($operator){
		if(!$this->isCompareOperatorSupported($operator)){
			throw new Entity_Query_Exception(
				"Unsupported compare operator '{$operator}', supported operators: " . implode(", ", $this->getSupportedCompareOperators()),
				Entity_Query_Exception::CODE_INVALID_OPERATOR
			);
		}
	}

	public function checkPropertyName($property_name){
		if(!isset($this->entity_properties_names[$property_name])){
			throw new Entity_Query_Exception(
				"Entity {$this->entity_class_name}' has no \${$property_name} property",
				Entity_Query_Exception::CODE_INVALID_OPERATOR
			);
		}
	}

	/**
	 * @param string $operator - AND, OR, AND NOT, OR NOT
	 * @return static|\Et\Entity_Query
	 */
	function addWhereLogicOperator($operator){
		$this->checkLogicalOperator($operator);
		$this->where[] = array((string)$operator);
		return $this;
	}


	/**
	 * Format:
	 * array(property_name => value | PROPERTY::property_name)
	 * array(property_name => array(compare_operator, value | PROPERTY::property_name)
	 * array("AND" | "OR" | "AND NOT" | "OR")
	 * array(array( ... nested expressions ... ))
	 *
	 * @param array $expressions
	 * @return static|\Et\Entity_Query
	 */
	function where(array $expressions){
		$this->where = array();
		return $this->addWhereExpressions($expressions);
	}

	/**
	 * Format:
	 * array(property_name => value | PROPERTY::property_name)
	 * array(property_name => array(compare_operator, value | PROPERTY::property_name)
	 * array("AND" | "OR" | "AND NOT" | "OR")
	 * array(array( ... nested expressions ... ))
	 *
	 * @param array $expressions
	 * @return static|\Et\Entity_Query
	 */
	function addWhereExpressions(array $expressions){

		foreach($expressions as $k => $v){
			if(is_numeric($k)){
				if(is_string($v)){
					$this->addWhereLogicOperator($v);
					continue;
				}

				if(!is_array($v)){
					throw new Entity_Query_Exception(
						"Invalid expression '{$k}' - nested expression (array) or logical operator expected",
						Entity_Query_Exception::CODE_INVALID_EXPRESSION
					);
				}
				$this->addNestedWhereExpressions($v);
				continue;

			}


		}

		return $this;
	}



	/**
	 * @return static|\Et\Entity_Query
	 */
	function addWhere_AND(){
		$this->where[] = self::OP_AND;
		return $this;
	}

	/**
	 * @return static|\Et\Entity_Query
	 */
	function addWhere_OR(){
		$this->where[] = self::OP_OR;
		return $this;
	}

	/**
	 * @return static|\Et\Entity_Query
	 */
	function addWhere_AND_NOT(){
		$this->where[] = self::OP_AND_NOT;
		return $this;
	}

	/**
	 * @return static|\Et\Entity_Query
	 */
	function addWhere_OR_NOT(){
		$this->where[] = self::OP_OR_NOT;
		return $this;
	}

	function addNestedWhereExpressions(array $expressions){

	}

	function addPropertiesEqual(array $properties){

	}

	function addPropertyEquals($property_name, $value){

	}

	function addPropertyCompare($property_name, $compare_operator, $value){

	}

	/**
	 * @param array|string $sort_by
	 * @param null|string $sort_how [optional]
	 *
	 * @return static|\Et\Entity_Query
	 */
	public function sortBy($sort_by = array(), $sort_how = null) {
		if(!$sort_by){
			$this->sort_by = array();
			return $this;
		}

		if(!is_array($sort_by)){
			$sort_by = array($sort_by => $sort_how);
		}

		if(!$sort_how){
			$sort_how = self::SORT_ASC;
		} else {
			$sort_how = strtolower($sort_how) == self::SORT_DESC
					? self::SORT_DESC
					: self::SORT_ASC;
		}

		$this->sort_by = array();
		foreach($sort_by as $k => $v){
			if(is_numeric($k)){

				$this->sort_by[$v] = $sort_how;

			} else {

				if(!$v){
					$v = $sort_how;
				} else {
					$v = strtolower($v) == self::SORT_DESC
						? self::SORT_DESC
						: self::SORT_ASC;
				}
				$this->sort_by[$v] = $v;

			}
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function getSortBy() {
		return $this->sort_by;
	}

}