<?php
namespace Et;
abstract class DB_Adapter_Abstract extends Object {

	const DB_TYPE_MYSQL = "mysql";
	const DB_TYPE_SQLITE = "sqlite";
	const DB_TYPE_SPHINX = "sphinx";
	const DB_TYPE_CASSANDRA = "cassandra";

	/**
	 * @var DB_Adapter_Config_Abstract
	 */
	protected $config;

	/**
	 * @var object|resource
	 */
	protected $connection;

	/**
	 * @var int
	 */
	protected $last_error_code = 0;

	/**
	 * @var string
	 */
	protected $last_error_message = "";

	/**
	 * @var \Et\Locales_Timezone|null
	 */
	protected $default_quote_timezone = null;

	/**
	 * @var string
	 */
	protected $tables_list;

	/**
	 * @var DB_Profiler
	 */
	protected $profiler;

	/**
	 * @param DB_Adapter_Config_Abstract $config
	 */
	function __construct(DB_Adapter_Config_Abstract $config){
		$this->config = $config;
		$this->connect();
	}

	/**
	 * @param null|string|\DateTimeZone|\Et\Locales_Timezone $default_quote_timezone
	 */
	public function setDefaultQuoteTimezone($default_quote_timezone = null) {
		if(!$default_quote_timezone){
			$this->default_quote_timezone = null;
			return;
		}
		$this->default_quote_timezone = Locales::getTimezone($default_quote_timezone);
	}

	/**
	 * @return \Et\Locales_Timezone|null
	 */
	public function getDefaultQuoteTimezone() {
		return $this->default_quote_timezone;
	}


	/**
	 * @throws DB_Exception
	 */
	abstract public function getDatabaseType();

	/**
	 * @param \Et\DB_Profiler $profiler
	 */
	public function setProfiler(DB_Profiler $profiler = null) {
		$this->profiler = $profiler;
	}

	/**
	 * @return \Et\DB_Profiler|null
	 */
	public function getProfiler() {
		return $this->profiler;
	}


	function __destruct(){
		$this->disconnect();
	}

	/**
	 * @return DB_Adapter_Config_Abstract
	 */
	public function getConfig() {
		return $this->config;
	}

	function __sleep(){
		return array("config");
	}

	function __wakeup(){
		$this->connect();
	}

	/**
	 * @return object|resource
	 */
	public function getConnection() {
		return $this->connection;
	}



	/**
	 * @throws DB_Adapter_Exception
	 */
	function connect(){
		$this->resetLastError();

		try {
			$this->_connect();
		} catch(Exception $e){
			throw new DB_Adapter_Exception(
				"DB connection failed - {$e->getMessage()}",
				DB_Adapter_Exception::CODE_CONNECTION_FAILED,
				null,
				$e
			);
		}
	}

	/**
	 * @throws DB_Adapter_Exception
	 */
	abstract protected function _connect();


	/**
	 * @throws DB_Adapter_Exception
	 */
	function disconnect(){
		$this->resetLastError();
		if(!$this->connection){
			return;
		}

		try {
			$this->_disconnect();
		} catch(Exception $e){
			throw new DB_Adapter_Exception(
				"Failed to disconnect DB - {$e->getMessage()}",
				DB_Adapter_Exception::CODE_DISCONNECTION_FAILED,
				null,
				$e
			);
		}

		$this->connection = null;
	}

	/**
	 * @throws DB_Adapter_Exception
	 */
	abstract protected function _disconnect();

	/**
	 * @return int|string
	 */
	public function getLastErrorCode() {
		return $this->last_error_code;
	}

	/**
	 * @return string
	 */
	public function getLastErrorMessage() {
		return $this->last_error_message;
	}

	/**
	 * @return string
	 */
	function getLastError(){
		$error_number = $this->getLastErrorCode();
		$error_message = $this->getLastErrorMessage();
		if(!$error_number && !$error_message){
			return "";
		}

		$error = "";
		if($error_number){
			$error .= "[{$error_number}] ";
		}

		if($error_message){
			$error .= $error_message;
		}

		return $error;
	}

	protected function resetLastError(){
		$this->last_error_code = 0;
		$this->last_error_message = "";
	}

	/**
	 * @param int|string $error_code
	 * @param string $error_message
	 */
	protected function setLastError($error_code, $error_message){
		$this->last_error_code = $error_code;
		$this->last_error_message = (string)$error_message;
	}


	abstract protected function fetchLastError();


	/**
	 * @param string $string
	 * @return string
	 */
	abstract public function quoteString($string);

	/**
	 * @param string $value
	 * @return string
	 */
	abstract function quoteBinaryData($value);

