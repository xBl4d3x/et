<?php
namespace Et;
/**
 * SQLite PDO database adapter
 */
class DB_Adapter_SQLite extends DB_Adapter_Abstract {

	/**
	 * @var array
	 */
	protected static $__quoted_tables_and_columns_cache = array();


	/**
	 * @var DB_Adapter_SQLite_Config
	 */
	protected $config;

	/**
	 * @var DB_Table_Builder_SQLite
	 */
	protected $table_builder;

	/**
	 * @param DB_Adapter_SQLite_Config $config
	 */
	function __construct(DB_Adapter_SQLite_Config $config){
		parent::__construct($config);
	}


	/**
	 * @return DB_Adapter_SQLite_Config
	 */
	function getConfig(){
		return parent::getConfig();
	}



	/**
	 * @param string $column_name
	 *
	 * @throws DB_Adapter_Exception
	 * @return string
	 */
	function quoteIdentifier($column_name){
		$column_name = (string)$column_name;
		if(isset(static::$__quoted_tables_and_columns_cache[$column_name])){
			return static::$__quoted_tables_and_columns_cache[$column_name];
		}
		$column_name = parent::quoteIdentifier($column_name);
		static::$__quoted_tables_and_columns_cache[$column_name] = "\"" . str_replace(".", "\".\"", $column_name) . "\"";
		return static::$__quoted_tables_and_columns_cache[$column_name];
	}

	/**
	 * @throws DB_Adapter_Exception
	 * @return array
	 */
	protected function _getTablesList() {
		$query = "SELECT "."name FROM sqlite_master WHERE type='table' UNION ALL SELECT name FROM sqlite_temp_master WHERE type='table' ORDER BY name";
		return $this->fetchColumn($query);
	}

	/**
	 * @param string $table_name
	 * @return int
	 * @throws DB_Adapter_Exception
	 *
	 */
	function truncateTable($table_name) {
		return $this->exec("DELETE FROM " . $this->quoteIdentifier($table_name));
	}

	/**
	 * @param string $source_table_name
	 * @param string $target_table_name
	 * @throws DB_Adapter_Exception
	 */
	function renameTable($source_table_name, $target_table_name) {
		$this->exec("ALTER "."TABLE {$this->quoteIdentifier($source_table_name)} RENAME TO {$this->quoteIdentifier($target_table_name)}");
	}

	/**
	 * @param string $source_table_name
	 * @param string $target_table_name
	 * @throws DB_Adapter_Exception
	 */
	function copyTable($source_table_name, $target_table_name) {
		$this->exec("CREATE "."TABLE {$this->quoteIdentifier($target_table_name)} LIKE {$this->quoteIdentifier($source_table_name)}");
		$this->exec("INSERT "."INTO {$this->quoteIdentifier($target_table_name)} SELECT * FROM {$this->quoteIdentifier($source_table_name)}");
	}

	/**
	 * @param string $table_name
	 * @return array
	 */
	function getTableColumnsNames($table_name) {
		$cols = $this->fetchRowsAssociative("PRAGMA table_info({$this->quoteIdentifier($table_name)});");
		return array_keys($cols);
	}

	/**
	 * @param string $source_table
	 * @param string $target_table
	 * @param array $columns
	 * @param string $where_query [optional]
	 * @param array $where_query_data [optional]
	 * @throws \Exception
	 * @return int
	 */
	function copyTableColumns($source_table, $target_table, array $columns, $where_query = "", array $where_query_data = array()) {
		$this->beginTransaction();

		$query = "INSERT INTO " . $this->quoteIdentifier($target_table) . "(";
		foreach($columns as $target_column_name){
			$query .= "\n\t{$this->quoteIdentifier($target_column_name)},";
		}
		$query = rtrim($query, ",");

		$query .= "\n) SELECT ";
		foreach($columns as $source_column_name => $target_column_name){
			if(is_numeric($source_column_name)){
				$source_column_name = $target_column_name;
			}
			$query .= "\n\t{$this->quoteIdentifier($source_column_name)},";
		}
		$query = rtrim($query, ",");
		$query .= "\nFROM\n\t" .  $this->quoteIdentifier($source_table);

		if(trim($where_query) !== ""){
			$query .= "\nWHERE\n\t{$where_query}";
		}

		try {
			$affected = $this->exec($query, $where_query_data);
			$this->commit();
			return $affected;
		} catch(\Exception $e){
			$this->rollback();
			throw $e;
		}

	}

	/**
	 * @throws DB_Exception
	 */
	public function getDatabaseType() {
		return self::DRIVER_SQLITE;
	}

	/**
	 * @return DB_Table_Builder_SQLite
	 */
	function getTableBuilder() {
		if(!$this->table_builder){
			$this->table_builder = new DB_Table_Builder_SQLite($this);
		}
		return $this->table_builder;
	}
}