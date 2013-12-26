<?php
namespace Et;
abstract class DB_Adapter_PDO extends DB_Adapter_Abstract {

	/**
	 * @var DB_Adapter_PDO_Config
	 */
	protected $config;

	/**
	 * @var \PDO
	 */
	protected $connection;

	/**
	 * @var string
	 */
	protected $driver_name;


	/**
	 * @param DB_Adapter_PDO_Config $config
	 */
	function __construct(DB_Adapter_PDO_Config $config){
		parent::__construct($config);
	}


	/**
	 * @return DB_Adapter_PDO_Config
	 */
	function getConfig(){
		return parent::getConfig();
	}

	/**
	 * @return array
	 */
	protected function _getDriverOptions(){

		$driver_options = $this->config->getDriverOptions(true);

		$driver_options[\PDO::ATTR_TIMEOUT] = $this->config->getConnectionTimeout();
		$driver_options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
		$driver_options[\PDO::ATTR_STRINGIFY_FETCHES] = false;
		isset($driver_options[\PDO::ATTR_EMULATE_PREPARES]) || $driver_options[\PDO::ATTR_EMULATE_PREPARES] = false;

		return $driver_options;
	}

	/**
	 * @throws DB_Adapter_Exception
	 */
	protected function _connect() {

		try {

			$this->connection = new \PDO(
				$this->config->getDSN(),
				$this->config->getUsername(),
				$this->config->getPassword(),
				$this->_getDriverOptions()
			);

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
	 * @throws DB_Adapter_Exception
	 */
	protected function _disconnect() {
		$this->connection = null;
	}


	/**
	 * @param string $string
	 * @return string
	 */
	public function quoteString($string) {
		return $this->connection->quote($string, \PDO::PARAM_STR);
	}

	/**
	 * @throws DB_Adapter_Exception
	 */
	function beginTransaction() {
		if(!$this->connection->beginTransaction()){
			throw new DB_Adapter_Exception(
				"Failed to begin transaction",
				DB_Adapter_Exception::CODE_OPERATION_FAILED
			);
		}
	}

	/**
	 * @throws DB_Adapter_Exception
	 */
	function commitTransaction() {
		if(!$this->connection->commit()){
			throw new DB_Adapter_Exception(
				"Failed to commit transaction",
				DB_Adapter_Exception::CODE_OPERATION_FAILED
			);
		}
	}

	/**
	 * @throws DB_Adapter_Exception
	 */
	function rollbackTransaction() {
		if(!$this->connection->rollBack()){
			throw new DB_Adapter_Exception(
				"Failed to rollback transaction",
				DB_Adapter_Exception::CODE_OPERATION_FAILED
			);
		}
	}

	/**
	 * @return bool
	 */
	function getTransactionStarted(){
		return $this->connection->inTransaction();
	}

	/**
	 * @param null|string $table_name [optional]
	 * @param null|string $primary_key [optional]
	 *
	 * @return string|int
	 */
	function getLastInsertID($table_name = null, $primary_key = null) {
		return $this->connection->lastInsertId($table_name);
	}

	/**
	 * @param string $sql_query
	 * @return int
	 */
	protected function _exec(&$sql_query) {
		return $this->connection->exec($sql_query);
	}


	protected function fetchLastError() {
		$this->resetLastError();
		$info = $this->connection->errorInfo();
		if(isset($info[2])){
			list($this->last_error_code, , $this->last_error_message) = $info;
		}
	}

	/**
	 * @param string $sql_query
	 * @return \PDOStatement|resource
	 */
	protected function _query(&$sql_query) {
		return $this->connection->query($sql_query);
	}

	/**
	 * @param string|DB_Query $sql_query
	 * @param array $query_data [optional]
	 * @param null|string $fetch_type [optional] One of DB::FETCH_ASSOCIATIVE|FETCH_VALUES, if NULL, FETCH_ASSOCIATIVE is used
	 * @param bool $cache_iterator_results [optional]
	 *
	 * @return DB_Iterator_Abstract
	 */
	function fetchIterator($sql_query, array $query_data = array(), $fetch_type = null, $cache_iterator_results = false) {
		/** @var $statement \PDOStatement */
		$statement = $this->runQuery($sql_query, $query_data, false);
		return new DB_Iterator_PDO($this, $statement, $fetch_type, $cache_iterator_results);
	}

	/**
	 * @param string $value
	 * @return string
	 */
	function quoteBinaryData($value) {
		return $this->connection->quote((string)$value, \PDO::PARAM_LOB);
	}
}