	/**
	 * @param array $value
	 * @return string|mixed
	 */
	public function quoteArrayData(array $value){
		return $this->quoteString(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	}


	/**
	 * @param null|string|int|\DateTime|\Et\Locales_DateTime $date [optional]
	 * @param null|string|\DateTimeZone|\Et\Locales_Timezone $timezone [optional]
	 * @param null|string|\DateTimeZone|\Et\Locales_Timezone $target_timezone [optional]
	 * @return string
	 */
	public function quoteDate($date, $timezone = null, $target_timezone = null){
		if(!$date){
			return "''";
		}

		$date = Locales::getDate($date, $timezone);
		if(!$target_timezone && $this->default_quote_timezone){
			$target_timezone = $this->default_quote_timezone;
		}

		if($target_timezone){
			$target_timezone = Locales::getTimezone($target_timezone);
			$date->setTimezone($target_timezone);
		}

		return "'{$date->format("Y-m-d")}'";
	}

	/**
	 * @param null|string|int|\DateTime|\Et\Locales_DateTime $datetime [optional]
	 * @param null|string|\DateTimeZone|\Et\Locales_Timezone $timezone [optional]
	 * @param null|string|\DateTimeZone|\Et\Locales_Timezone $target_timezone [optional]
	 * @return string
	 */
	public function quoteDateTime($datetime, $timezone = null, $target_timezone = null){
		if(!$datetime){
			return "''";
		}

		$datetime = Locales::getDateTime($datetime, $timezone);

		if(!$target_timezone && $this->default_quote_timezone){
			$target_timezone = $this->default_quote_timezone;
		}

		if($target_timezone){
			$target_timezone = Locales::getTimezone($target_timezone);
			$datetime->setTimezone($target_timezone);
		}

		return "'{$datetime->format("Y-m-d H:i:s")}'";
	}



	/**
	 * @param array|\Iterator $values
	 * @return DB_Expression
	 * @throws DB_Adapter_Exception
	 */
	function quoteIN($values){
		$values = $this->quoteValues($values, false);
		if(!$values){
			throw new DB_Adapter_Exception(
				"IN() statement requires at least 1 value",
				DB_Adapter_Exception::CODE_QUOTE_FAILED
			);
		}
		return DB::expression(implode(", ", $values));
	}

	/**
	 * @param array|\Iterator $values
	 * @param bool $get_as_string [optional]
	 * @return array|string
	 * @throws DB_Adapter_Exception
	 */
	function quoteValues($values, $get_as_string = false){
		if(!is_array($values) && !$values instanceof \Iterator){

			throw new DB_Adapter_Exception(
				"Only arrays or instances of Iterator may be quoted",
				DB_Adapter_Exception::CODE_QUOTE_FAILED
			);

		}

		$quoted = array();
		foreach($values as $key => $value){
			$quoted[$key] = $this->quoteValue($value);
		}

		if($get_as_string){
			return implode(", ", $quoted);
		}

		return $quoted;
	}

	/**
	 * @param mixed $value
	 *
	 * @return string|int|float
	 * @throws DB_Adapter_Exception
	 */
	function quoteValue($value){
		if(is_string($value)){
			return $this->quoteString($value);
		}

		if($value === null){
			return "NULL";
		}

		if(is_bool($value)){
			return (int)$value;
		}

		if(is_numeric($value)){
			return $value;
		}

		if($value instanceof DB_Expression){
			return (string)$value;
		}

		if($value instanceof DB_Table_Column){
			return $this->quoteColumnName((string)$value);
		}

		if(is_array($value)){
			return $this->quoteArrayData($value);
		}

		if(is_object($value)){

			if($value instanceof Locales_DateTime){
				if($value instanceof Locales_Date){
					return $this->quoteDate($value);
				}

				return $this->quoteDateTime($value);
			}

			if(method_exists($value, "__toString")){
				return $this->quoteString((string)$value);
			}

			if(method_exists($value, "toString")){
				return $this->quoteString($value->toString());
			}
		}

		throw new DB_Adapter_Exception(
			'Invalid value to quote for DB',
			DB_Adapter_Exception::CODE_QUOTE_FAILED
		);

	}

	/**
	 * @param array $row
	 *
	 * @return array
	 */
	function quoteRow(array $row){
		$output = array();
		foreach($row as $r => $v){
			$output[$this->quoteColumnName($r)] = $this->quoteValue($v);
		}
		return $output;
	}

	/**
	 * @param string $table_name
	 *
	 * @return string
	 */
	function quoteTableName($table_name){
		$table_name = (string)$table_name;
		Debug_Assert::isVariableName($table_name);
		return $table_name;
	}

	/**
	 * @param string $column_name
	 *
	 * @return string
	 */
	function quoteColumnName($column_name){
		$column_name = (string)$column_name;
		Debug_Assert::isStringMatching($column_name, '^\w+(\.\w+)?$');
		return $column_name;
	}

	/**
	 * @param array $columns_names
	 * @return array
	 */
	function quoteColumnNames(array $columns_names){
		$output = array();
		foreach($columns_names as $k => $v){
			if(is_numeric($k)){
				$output[$k] = $this->quoteColumnName($v);
			} else {
				$output[$this->quoteColumnName($k)] = $this->quoteColumnName($v);
			}
		}
		return $output;
	}

	/**
	 * @param string $sql_query
	 * @param array $query_data
	 *
	 * @throws DB_Adapter_Exception
	 * @return string
	 */
	function bindDataToQuery($sql_query, array $query_data){
		if(!$query_data){
			return $sql_query;
		}

		$replacements = array();
		foreach($query_data as $placeholder => $value){
			try {

				$replacements[":{$placeholder}"] = $this->quoteValue($value);

			} catch(DB_Adapter_Exception $e){

				throw new DB_Adapter_Exception(
					"Failed to quote value with key '{$placeholder}' - {$e->getMessage()}",
					DB_Adapter_Exception::CODE_BINDING_FAILED,
					array(
					     "value" => $value
					),
					$e
				);

			}

		}

		krsort($replacements, SORT_STRING);

		return str_replace(
					array_keys($replacements),
					array_values($replacements),
					$sql_query
		);
	}



	/**
	 * @param string|DB_Query $sql_query
	 * @param array $query_data [optional]
	 *
	 * @throws DB_Adapter_Exception
	 * @return int number of affected rows
	 */
	function exec($sql_query, array $query_data = array()){
		return $this->runQuery($sql_query, $query_data, true);
	}


	/**
	 * @param string|DB_Query $sql_query
	 * @param array $query_data
	 * @param bool $exec_only
	 *
	 * @throws DB_Adapter_Exception
	 * @return resource|object|int
	 */
	protected function runQuery(&$sql_query, array &$query_data, $exec_only){

		if($sql_query instanceof DB_Query){
			$sql_query = $this->buildQuery($sql_query);
		}

		if($query_data){
			$sql_query = $this->bindDataToQuery($sql_query, $query_data);
		}

		try {

			if($this->profiler){

				$profiler_query = $sql_query;
				$query_period = $this->profiler->period($profiler_query);

				if($exec_only){
					$result = $this->_exec($sql_query);
				} else {
					$result = $this->_query($sql_query);
				}

				$query_period->end();

			} else {

				if($exec_only){
					$result = $this->_exec($sql_query);
				} else {
					$result = $this->_query($sql_query);
				}

			}

			if($result === false){
				throw new DB_Adapter_Exception(
					"FALSE returned as a result",
					DB_Adapter_Exception::CODE_QUERY_FAILED
				);
			}

		} catch(\Exception $e){

			$this->fetchLastError();

			$exception = new DB_Adapter_Exception(
				"SQL query execution failed - {$e->getMessage()}",
				DB_Adapter_Exception::CODE_QUERY_FAILED,
				array(
					"SQL query" => $sql_query,
					"query data" => $query_data
				),
				$e
			);

			$exception->setSqlErrorCode($this->getLastErrorCode());
			$exception->setSqlErrorMessage($this->getLastErrorMessage());

			throw $exception;
		}


		return $result;
	}

	/**
	 * @param string $sql_query
	 *
	 * @return int
	 */
	abstract protected function _exec(&$sql_query);

	/**
	 * @param string $sql_query
	 *
	 * @return object|resource
	 */
	abstract protected function _query(&$sql_query);


	/**
	 * @param bool $force_refresh [optional]
	 *
	 * @return array
	 */
	function listTables($force_refresh = false){
		if(!$force_refresh && is_array($this->tables_list)){
			return $this->tables_list;
		}
		$this->tables_list = $this->_listTables();
		return $this->tables_list;
	}

	/**
	 * @return array
	 */
	abstract protected function _listTables();

	/**
	 * @param string $table_name
	 *
	 * @param bool $force_refresh_tables_list [optional]
	 *
	 * @return bool
	 */
	function getTableExists($table_name, $force_refresh_tables_list = false){
		return in_array($table_name, $this->listTables($force_refresh_tables_list));
	}

	/**
	 * @param string|DB_Query $sql_query
	 * @param array $query_data [optional]
	 * @param null|string $fetch_type [optional] One of DB::FETCH_ASSOCIATIVE|FETCH_VALUES, if NULL, FETCH_ASSOCIATIVE is used
	 * @param bool $cache_iterator_results [optional]
	 *
	 * @return DB_Iterator_Abstract
	 */
	abstract function fetchIterator($sql_query, array $query_data = array(), $fetch_type = null, $cache_iterator_results = false);

	/**
	 * @param string|DB_Query $sql_query
	 * @param array $query_data [optional]
	 * @param null|string $fetch_type [optional] One of DB::FETCH_ASSOCIATIVE|FETCH_VALUES, if NULL, FETCH_ASSOCIATIVE is used
	 *
	 * @return array|bool
	 *
	 * @throws DB_Adapter_Exception
	 */
	function fetchRow($sql_query, array $query_data = array(), $fetch_type = null){
		$iterator = $this->fetchIterator($sql_query, $query_data, $fetch_type, false);
		$row = $iterator->fetchRow();
		$iterator->freeResult();
		unset($iterator);
		return $row;
	}


	/**
	 * @param DB_Table_Key $key
	 * @param array|null $row_columns [optional] If empty, * is used in select statement
	 * @param DB_Query $query [optional]
	 * @return array|bool
	 */
	function fetchRowByKey(DB_Table_Key $key, array $row_columns = null, DB_Query $query = null){
		$query = $key->getWhereQuery($query);
		if(!$row_columns){
			$query->selectAll($key->getTableName());
		} else {
			$query->selectColumns($row_columns, $key->getTableName());
		}
		return $this->fetchRow($query);
	}

	

	/**
	 * @param DB_Table_Key $key
	 * @param array|null $row_columns [optional] If empty, * is used in select statement
	 * @param DB_Query $query [optional]
	 * @return array
	 */
	function fetchRowsByKey(DB_Table_Key $key, array $row_columns = null, DB_Query $query = null){
		$query = $key->getWhereQuery($query);
		if(!$row_columns){
			$query->getSelect()->addAllColumns($key->getTableName());
		} else {
			$query->getSelect()->addColumns($row_columns, $key->getTableName());
		}
		return $this->fetchRows($query);
	}

	/**
	 * @param DB_Table_Key[] $keys
	 * @param array|null $row_columns [optional] If empty, * is used in select statement
	 * @param DB_Query $query [optional]
	 * @throws DB_Exception
	 * @return array
	 */
	function fetchRowsByKeys(array $keys, array $row_columns = null, DB_Query $query = null){
		if(!$keys){
			return array();
		}
		Debug_Assert::isArrayOfInstances($keys, "Et\\DB_Table_Key");

		$sets = array();

		/** @var $key DB_Table_Key */
		$key = null;
		foreach($keys as $idx => $k){
			if(!$k->hasAllColumnValues()){
				throw new DB_Exception(
					"Key '{$idx}' does not have values filled in",
					DB_Exception::CODE_INVALID_KEY
				);
			}

			if(!$key){
				$key = $k;
				$sets[] = new DB_Expression("(" . implode(", ",  $this->quoteValues($key->getColumnValues())) . ")");
				continue;
			}

			if($k->getTableName() != $key->getTableName() || $k->getColumnNames() != $key->getColumnNames()){
				throw new DB_Exception(
					"Key '{$idx}' is different than first key (different table name or columns names",
					DB_Exception::CODE_INVALID_KEY
				);
			}

			$sets[] = new DB_Expression("(" . implode(", ",  $this->quoteValues($k->getColumnValues())) . ")");
		}

		if(!$query){
			$query = new DB_Query($key->getTableName());
		}

		if(!$row_columns){
			$query->getSelect()->addAllColumns($key->getTableName());
		} else {
			$query->getSelect()->addColumns($row_columns, $key->getTableName());
		}

		$query->getWhere()->addExpressionCompare(
			implode(", ", $this->quoteColumnNames($key->getColumnNames())),
			DB_Query_Where::CMP_IN,
			$sets
		);

		return $this->fetchRows($query);
	}

	/**
	 * @param array $row [reference]
	 * @param string|null $key_column
	 * @return string|int
	 * @throws DB_Adapter_Exception
	 */
	protected function resolveColumnKey(array &$row, $key_column){
		reset($row);
		if($key_column === null){
			return key($row);
		}

		if(array_key_exists($row, $key_column)){
			return $key_column;
		}

		if(is_numeric($key_column)){
			$keys = array_keys($row);
			if(isset($keys[$key_column])){
				return $keys[$key_column];
			}
		}

		throw new DB_Adapter_Exception(
			"Column '{$key_column}' not found in row",
			DB_Adapter_Exception::CODE_INVALID_COLUMN
		);
	}

	/**
	 * @param string|DB_Query $sql_query
	 * @param array $query_data [optional]
	 * @param null|string|int $value_column [optional] Name of value column in row, if NULL, first column is used
	 * @throws DB_Adapter_Exception
	 *
	 * @return mixed|bool
	 */
	function fetchValue($sql_query, array $query_data = array(), $value_column = null){
		$row = $this->fetchRow($sql_query, $query_data, DB::FETCH_ASSOCIATIVE);
		if(!$row){
			return false;
		}
		$value_column = $this->resolveColumnKey($row, $value_column);
		return $row[$value_column];
	}

	/**
	 * @param string|DB_Query $sql_query
	 * @param array $query_data [optional]
	 * @param null|string|int $value_column [optional] Name of value column in row, if NULL, first column is used
	 *
	 * @throws DB_Adapter_Exception
	 *
	 * @return array
	 */
	function fetchColumn($sql_query, array $query_data = array(), $value_column = null){
		$iterator = $this->fetchIterator($sql_query, $query_data, DB::FETCH_ASSOCIATIVE, false);
		$output = array();
		foreach($iterator as $row_idx => $row){
			if(!$row_idx){
				$value_column = $this->resolveColumnKey($row, $value_column);
			}
			$output[] = $row[$value_column];
		}
		$iterator->freeResult();
		unset($iterator);
		return $output;
	}

	/**
	 * @param string|DB_Query $sql_query
	 * @param array $query_data [optional]
	 * @param null|string|int $key_column [optional] Name of key column in row, if NULL, first column is used
	 * @param null|string|int $value_column [optional] Name of value column in row, if NULL, second column is used if exists, if not, first column is used
	 *
	 * @throws DB_Adapter_Exception
	 *
	 * @return array
	 */
	function fetchPairs($sql_query, array $query_data = array(), $key_column = null, $value_column = null){
		$iterator = $this->fetchIterator($sql_query, $query_data, DB::FETCH_ASSOCIATIVE, false);
		$output = array();
		foreach($iterator as $row_idx => $row){
			if(!$row_idx){
				$key_row = $row;
				$key_column = $this->resolveColumnKey($key_row, $key_column);
				array_shift($key_row);
				if(!$key_row){
					$value_column = $key_column;
				} else {
					$value_column = $this->resolveColumnKey($key_row, $value_column);
				}
			}

			$output[$row[$key_column]] = $row[$value_column];
		}
		$iterator->freeResult();
		unset($iterator);
		return $output;
	}

	/**
	 * @param string|DB_Query $sql_query
	 * @param array $query_data [optional]
	 * @param null|string $fetch_type [optional] One of DB::FETCH_ASSOCIATIVE|FETCH_VALUES, if NULL, FETCH_ASSOCIATIVE is used
	 * @param null|string|int $key_column [optional] Name of key column in row, if NULL, first column is used
	 *
	 * @throws DB_Adapter_Exception
	 *
	 * @return array
	 */
	function fetchRowsAssociative($sql_query, array $query_data = array(), $fetch_type = null, $key_column = null){
		$iterator = $this->fetchIterator($sql_query, $query_data, $fetch_type, false);

		$output = array();
		foreach($iterator as $row_idx => $row){
			if(!$row_idx){
				$key_column = $this->resolveColumnKey($row, $key_column);
			}
			$output[$row[$key_column]] = $row;
		}
		$iterator->freeResult();
		unset($iterator);
		return $output;
	}

	/**
	 * @param string|DB_Query $sql_query
	 * @param array $query_data [optional]
	 * @param null|string $fetch_type [optional] One of DB::FETCH_ASSOC|VALUES|BOTH, if NULL, FETCH_ASSOC is used
	 *
	 * @throws DB_Adapter_Exception
	 *
	 * @return array
	 */
	function fetchRows($sql_query,  array $query_data = array(), $fetch_type = null){

		$iterator = $this->fetchIterator($sql_query, $query_data, $fetch_type, false);
		$output = array();
		foreach($iterator as $row){
			$output[] = $row;
		}
		$iterator->freeResult();
		unset($iterator);
		return $output;
	}

	/**
	 * @param int $table_name
	 * @param array $row
	 *
	 * @throws DB_Adapter_Exception
	 *
	 * @return int
	 */
	function insert($table_name, array $row){
		return $this->insertOrReplace($table_name, $row, false);
	}

	/**
	 * @param int $table_name
	 * @param array $row
	 * @param bool $replace
	 *
	 * @throws DB_Adapter_Exception
	 * @return int
	 */
	protected function insertOrReplace($table_name, array $row, $replace){
		if(!$row){
			throw new DB_Adapter_Exception(
				"Cannot insert empty row",
				DB_Adapter_Exception::CODE_INVALID_ARGUMENT
			);
		}

		$query_columns = array();
		foreach($row as $r => $v){
			$query_columns[$this->quoteColumnName($r)] = $this->quoteValue($v);
		}

		$query = ($replace ? "REPLACE" : "INSERT") . " INTO " . $this->quoteTableName($table_name) . "(\n    ";
		$query .= implode(",\n    ", array_keys($query_columns));
		$query .= "\n) VALUES (\n    ";
		$query .= implode(",\n    ", array_values($query_columns));
		$query .= ");";

		return $this->exec($query, $row);
	}

	/**
	 * @param int $table_name
	 * @param array $row
	 *
	 * @throws DB_Adapter_Exception
	 *
	 * @return int
	 */
	function replace($table_name, array $row){
		return $this->insertOrReplace($table_name, $row, true);
	}

	/**
	 * @param string $table_name
	 * @param array[] $rows
	 * @param null|int $max_rows_per_query [optional]
	 * @param bool $replace
	 *
	 * @throws DB_Adapter_Exception
	 * @return int
	 */
	protected function insertOrReplaceMultiple($table_name, array $rows, $max_rows_per_query = null, $replace = false){

		if(!$rows){
			return 0;
		}

		if($max_rows_per_query !== null){
			Debug_Assert::isGreaterOrEqualThan($max_rows_per_query, 1);
		}

		$columns = null;
		$queries_data = array();
		$row_nr = 0;
		$query_data = array();
		foreach($rows as $k => &$row){
			++$row_nr;

			if(!is_array($row)){
				throw new DB_Adapter_Exception(
					"[row {$k}] - Row must be an array",
					DB_Adapter_Exception::CODE_INVALID_ARGUMENT
				);
			}

			if(!$row){
				throw new DB_Adapter_Exception(
					"[row {$k}] - Cannot insert empty row",
					DB_Adapter_Exception::CODE_INVALID_ARGUMENT
				);
			}

			if($columns === null){
				$columns = array_keys($row);
			} else {
				$cols = array_keys($row);
				if($cols !== $columns){
					throw new DB_Adapter_Exception(
						"[row {$k}] - Invalid row columns, expected '".implode(", ", $columns)."', given '".implode(", ", $cols)."'",
						DB_Adapter_Exception::CODE_INVALID_ARGUMENT,
						array(
							"row data" => $row
						)
					);
				}
			}

			foreach($row as $i => $value){
				$row[$i] = $this->quoteValue($value);
			}
			$query_data[] = $row;

			if($max_rows_per_query > 0 && $row_nr % $max_rows_per_query == 0){
				$queries_data[] = $query_data;
				$query_data = array();
			}
		}

		if($query_data){
			$queries_data[] = $query_data;
		}

		foreach($columns as $i => $column){
			$columns[$i] = $this->quoteColumnName($column);
		}

		$query_start = ($replace ? "REPLACE" : "INSERT")." INTO " . $this->quoteTableName($table_name) . "(\n    ";
		$query_start .= implode(",\n    ", $columns);
		$query_start .= "\n) VALUES ";

		$count = 0;
		$this->beginTransaction();
		try {
			$query = $query_start;
			foreach($queries_data as $query_data){
				foreach($query_data as $row){
					$query .= "\n(\n    ";
					$query .= implode(",\n    ", $row);
					$query .= "\n),";
				}
			}
			$count += $this->exec(rtrim($query, ","));
			$this->commitTransaction();
		} catch(\Exception $e){
			$this->rollbackTransaction();
			throw new DB_Adapter_Exception(
				"Failed to insert rows - {$e->getMessage()}",
				DB_Adapter_Exception::CODE_QUERY_FAILED,
				null,
				$e
			);
		}

		return $count;
	}

	/**
	 * @param string $table_name
	 * @param array[] $rows
	 * @param null|int $max_rows_per_query [optional]
	 *
	 * @return int
	 * @throws DB_Adapter_Exception
	 */
	function insertMultiple($table_name, array $rows, $max_rows_per_query = null){
		return $this->insertOrReplaceMultiple($table_name, $rows, $max_rows_per_query, false);
	}

	/**
	 * @param string $table_name
	 * @param array[] $rows
	 * @param null|int $max_rows_per_query [optional]
	 *
	 * @return int
	 * @throws DB_Adapter_Exception
	 */
	function replaceMultiple($table_name, array $rows, $max_rows_per_query = null){
		return $this->insertOrReplaceMultiple($table_name, $rows, $max_rows_per_query, true);
	}


	/**
	 * @param string $table_name
	 * @param array $new_row_data
	 * @param string|DB_Query|DB_Table_Key|array $where_query [optional]
	 * @param array $where_query_data [optional]
	 *
	 * @return int
	 * @throws DB_Adapter_Exception
	 */
	function update($table_name, array $new_row_data, $where_query = "", array $where_query_data = array()){

		if(!$new_row_data){
			throw new DB_Adapter_Exception(
				"Cannot update row with empty row",
				DB_Adapter_Exception::CODE_INVALID_ARGUMENT
			);
		}

		if(is_array($where_query)){
			$where_query = DB_Query::getInstance($table_name, array(), $where_query);
		}

		if($where_query instanceof DB_Table_Key){
			$where_query = $where_query->getWhereQuery();
		}

		if($where_query instanceof DB_Query){
			$where_query = $this->buildWhereQueryPart($where_query);
		}

		$where_query = trim($where_query);
		if($where_query_data){
			$where_query = $this->bindDataToQuery($where_query, $where_query_data);
		}

		$query_columns = array();
		foreach($new_row_data as $r => $v){
			$query_columns[] = $this->quoteColumnName($r) . " = :{$r}";
		}

		$query = "UPDATE " . $this->quoteTableName($table_name) . "SET\n    ";
		$query .= implode(",\n    ", $query_columns);

		if($where_query !== ""){
			$query .= "\nWHERE\n{$where_query}";
		}

		return $this->exec($query, $new_row_data);
	}


	/**
	 * @param string $table_name
	 * @param string|array|DB_Query|DB_Table_Key $where_query
	 * @param array $where_query_data [optional]
	 *
	 * @return int
	 * @throws DB_Adapter_Exception
	 */
	function delete($table_name, $where_query, array $where_query_data = array()){

		if(is_array($where_query)){
			$where_query = DB_Query::getInstance($table_name, array(), $where_query);
		}

		if($where_query instanceof DB_Table_Key){
			$where_query = $where_query->getWhereQuery();
		}

		if($where_query instanceof DB_Query){
			$where_query = $this->buildWhereQueryPart($where_query);
		}

		$where_query = trim($where_query);
		if($where_query === ""){
			throw new DB_Adapter_Exception(
				"'Where part' may not be empty string",
				DB_Adapter_Exception::CODE_INVALID_ARGUMENT
			);
		}

		if($where_query_data){
			$where_query = $this->bindDataToQuery($where_query, $where_query_data);
		}

		$query = "DELETE FROM\n    " . $this->quoteTableName($table_name) . "\nWHERE\n    {$where_query}";
		return $this->exec($query);
	}


	/**
	 * @param string $table_name
	 * @return int
	 * @throws DB_Adapter_Exception
	 */
	abstract function truncateTable($table_name);


	/**
	 * @param string|DB_Query|DB_Table_Key $sql_query
	 * @param array $query_data [optional]
	 * @param bool $ignore_query_limit_and_offset [optional]
	 * @throws DB_Adapter_Exception
	 * @return int
	 */
	public function fetchRowsCount($sql_query, array $query_data = array(), $ignore_query_limit_and_offset = false){
		if($sql_query instanceof DB_Table_Key){
			$sql_query = $sql_query->getWhereQuery();
		}

		if($sql_query instanceof DB_Query){
			$cloned_query = $sql_query->cloneInstance(true)->selectCount();
			if($ignore_query_limit_and_offset){
				$cloned_query->limit(null, null);
			}
			$cloned_query->getOrderBy()->removeExpressions();
			return (int)$this->fetchValue($cloned_query);
		}

		$sql_query = ltrim($sql_query);
		if(!preg_match("~^SELECT~i", $sql_query)){
			throw new DB_Adapter_Exception(
				"Invalid SQL query - expected format like: SELECT col1, col2 .. , colN FROM table_name ...",
				DB_Adapter_Exception::CODE_INVALID_ARGUMENT
			);
		}

		$sql_query = preg_replace("~^SELECT(.+?)FROM~is", "SELECT\n    COUNT(*)\nFROM", $sql_query);
		if($ignore_query_limit_and_offset){
			$query_data = preg_replace('~LIMIT\s+\d+(?:\s*,\s*\d+)?|OFFSET\s+\d+~is', "", $query_data);
		}

		return $this->fetchValue($sql_query, $query_data);
	}

	/**
	 * @throws DB_Adapter_Exception
	 */
	abstract function beginTransaction();

	/**
	 * @throws DB_Adapter_Exception
	 */
	abstract function commitTransaction();

	/**
	 * @throws DB_Adapter_Exception
	 */
	abstract function rollbackTransaction();

	/**
	 * @return bool
	 */
	abstract function getTransactionStarted();

	/**
	 * @param string|DB_Query|DB_Table_Key $sql_query
	 * @param array $query_data [optional]
	 * @return bool
	 */
	function fetchRowExists($sql_query, array $query_data = array()){
		return $this->fetchRowsCount($sql_query, $query_data) > 0;
	}

	/**
	 * @param string $table_name
	 *
	 * @return int
	 */
	function dropTable($table_name){
		return $this->exec("DROP TABLE " . $this->quoteTableName($table_name));
	}

	/**
	 * @param string $source_table_name
	 * @param string $target_table_name
	 */
	abstract function renameTable($source_table_name, $target_table_name);

	/**
	 * @param string $source_table_name
	 * @param string $target_table_name
	 */
	abstract function copyTable($source_table_name, $target_table_name);

	/**
	 * @param string $table_name
	 * @return array
	 */
	abstract function getTableColumnsNames($table_name);

	/**
	 * @param string $source_table
	 * @param string $target_table
	 * @param array $columns , [col1, col2, .. ] or [source_col1 => target_col1, ... ]
	 * @param string|DB_Query $where_query [optional]
	 * @param array $where_query_data [optional]
	 * @return int
	 */
	abstract function copyTableColumns($source_table, $target_table, array $columns, $where_query = null, array $where_query_data = array());

	/**
	 * @param null|string $table_name [optional]
	 *
	 * @return string|int
	 */
	abstract function getLastInsertID($table_name = null);


	/**
	 * @param DB_Query $query
	 * @return string
	 */
	function buildQuery(DB_Query $query){

		if($query->getCheckRelationsBeforeBuild()){
			$query->checkRelations();
		}

		$output = "SELECT\n";

		$select = $query->getSelect();
		if($select->isEmpty()){
			$output .= "\t*\n";
		} else {
			$output .= "\t" . str_replace("\n", "\n\t", $this->buildSelectExpression($select)) . "\n";
		}

		$output .= "FROM\n";
		$output .= "\t" . str_replace("\n", "\n\t", $this->buildFromExpression($query)) . "\n";

		$where = $query->getWhere();
		if(!$where->isEmpty()){
			$output .= "WHERE\n";
			$output .= "\t" . str_replace("\n", "\n\t", $this->buildWhereExpression($where)) . "\n";
		}

		$group_by = $query->getGroupBy();
		if(!$group_by->isEmpty()){
			$output .= "GROUP BY\n";
			$output .= "\t" . str_replace("\n", "\n\t", $this->buildGroupByExpression($group_by)) . "\n";
		}

		$having = $query->getHaving();
		if(!$having->isEmpty()){
			$output .= "HAVING\n";
			$output .= "\t" . str_replace("\n", "\n\t", $this->buildWhereExpression($having)) . "\n";
		}

		$order_by = $query->getOrderBy();
		if(!$order_by->isEmpty()){
			$output .= "ORDER BY\n";
			$output .= "\t" . str_replace("\n", "\n\t", $this->buildOrderByExpression($order_by)) . "\n";
		}

		if($query->getLimit() !== null){
			$output .= "LIMIT {$query->getLimit()}\n";
		}

		if($query->getOffset() !== null){
			$output .= "OFFSET {$query->getOffset()}\n";
		}

		if(count($query->getTablesInQuery()) == 1 && $query->getAllowSingleTableBuildSimplification()){
			$search_for = preg_quote($this->quoteTableName($query->getMainTableName()). ".");
			$output = preg_replace('~'.$search_for.'(\*|[^\s]?\w+[^\s]?)\b~sU', '\1', $output);
		}

		return rtrim($output);
	}

	/**
	 * @param DB_Table_Definition $table_definition
	 * @param bool $drop_if_exists [optional]
	 * @param null|string $rename_to_if_exists [optional]
	 * @throws DB_Exception
	 */
	function createTable(DB_Table_Definition $table_definition, $drop_if_exists = false, $rename_to_if_exists = null){
		$table_name = $table_definition->getTableName();
		if($this->getTableExists($table_name, true)){
			if($rename_to_if_exists){
				DB::checkTableName($rename_to_if_exists);
				if($this->getTableExists($rename_to_if_exists, true)){
					throw new DB_Exception(
						"Table '{$rename_to_if_exists}' already exists, cannot rename table '{$table_name}' to it",
						DB_Exception::CODE_INVALID_TABLE_NAME
					);
				}
				$this->renameTable($table_name, $rename_to_if_exists);
			} elseif($drop_if_exists){
				$this->dropTable($table_name);
			} else {
				return;
			}
		}

		$create_table_queries = $this->getCreateTableQuery($table_definition);
		if(!is_array($create_table_queries)){
			$create_table_queries = array($create_table_queries);
		}

		foreach($create_table_queries as $query){
			$this->exec($query);
		}

	}

	/**
	 * @param DB_Table_Definition $table_definition
	 * @return string|array
	 */
	abstract function getCreateTableQuery(DB_Table_Definition $table_definition);


	/**
	 * @param DB_Query_Select $select
	 * @return string
	 */
	protected function buildSelectExpression(DB_Query_Select $select){
		$expressions = $select->getExpressions();
		$output = array();
		foreach($expressions as $expression){


			if($expression instanceof DB_Query_Select_Column){
				// column select
				$expr = $this->quoteColumnName($expression->getColumnName(true));

			} elseif($expression instanceof DB_Query_Select_AllColumns){

				// table.* select
				if($expression->getTableName()){
					$expr = $this->quoteTableName($expression->getTableName()) . ".*";
				} else {
					$expr = "*";
				}

			} elseif($expression instanceof DB_Query_Select_Function){

				$expr = $this->buildFunctionExpression($expression);

			} elseif($expression instanceof DB_Query_Select_Expression){

				// expression select
				$expr = (string)$expression->getExpression();

			} elseif($expression instanceof DB_Query_Select_SubQuery){

				// subquery select
				$expr = "(\n\t" . str_replace("\n", "\n\t", $this->buildQuery($expression->getSubQuery())) . "\n)";

			} else {

				continue;

			}

			if($expression->getSelectAs()){
				$expr .= " AS {$this->quoteColumnName($expression->getSelectAs())}";
			}
			$output[] = $expr;
		}
		return implode(",\n", $output);
	}

	/**
	 * @param DB_Query $query
	 * @return string
	 */
	protected function buildFromExpression(DB_Query $query){
		$tables_in_query = $query->getTablesInQuery();
		$output = $this->quoteTableName($query->getMainTableName());
		if(count($tables_in_query) == 1){
			return $output;
		}
		
		$relations = $query->getRelations();
		if($relations->isEmpty()){
			$main_table = $query->getMainTableName();
			foreach($tables_in_query as $table){
				if($table == $main_table){
					continue;
				}
				$output .= ",\n{$this->quoteTableName($table)}";
			}
			return $output;
		}
		return $output . "\n{$this->buildRelationsExpression($relations)}";
	}

	/**
	 * @param DB_Query_Relations $relations
	 * @return string
	 */
	protected function buildRelationsExpression(DB_Query_Relations $relations){
		$output = "";
		$relations = $relations->getRelations();
		foreach($relations as $relation){
			if($relation instanceof DB_Query_Relations_SimpleRelation){

				$output .= "{$relation->getJoinType()} JOIN {$this->quoteTableName($relation->getRelatedTableName())} ON (\n";
				$joins = array();
				foreach($relation->join_on_columns as $col1 => $col2){
					$joins[] = "{$this->quoteColumnName($col1)} = {$this->quoteColumnName($col2)}";
				}
				$output .= "\t" . implode(" AND\n\t", $joins) . "\n";
				$output .= ")\n";

			} elseif($relation instanceof DB_Query_Relations_ComplexRelation){

				$output = "{$relation->getJoinType()} JOIN {$this->quoteTableName($relation->getRelatedTableName())} ON (\n";
				$output .= str_replace("\n", "\n\t", $this->buildWhereExpression($relation)) . "\n)\n";

			}
		}
		return rtrim($output);
	}

	/**
	 * @param DB_Query $query
	 * @return string
	 */
	function buildWhereQueryPart(DB_Query $query){
		$where = $query->getWhere();
		if($where->isEmpty()){
			return "";
		}

		return $this->buildWhereExpression($where);
	}

	/**
	 * @param DB_Query_Having $having
	 * @return string
	 */
	protected function buildHavingExpression(DB_Query_Having $having){
		return $this->buildWhereExpression($having);
	}

	/**
	 * @param DB_Query_Where $where
	 * @return string
	 */
	protected function buildWhereExpression(DB_Query_Where $where){
		$expressions = $where->getExpressions();
		$output = array();
		$last_idx = -1;

		foreach($expressions as $expression){
			// operators
			if(is_scalar($expression)){
				if($last_idx == -1){
					continue;
				}
				$output[$last_idx] .= " {$expression}";
				continue;
			}

			// nested query
			if($expression instanceof DB_Query_Where){
				if($expression->isEmpty()){
					continue;
				}
				$output[] = "(";
				$output[] = "\t" . str_replace("\n", "\n\t", $this->buildWhereExpression($expression));
				$output[] = ")";
				$last_idx = count($output) - 1;
				continue;
			}

			// compare expression
			$cmp_expr = $this->buildCompareExpression($expression);
			if($cmp_expr !== ""){
				$output[] = $cmp_expr;
				$last_idx++;
			}
		}

		return implode("\n", $output);
	}

	/**
	 * @param DB_Query_Where_ExpressionCompare|DB_Query_Where_ColumnCompare $expression
	 * @return string
	 */
	protected function buildCompareExpression($expression){

		if($expression instanceof DB_Query_Where_ExpressionCompare){
			if($expression->getCompareOperator() == null){
				return (string)$expression->getExpression();
			}

			$output = (string)$expression->getExpression() . " ";

		} elseif($expression instanceof DB_Query_Where_ColumnCompare){

			$output = $this->quoteColumnName($expression->getColumnName(true)) . " ";

		} else {

			return "";

		}

		$operator = $expression->getCompareOperator();
		$output .= $operator;

		if($expression->isNULLCompare()){
			return $output;
		}


		$value = $expression->getValue();
		if($value instanceof DB_Query){
			return $output . " (\n" . str_replace("\n", "\n\t", $this->buildQuery($value)) . "\n)";
		}

		if($value instanceof DB_Table_Column){
			return "{$output} " . $this->quoteColumnName($value->getColumnName(true));
		}

		if(!$expression->isNULLCompare()){
			return "{$output} {$this->quoteValue($value)}";
		}

		return "{$output} (" .$this->quoteIN($value) . ")";
	}

	/**
	 * @param DB_Query_GroupBy $group_by
	 * @return string
	 */
	protected function buildGroupByExpression(DB_Query_GroupBy $group_by){
		$columns = $columns = $group_by->getGroupByColumns();
		$output = array();

		foreach($columns as $column){
			$output[] = $this->quoteColumnName($column->getColumnName(true));
		}

		return implode(",\n", $output);
	}

	/**
	 * @param DB_Query_OrderBy $order_by
	 * @return string
	 */
	protected function buildOrderByExpression(DB_Query_OrderBy $order_by){
		$expressions = $order_by->getOrderByExpressions();
		$output = array();

		foreach($expressions as $expression){
			if($expression instanceof DB_Query_OrderBy_Column){

				$output[] = $this->quoteColumnName($expression->getColumnName(true)) . " {$expression->getOrderHow()}";

			} elseif($expression instanceof DB_Query_OrderBy_ColumnNumber){

				$output[] = $expression->getColumnNumber() . " {$expression->getOrderHow()}";

			} elseif($expression instanceof DB_Query_OrderBy_Expression){

				$output[] = (string)$expression->getExpression() . " {$expression->getOrderHow()}";

			}
		}

		return implode(",\n", $output);
	}

	/**
	 * @param DB_Query_Function $function
	 * @return string
	 */
	protected function buildFunctionExpression(DB_Query_Function $function){
		$output = "{$function->getFunctionName()}(";
		if(!$function->hasArguments()){
			return $output . ")";
		}
		//array|DB_Table_Column[]|DB_Expression[]|DB_Query[]
		$arguments = array_values($function->getArguments());
		foreach($arguments as $i => $arg){
			if($i){
				$output .= ", ";
			}
			// column
			if($arg instanceof DB_Table_Column){
				$output .= $this->quoteColumnName($arg->getColumnName(true));
				continue;
			}

			if($arg instanceof DB_Expression){
				$output .= (string)$arg;
				continue;
			}

			if($arg instanceof DB_Query){
				$output .= "\n\t" . str_replace("\n", "\n\t", $this->buildQuery($arg)) . "\n";
				continue;
			}

			$output .= $this->quoteValue($arg);
		}
		$output .= ")";
		return $output;
	}
}