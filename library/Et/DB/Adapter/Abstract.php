<?php
namespace Et;
abstract class DB_Adapter_Abstract extends \PDO {

	const DRIVER_MYSQL = "mysql";
	const DRIVER_SQLITE = "sqlite";
	const DRIVER_SPHINX = "sphinx";
	const DRIVER_CASSANDRA = "cassandra";

	/**
	 * @var DB_Adapter_Config_Abstract
	 */
	protected $config;

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
	 * @throws DB_Adapter_Exception
	 */
	function __construct(DB_Adapter_Config_Abstract $config){

		$this->config = $config;
		$driver_options = $this->getDefaultDriverOptions();

		try {

			parent::__construct(
				$this->config->getDSN(),
				$this->config->getUsername(),
				$this->config->getPassword(),
				$driver_options
			);


		} catch (\PDOException $e) {

			throw new DB_Adapter_Exception(
				"Failed to connect to database [DSN: {$this->config->getDSN()}] -  {$e->getMessage()}",
				DB_Adapter_Exception::CODE_CONNECTION_FAILED
			);

		}
	}

	/**
	 * @return string
	 */
	function getDriverName(){
		return strtolower($this->getAttribute(\PDO::ATTR_DRIVER_NAME));
	}

	/**
	 * @return array
	 */
	protected function getDefaultDriverOptions(){

		$driver_options = $this->config->getDriverOptions(true);

		if(!isset($driver_options[\PDO::ATTR_TIMEOUT])){
			$driver_options[\PDO::ATTR_TIMEOUT] = $this->config->getConnectionTimeout();
		}

		$driver_options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
		$driver_options[\PDO::ATTR_STRINGIFY_FETCHES] = false;
		if(!isset($driver_options[\PDO::ATTR_EMULATE_PREPARES])){
			$driver_options[\PDO::ATTR_EMULATE_PREPARES] = false;
		}

		return $driver_options;
	}


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


