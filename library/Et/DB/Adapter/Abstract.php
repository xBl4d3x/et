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
	 * @var array
	 */
	protected $tables_list;

	/**
	 * @var DB_Profiler
	 */
	protected $profiler;

	/**
	 * @var DB_Query_Builder_Abstract
	 */
	protected $query_builder;

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
	 * @return DB_Query_Builder_Abstract
	 */
	function getQueryBuilder(){
		if(!$this->query_builder){
			$this->query_builder = new DB_Query_Builder_DB($this);
		}
		return $this->query_builder;
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
	 * @param DB_Profiler $profiler [optional]
	 * @return DB_Profiler
	 */
	function enableProfiler(DB_Profiler $profiler = null){
		if($profiler){
			$this->profiler = $profiler;
		} elseif(!$this->profiler){
			$this->profiler = new DB_Profiler($this->getConfig()->getDSN());
		}
		$this->profiler->setEnabled(true);
		return $this->profiler;
	}

	function disableProfiler(){
		if($this->profiler){
			$this->profiler->setEnabled(false);
		}
	}

	/**
	 * @return bool
	 */
	function isProfilerEnabled(){
		return $this->profiler && $this->profiler->isEnabled();
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
	function getLastErrorMessage(){
		
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
		return parent::quote(
					json_encode(
						$value,
						JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
					),
					self::PARAM_STR
				);
	}


	/**
	 * @param null|string|int|\DateTime|\Et\Locales_DateTime $date [optional]
	 * @return string
	 */
	public function quoteDate($date){
		if(!$date){
			return "''";
		}
		
		if(!$date instanceof \DateTime){
			$date = Locales::getDate($date);	
		}

		return "'{$date->format("Y-m-d")}'";
	}

	/**
	 * @param null|string|int|\DateTime|\Et\Locales_DateTime $datetime [optional]
	 * @return string
	 */
	public function quoteDateTime($datetime){
		if(!$datetime){
			return "''";
		}

		if(!$datetime instanceof \DateTime){
			$datetime = Locales::getDateTime($datetime);
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
	 * @return array
	 * @throws DB_Adapter_Exception
	 */
	function quoteValues($values){
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


			// numbers
			case is_numeric($value):
				if(is_int($value)){
					parent::quote((int)$value, self::PARAM_INT);
				}
				return (float)$value;

			// boolean
			case is_bool($value):
				return parent::quote($value, self::PARAM_BOOL);

			// NULL
			case $value === null:
				return parent::quote($value, self::PARAM_NULL);

			// DB expression
			case $value instanceof DB_Expression:
				return (string)$value;

			// table column
			case $value instanceof DB_Table_Column:
				return $this->quoteIdentifier((string)$value);

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
			$output[$this->quoteIdentifier($r)] = $this->quote($v);
		}
		return $output;
	}

	/**
	 * Quote table or column name
	 *
	 * @param string $column_name
	 *
	 * @return string
	 */
	function quoteIdentifier($column_name){
		$column_name = (string)$column_name;
		Debug_Assert::isStringMatching($column_name, '^\w+(\.\w+)*$');
		return $column_name;
	}

	/**
	 * @param array $columns_names
	 * @return array
	 */
	function quoteIdentifiers(array $columns_names){
		$output = array();
		foreach($columns_names as $k => $v){
			$output[$k] = $this->quoteIdentifier($v);
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
	 * @param array $query_data
	 * @param bool $exec
	 * @return int|\PDOStatement
	 * @throws DB_Adapter_Exception
	 */
	protected function _execOrQuery(&$sql_query, array &$query_data, $exec){
		if($sql_query instanceof DB_Query){
			$sql_query = $this->buildQuery($sql_query);
		}

		if($query_data){
			$sql_query = $this->bindDataToQuery($sql_query, $query_data);
		}

		try {

			if($this->isProfilerEnabled()){

				$query_period = $this->profiler->queryStarted($sql_query);
				if($exec){

					$result = parent::exec($sql_query);
					$query_period->end();
					$query_period->setRowsCount($result);

				} else {

					$result = parent::query($sql_query);
					$query_period->end();

				}


			} else {

				if($exec){
					$result = parent::exec($sql_query);
				} else {
					$result = parent::query($sql_query);
				}


			}

			if($result === false){
				Debug::triggerErrorOrLastError("Query failed");
			}

			unset($sql_query);
			unset($query_data);

			return $result;

		} catch(\Exception $e){

			throw new DB_Adapter_Exception(
				"SQL query execution failed - {$e->getMessage()}\n\nSQL ERROR: {$this->getLastErrorMessage()}\n\nSQL QUERY:\n{$sql_query}",
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
	 * @return int number of affected rows
	 */
	function exec($sql_query, array $query_data = array()){
		return $this->_execOrQuery($sql_query, $query_data, true);
	}

	/**
	 * @param string|\Et\DB_Query $sql_query
	 * @param array $query_data [optional]
	 *
	 * @throws DB_Adapter_Exception
	 * @return \PDOStatement
	 */
	function query($sql_query, array $query_data = array()){
		return $this->_execOrQuery($sql_query, $query_data, false);
	}


	/**
	 * @param bool $refresh_tables_list [optional]
	 *
	 * @return array
	 */
	function getTablesList($refresh_tables_list = false){
		if(!$refresh_tables_list && is_array($this->tables_list)){
			return $this->tables_list;
		}
		$this->tables_list = $this->_getTablesList();
		return $this->tables_list;
	}

	/**
	 * @return array
	 */
	abstract protected function _getTablesList();

	/**
	 * @return static|\ET\DB_Adapter_Abstract
	 */
	function refreshTablesList(){
		$this->getTablesList(true);
		return $this;
	}

	/**
	 * @param string $table_name
	 *
	 * @param bool $refresh_tables_list [optional]
	 *
	 * @return bool
	 */
	function getTableExists($table_name, $refresh_tables_list = false){
		return in_array($table_name, $this->getTablesList($refresh_tables_list));
	}

	/**
	 * @return bool
	 */
	protected function _profilerFetchStarted(){
		if(!$this->isProfilerEnabled()){
			return false;
		}
		$query = $this->profiler->getLastQuery();
		$query->fetchStarted();
		return true;
	}

	/**
	 * @param int $rows_count
	 * @return bool
	 */
	protected function _profilerFetchEnded($rows_count){
		if(!$this->isProfilerEnabled()){
			return false;
		}
		$query = $this->profiler->getLastQuery();
		$query->fetchEnded();
		$query->setRowsCount((int)$rows_count);
		return true;
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
		$this->_profilerFetchStarted();

		if(!$fetch_type){
			$fetch_type = self::FETCH_ASSOC;
		}

		$row = $result->fetch($fetch_type);
		$result->closeCursor();

		$this->_profilerFetchEnded($row ? 1 : 0);


		return $row;
	}

	/**
	 * @param string|DB_Query $sql_query
	 * @param array $query_data [optional]
	 * @throws DB_Adapter_Exception
	 *
	 * @return mixed|bool
	 */
	function fetchValue($sql_query, array $query_data = array()){

		$result = $this->query($sql_query, $query_data);
		$this->_profilerFetchStarted();

		$column = $result->fetch(self::FETCH_COLUMN);
		$result->closeCursor();

		$this->_profilerFetchEnded($column !== false ? 1 : 0);

		return $column;
	}

	/**
	 * @param string|DB_Query $sql_query
	 * @param array $query_data [optional]
	 *
	 * @throws DB_Adapter_Exception
	 *
	 * @return array
	 */
	function fetchColumn($sql_query, array $query_data = array()){

		$result = $this->query($sql_query, $query_data);
		$this->_profilerFetchStarted();

		$output = array();
		$result->setFetchMode(self::FETCH_COLUMN, 1);
		foreach($result as $col){
			$output[] = $col;
		}

		$result->closeCursor();
		$this->_profilerFetchEnded(count($output));
		return $output;
	}

	/**
	 * @param string|DB_Query $sql_query
	 * @param array $query_data [optional]
	 *
	 * @throws DB_Adapter_Exception
	 *
	 * @return array
	 */
	function fetchPairs($sql_query, array $query_data = array()){

		$result = $this->query($sql_query, $query_data);
		$this->_profilerFetchStarted();

		$output = array();
		$result->setFetchMode(self::FETCH_NUM);

		$value_column = null;

		foreach($result as $row){

			if($value_column === null){
				$value_column = (int)array_key_exists(1, $row);
			}

			$output[$row[0]] = $row[$value_column];
		}

		$result->closeCursor();
		$this->_profilerFetchEnded(count($output));

		return $output;
	}

	/**
	 * @param string|DB_Query $sql_query
	 * @param array $query_data [optional]
	 * @param null|string $fetch_type [optional] One of DB::FETCH_ASSOCIATIVE|FETCH_VALUES, if NULL, FETCH_ASSOCIATIVE is used
	 *
	 * @throws DB_Adapter_Exception
	 *
	 * @return array
	 */
	function fetchRowsAssociative($sql_query, array $query_data = array(), $fetch_type = null){
		$result = $this->query($sql_query, $query_data);
		$this->_profilerFetchStarted();

		if(!$fetch_type){
			$fetch_type = self::FETCH_ASSOC;
		}

		$result->setFetchMode($fetch_type);
		$output = array();
		$key = null;
		foreach($result as $row){
			if($key === null){
				reset($row);
				$key = key($row);
			}
			$output[$row[$key]] = $row;
		}
		$result->closeCursor();

		$this->_profilerFetchEnded(count($output));

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
		$this->_profilerFetchStarted();

		if(!$fetch_type){
			$fetch_type = self::FETCH_ASSOC;
		}

		$output = $result->fetchAll($fetch_type);
		$result->closeCursor();

		$this->_profilerFetchEnded(count($output));

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
			$query_columns[$this->quoteIdentifier($r)] = $this->quote($v);
		}

		$query = ($replace ? "REPLACE" : "INSERT") . " INTO " . $this->quoteIdentifier($table_name) . "(\n    ";
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
			$max_rows_per_query = max(1, (int)$max_rows_per_query);
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
			$columns[$i] = $this->quoteIdentifier($column);
		}

		$query_start = ($replace ? "REPLACE" : "INSERT")." INTO " . $this->quoteIdentifier($table_name) . "(\n    ";
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
			$this->commit();

		} catch(\Exception $e){

			$this->rollBack();
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
			$where_query = $this->getQueryBuilder()->buildCompareExpression($where_query->getWhere());
		}

		$where_query = trim($where_query);
		if($where_query_data){
			$where_query = $this->bindDataToQuery($where_query, $where_query_data);
		}

		$query_columns = array();
		foreach($new_row_data as $r => $v){
			$query_columns[] = $this->quoteIdentifier($r) . " = :{$r}";
		}

		$query = "UPDATE " . $this->quoteIdentifier($table_name) . "SET\n    ";
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
			$where_query = $this->getQueryBuilder()->buildCompareExpression($where_query->getWhere());
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

		$query = "DELETE FROM\n    " . $this->quoteIdentifier($table_name) . "\nWHERE\n    {$where_query}";
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
	 * @throws DB_Adapter_Exception
	 * @return int
	 */
	public function fetchRowsCount($sql_query, array $query_data = array()){
		if($sql_query instanceof DB_Table_Key){
			$sql_query = $sql_query->getWhereQuery();
		}

		if($sql_query instanceof DB_Query){
			$cloned_query = $sql_query->cloneInstance(true)->selectCount();
			$cloned_query->limit(0, 0);
			$cloned_query->orderBy(array());
			return (int)$this->fetchValue($cloned_query);
		}

		$sql_query = ltrim($sql_query);
		if(!preg_match("~^SELECT~i", $sql_query)){
			throw new DB_Adapter_Exception(
				"Invalid SQL query - expected format like: SELECT col1, col2 .. , colN FROM table_name ...",
				DB_Adapter_Exception::CODE_INVALID_ARGUMENT
			);
		}

		$sql_query = preg_replace('~^SELECT\b(.+?)\sFROM\b~is', "SELECT\n    COUNT(*)\nFROM", ltrim($sql_query));
		$sql_query = preg_replace('~LIMIT\b\s+\d+(?:\s*,\s*\d+)?|OFFSET\b\s+\d+~is', "", $sql_query);

		return $this->fetchValue($sql_query, $query_data);
	}

	/**
	 * @param string|DB_Query|DB_Table_Key $sql_query
	 * @param array $query_data [optional]
	 * @return bool
	 */
	function fetchRowExists($sql_query, array $query_data = array()){
		return $this->fetchValue($sql_query, $query_data) !== false;
	}

	/**
	 * @param string $table_name
	 *
	 * @return int
	 */
	function dropTable($table_name){
		return $this->exec("DROP TABLE " . $this->quoteIdentifier($table_name));
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

				$this->beginTransaction();
				$this->renameTable($table_name, $rename_to_if_exists);

			} elseif($drop_if_exists){

				$this->beginTransaction();
				$this->dropTable($table_name);

			} else {
				return;
			}
		} else {
			$this->beginTransaction();
		}

		try {

			$create_table_queries = $this->getCreateTableQueries($table_definition);
			foreach($create_table_queries as $query){
				$this->exec($query);
			}
			$this->commit();

		} catch(\Exception $e){

			$this->rollBack();

			throw new DB_Exception(
				"Failed to create table - {$e->getMessage()}",
				DB_Exception::CODE_TABLE_CREATION_FAILED,
				null,
				$e
			);
		}


	}

	/**
	 * @return DB_Table_Builder_Abstract
	 */
	abstract function getTableBuilder();

	/**
	 * @param DB_Table_Definition $table_definition
	 * @return string|array
	 */
	function getCreateTableQueries(DB_Table_Definition $table_definition){
		return $this->getTableBuilder()->getCreateTableQueries($table_definition);
	}

	/**
	 * @param DB_Query $query
	 * @return string
	 */
	function buildQuery(DB_Query $query){
		return $this->getQueryBuilder()->buildQuery($query);
	}
}