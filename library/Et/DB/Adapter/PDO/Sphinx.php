<?php
namespace Et;
/**
 * Sphinx PDO database adapter
 */
class DB_Adapter_PDO_Sphinx extends DB_Adapter_PDO_MySQL {

	/**
	 * @var string
	 */
	protected static $_adapter_type = "PDO_Sphinx";


	/**
	 * @var DB_Adapter_PDO_Sphinx_Config
	 */
	protected $config;

	/**
	 * @param DB_Adapter_PDO_Sphinx_Config $config
	 */
	function __construct(DB_Adapter_PDO_Sphinx_Config $config){
		$this->config = $config;
		$this->connect();
	}


	/**
	 * @return DB_Adapter_PDO_Sphinx_Config
	 */
	function getConfig(){
		return parent::getConfig();
	}

	/**
	 * @throws DB_Adapter_Exception
	 */
	protected function _connect() {

		try {

			$this->connection = new \PDO($this->config->getDSN());

		} catch (\PDOException $e) {

			throw new DB_Adapter_Exception(
				"Failed to connect to database [DSN: {$this->config->getDSN()}] - PDO error: {$e->getMessage()}",
				DB_Adapter_Exception::CODE_CONNECTION_FAILED,
				null,
				$e
			);

		}

		$this->driver_name = strtolower($this->connection->getAttribute(\PDO::ATTR_DRIVER_NAME));

	}

	/**
	 * @param string $table_name
	 *
	 * @throws DB_Adapter_Exception
	 * @return string
	 */
	function quoteTableName($table_name){
		$this->assert()->isVariableName($table_name);
		return $table_name;
	}

	/**
	 * @param string $column_name
	 *
	 * @throws DB_Adapter_Exception
	 * @return string
	 */
	function quoteColumnName($column_name){
		$this->assert()->isStringMatching($column_name, '^\w+(?:\.\w+)?$');
		return $column_name;
	}

	/**
	 * @return DB_Expression
	 */
	function getWeightExpression(){
		return new DB_Expression("WEIGHT()");
	}

	/**
	 * @param string $index_name
	 * @param string $search_expression
	 * @param DB_Query $query [optional]
	 * @return DB_Query
	 * @throws DB_Exception
	 */
	function prepareFetchMatchingQuery($index_name, $search_expression, DB_Query $query = null){
		if(!$query){
			$query = new DB_Query($index_name, $this);
		} elseif($query->getMainTableName() != $index_name){
			throw new DB_Exception(
				"Query main table name ({$query->getMainTableName()}) does not match required index name ('{$index_name}')",
				DB_Exception::CODE_INVALID_TABLE_NAME
			);
		}

		$query->getWhere()->addExpressionCompare($this->getMatchExpression($search_expression));
		if($query->getSelect()->isEmpty()){
			$query->getSelect()->addColumn("ID");
			$query->getSelect()->addExpression($this->getWeightExpression(), null, "weight");
		}

		if($query->getOrderBy()->isEmpty()){
			$query->getOrderBy()->addOrderByExpression($this->getWeightExpression(), DB_Query::ORDER_DESC);
		}

		return $query;
	}

	/**
	 * @param string $search_expression
	 * @return DB_Expression
	 */
	function getMatchExpression($search_expression){
		$search_expression = (string)$search_expression;
		return new DB_Expression("MATCH({$this->quoteString($search_expression)})");
	}

	/**
	 * @param string $index_name
	 * @param string $search_expression
	 * @param DB_Query $query [optional]
	 * @return array
	 */
	function fetchMatchingRows($index_name, $search_expression, DB_Query $query = null){
		return $this->fetchRows($this->prepareFetchMatchingQuery($index_name, $search_expression, $query));
	}

	/**
	 * @param string $index_name
	 * @param string $search_expression
	 * @param DB_Query $query [optional]
	 * @param bool $ignore_query_limit_and_offset [optional]
	 * @return int
	 */
	function fetchMatchingRowsCount($index_name, $search_expression, DB_Query $query = null, $ignore_query_limit_and_offset = true){
		return $this->fetchRowsCount(
			$this->prepareFetchMatchingQuery($index_name, $search_expression, $query),
			array(),
			$ignore_query_limit_and_offset
		);
	}

	/**
	 * @param string $index_name
	 * @param string $search_expression
	 * @param DB_Query $query [optional]
	 * @return array
	 */
	function fetchMatchingRow($index_name, $search_expression, DB_Query $query = null){
		return $this->fetchRow($this->prepareFetchMatchingQuery($index_name, $search_expression, $query));
	}

	/**
	 * @param string $index_name
	 * @param string $search_expression
	 * @param DB_Query $query [optional]
	 * @return array
	 */
	function fetchMatchingRowsAssociative($index_name, $search_expression, DB_Query $query = null){
		return $this->fetchRowsAssociative($this->prepareFetchMatchingQuery($index_name, $search_expression, $query));
	}

	/**
	 * @param string $index_name
	 * @param string $search_expression
	 * @param DB_Query $query [optional]
	 * @return array
	 */
	function fetchMatchingPairs($index_name, $search_expression, DB_Query $query = null){
		return $this->fetchPairs($this->prepareFetchMatchingQuery($index_name, $search_expression, $query));
	}

	/**
	 * @param string $index_name
	 * @param string $search_expression
	 * @param DB_Query $query [optional]
	 * @return array
	 */
	function fetchMatchingColumn($index_name, $search_expression, DB_Query $query = null){
		return $this->fetchColumn($this->prepareFetchMatchingQuery($index_name, $search_expression, $query));
	}

	/**
	 * @param string $index_name
	 * @param string $search_expression
	 * @param DB_Query $query [optional]
	 * @return mixed|bool
	 */
	function fetchMatchingValue($index_name, $search_expression, DB_Query $query = null){
		return $this->fetchValue($this->prepareFetchMatchingQuery($index_name, $search_expression, $query));
	}
}