	/**
	 * @return DB_Adapter_Config_Abstract
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * @return string|bool
	 */
	function errorMessage(){
		
		$info = $this->errorInfo();
		if(!$info || !isset($info[2])){
			return false;
		}
		
		list($error_code, ,$error_message) = $info;
		return "[{$error_code}] {$error_message}";
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public function quoteString($string){
		return parent::quote((string)$string, self::PARAM_STR);
	}

	/**
	 * @param string $value
	 * @return string
	 */
	function quoteBinary($value){
		return parent::quote($value, self::PARAM_LOB);
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function quoteJSON($value){
		return $this->quoteString(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	}


	/**
	 * @param null|string|int|\DateTime|\Et\Locales_DateTime $date [optional]
	 * @param null|string|\DateTimeZone|\Et\Locales_Timezone $target_timezone [optional]
	 * @return string
	 */
	public function quoteDate($date, $target_timezone = null){
		if(!$date){
			return "''";
		}
		
		if(!$date instanceof \DateTime){
			$date = Locales::getDate($date);	
		}

		if($target_timezone){
			if(!$target_timezone instanceof \DateTimeZone){
				$target_timezone = Locales::getTimezone($target_timezone);	
			}
			$date->setTimezone($target_timezone);
		}

		return "'{$date->format("Y-m-d")}'";
	}

	/**
	 * @param null|string|int|\DateTime|\Et\Locales_DateTime $datetime [optional]
	 * @param null|string|\DateTimeZone|\Et\Locales_Timezone $target_timezone [optional]
	 * @return string
	 */
	public function quoteDateTime($datetime, $target_timezone = null){
		if(!$datetime){
			return "''";
		}

		if(!$datetime instanceof \DateTime){
			$datetime = Locales::getDateTime($datetime);
		}

		if($target_timezone){
			if(!$target_timezone instanceof \DateTimeZone){
				$target_timezone = Locales::getTimezone($target_timezone);
			}
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
	 * @param bool $return_string [optional]
	 * @return array|string
	 * @throws DB_Adapter_Exception
	 */
	function quoteValues($values, $return_string = false){
		if(!is_array($values) && !$values instanceof \Iterator){

			throw new DB_Adapter_Exception(
				"Only arrays or instances of Iterator may be quoted",
				DB_Adapter_Exception::CODE_QUOTE_FAILED
			);

		}

		$quoted = array();
		foreach($values as $key => $value){
			$quoted[$key] = $this->quote($value);
		}

		if($return_string){
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
	function quote($value){
		
		switch(true){
			// string
			case is_string($value):
				return parent::quote($value, self::PARAM_STR);

			// NULL
			case $value === null:
				return parent::quote($value, self::PARAM_NULL);

			// boolean
			case is_bool($value):
				return parent::quote($value, self::PARAM_BOOL);

			// numbers
			case is_numeric($value):
				if(is_int($value)){
					parent::quote((int)$value, self::PARAM_INT);
				}
				return (float)$value;

			// DB expression
			case $value instanceof DB_Expression:
				return (string)$value;

			// date and time
			case $value instanceof \DateTime:
				if($value instanceof Locales_Date){
					return $this->quoteDate($value);
				}
				return $this->quoteDateTime($value);

			// locale
			case $value instanceof Locales_Locale:
				return parent::quote((string)$value, self::PARAM_STR);

			// timezone
			case $value instanceof \DateTimeZone:
				return parent::quote($value->getName(), self::PARAM_STR);

			// table column
			case $value instanceof DB_Table_Column:
				return $this->quoteTableOrColumn((string)$value);

			// array
			case is_array($value):
				return $this->quoteJSON($value);
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
			$output[$this->quoteTableOrColumn($r)] = $this->quote($v);
		}
		return $output;
	}

	/**
	 * @param string $column_name
	 *
	 * @return string
	 */
	function quoteTableOrColumn($column_name){
		$column_name = (string)$column_name;
		Debug_Assert::isStringMatching($column_name, '^\w+(\.\w+)*$');
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
				$output[$k] = $this->quoteTableOrColumn($v);
			} else {
				$output[$this->quoteTableOrColumn($k)] = $this->quoteTableOrColumn($v);
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

				$replacements[":{$placeholder}"] = $this->quote($value);

			} catch(DB_Adapter_Exception $e){

				throw new DB_Adapter_Exception(
					"Failed to quote value with key '{$placeholder}' - {$e->getMessage()}",
					DB_Adapter_Exception::CODE_BINDING_FAILED,
					array("value" => $value),
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
	 * @param string|\Et\DB_Query $sql_query
	 * @param array $query_data [optional]
	 *
	 * @throws DB_Adapter_Exception
	 * @return int number of affected rows
	 */
	function exec($sql_query, array $query_data = array()){

		if($sql_query instanceof DB_Query){
			$sql_query = $this->buildQuery($sql_query);
		}

		if($query_data){
			$sql_query = $this->bindDataToQuery($sql_query, $query_data);
		}

		try {

			if($this->profiler){

				$query_period = $this->profiler->queryStarted($sql_query);
				$result = parent::exec($sql_query);
				$query_period->end();
				$query_period->setResultRowsCount($result);

			} else {

				$result = parent::exec($sql_query);

			}

			if($result === false){
				Debug::triggerErrorOrLastError("Query exec failed");
			}

			unset($sql_query);
			unset($query_data);

			return $result;

		} catch(\Exception $e){

			throw new DB_Adapter_Exception(
				"SQL query execution failed - {$e->getMessage()}\n\nSQL ERROR: {$this->errorMessage()}\n\nSQL QUERY:\n{$sql_query}",
				DB_Adapter_Exception::CODE_QUERY_FAILED,
				null,
				$e
			);

		}

	}

	/**
	 * @param string|\Et\DB_Query $sql_query
	 * @param array $query_data [optional]
	 *
	 * @throws DB_Adapter_Exception
	 * @return \PDOStatement
	 */
	function query($sql_query, array $query_data = array()){

		if($sql_query instanceof DB_Query){
			$sql_query = $this->buildQuery($sql_query);
		}

		if($query_data){
			$sql_query = $this->bindDataToQuery($sql_query, $query_data);
		}

		try {

			if($this->profiler){

				$query_period = $this->profiler->queryStarted($sql_query);
				$result = parent::query($sql_query);
				$query_period->end();
				$query_period->setResultRowsCount($result->rowCount());

			} else {

				$result = parent::query($sql_query);

			}

			if($result === false){
				Debug::triggerErrorOrLastError("Query failed");
			}

			unset($sql_query);
			unset($query_data);

			return $result;

		} catch(\Exception $e){

			throw new DB_Adapter_Exception(
				"SQL query execution failed - {$e->getMessage()}\n\nSQL ERROR: {$this->errorMessage()}\n\nSQL QUERY:\n{$sql_query}",
				DB_Adapter_Exception::CODE_QUERY_FAILED,
				null,
				$e
			);

		}

	}


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
	 * @return bool|DB_Profiler_Query
	 */
	protected function _profilerFetchStarted(){
		if(!$this->profiler){
			return false;
		}
		$query = $this->profiler->getLastQuery();
		$query->fetchStarted();
		return $query;
	}


	/**
	 * @param string|DB_Query $sql_query
	 * @param array $query_data [optional]
	 * @param null|string $fetch_type [optional] \PDO::FETCH_*
	 *
	 * @return array|bool
	 *
	 * @throws DB_Adapter_Exception
	 */
	function fetchRow($sql_query, array $query_data = array(), $fetch_type = null){
		$result = $this->query($sql_query, $query_data);
		$last_query = $this->_profilerFetchStarted();

		if(!$result->rowCount()){
			return false;
		}

		if(!$fetch_type){
			$fetch_type = self::FETCH_ASSOC;
		}

		$row = $result->fetch($fetch_type);
		if($last_query){
			$last_query->fetchEnded();
		}

		$result->closeCursor();
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
	 * @param string|DB_Query $sql_query
	 * @param array $query_data [optional]
	 * @param null|string|int $value_column [optional] Name of value column in row, if NULL, first column is used
	 * @throws DB_Adapter_Exception
	 *
	 * @return mixed|bool
	 */
	function fetchValue($sql_query, array $query_data = array(), $value_column = null){

		if($value_column === null){
			$value_column = 0;
		}

		$row = $this->fetchRow(
					$sql_query,
					$query_data,
					is_numeric($value_column)
					? self::FETCH_NUM
					: self::FETCH_ASSOC
		);

		if(!$row){
			return false;
		}

		if(!isset($row[$value_column]) && !array_key_exists($value_column, $row)){
			throw new DB_Adapter_Exception(
				"Column '{$value_column}' not found in result row",
				DB_Adapter_Exception::CODE_INVALID_COLUMN
			);
		}

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

		$result = $this->query($sql_query, $query_data);
		if(!$result->rowCount()){
			return array();
		}

		$last_query = $this->_profilerFetchStarted();
		if($value_column === null){
			$value_column = 0;
		}

		if(is_numeric($value_column)){

			$output = $result->fetchAll(self::FETCH_COLUMN, $value_column);

		} else {

			$output = array();
			$result->setFetchMode(self::FETCH_ASSOC);
			foreach($result as $i => $row){
				if(!$i){
					if(!isset($row[$value_column]) && !array_key_exists($value_column, $row)){
						throw new DB_Adapter_Exception(
							"Column '{$value_column}' not found in result",
							DB_Adapter_Exception::CODE_INVALID_COLUMN
						);
					}
				}
				$output[] = $row[$value_column];
			}
		}

		if($last_query){
			$last_query->fetchEnded();
		}

		$result->closeCursor();
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
		$result = $this->query($sql_query, $query_data);
		if(!$result->rowCount()){
			return array();
		}

		$last_query = $this->_profilerFetchStarted();
		if($key_column === null){
			$key_column = 0;
		}

		$output = array();
		$result->setFetchMode(self::FETCH_BOTH);

		foreach($result as $i => $row){

			if(!$i){
				if(!isset($row[$key_column]) && !array_key_exists($key_column, $row)){
					throw new DB_Adapter_Exception(
						"Column '{$key_column}' not found in result",
						DB_Adapter_Exception::CODE_INVALID_COLUMN
					);
				}

				if($value_column === null){
					$value_column = array_key_exists(1, $row)
									? 1
									: 0;
				}

				if(!isset($row[$value_column]) && !array_key_exists($value_column, $row)){
					throw new DB_Adapter_Exception(
						"Column '{$value_column}' not found in result",
						DB_Adapter_Exception::CODE_INVALID_COLUMN
					);
				}
			}

			$output[$row[$key_column]] = $row[$value_column];
		}

		if($last_query){
			$last_query->fetchEnded();
		}

		$result->closeCursor();
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
		$result = $this->query($sql_query, $query_data);
		if(!$result->rowCount()){
			return array();
		}

		$last_query = $this->_profilerFetchStarted();
		if(!$fetch_type){
			$fetch_type = self::FETCH_ASSOC;
		}

		if($key_column === null || is_numeric($key_column)){
			$output = $result->fetchAll($fetch_type | self::FETCH_COLUMN, (int)$key_column);
		} else {

			$output = array();
			$result->setFetchMode($fetch_type);

			foreach($result as $i => $row){

				if(!$i){

					if($key_column === null){
						list($key_column) = array_keys($row);
					}

					if(!isset($row[$key_column]) && !array_key_exists($key_column, $row)){
						throw new DB_Adapter_Exception(
							"Column '{$key_column}' not found in result",
							DB_Adapter_Exception::CODE_INVALID_COLUMN
						);
					}
				}

				$output[$row[$key_column]] = $row;
			}
		}

		if($last_query){
			$last_query->fetchEnded();
		}

		$result->closeCursor();
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

		$result = $this->query($sql_query, $query_data);
		if(!$result->rowCount()){
			return array();
		}

		$last_query = $this->_profilerFetchStarted();
		if(!$fetch_type){
			$fetch_type = self::FETCH_ASSOC;
		}

		$output = $result->fetchAll($fetch_type);

		if($last_query){
			$last_query->fetchEnded();
		}

		$result->closeCursor();
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
			$query_columns[$this->quoteTableOrColumn($r)] = $this->quote($v);
		}

		$query = ($replace ? "REPLACE" : "INSERT") . " INTO " . $this->quoteTableOrColumn($table_name) . "(\n    ";
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
				$row[$i] = $this->quote($value);
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
			$columns[$i] = $this->quoteTableOrColumn($column);
		}

		$query_start = ($replace ? "REPLACE" : "INSERT")." INTO " . $this->quoteTableOrColumn($table_name) . "(\n    ";
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
			$query_columns[] = $this->quoteTableOrColumn($r) . " = :{$r}";
		}

		$query = "UPDATE " . $this->quoteTableOrColumn($table_name) . "SET\n    ";
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

		$query = "DELETE FROM\n    " . $this->quoteTableOrColumn($table_name) . "\nWHERE\n    {$where_query}";
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
		return $this->exec("DROP TABLE " . $this->quoteTableOrColumn($table_name));
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

		if($query->getLimit() > 0){
			$output .= "LIMIT {$query->getLimit()}\n";
		}

		if($query->getOffset() > 0){
			$output .= "OFFSET {$query->getOffset()}\n";
		}

		if(count($query->getTablesInQuery()) == 1 && $query->getAllowSingleTableBuildSimplification()){
			$search_for = preg_quote($this->quoteTableOrColumn($query->getMainTableName()). ".");
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
				$expr = $this->quoteTableOrColumn($expression->getColumnName(true));

			} elseif($expression instanceof DB_Query_Select_AllColumns){

				// table.* select
				if($expression->getTableName()){
					$expr = $this->quoteTableOrColumn($expression->getTableName()) . ".*";
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
				$expr .= " AS {$this->quoteTableOrColumn($expression->getSelectAs())}";
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
		$output = $this->quoteTableOrColumn($query->getMainTableName());
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
				$output .= ",\n{$this->quoteTableOrColumn($table)}";
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

				$output .= "{$relation->getJoinType()} JOIN {$this->quoteTableOrColumn($relation->getRelatedTableName())} ON (\n";
				$joins = array();
				foreach($relation->getJoinOnColumns() as $col1 => $col2){
					$joins[] = "{$this->quoteTableOrColumn($col1)} = {$this->quoteTableOrColumn($col2)}";
				}
				$output .= "\t" . implode(" AND\n\t", $joins) . "\n";
				$output .= ")\n";

			} elseif($relation instanceof DB_Query_Relations_ComplexRelation){

				$output = "{$relation->getJoinType()} JOIN {$this->quoteTableOrColumn($relation->getRelatedTableName())} ON (\n";
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

			$output = $this->quoteTableOrColumn($expression->getColumnName(true)) . " ";

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
			return "{$output} " . $this->quoteTableOrColumn($value->getColumnName(true));
		}

		if(!$expression->isNULLCompare()){
			return "{$output} {$this->quote($value)}";
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
			$output[] = $this->quoteTableOrColumn($column->getColumnName(true));
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

				$output[] = $this->quoteTableOrColumn($expression->getColumnName(true)) . " {$expression->getOrderHow()}";

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
				$output .= $this->quoteTableOrColumn($arg->getColumnName(true));
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

			$output .= $this->quote($arg);
		}
		$output .= ")";
		return $output;
	}
}