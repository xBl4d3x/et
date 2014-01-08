<?php
namespace Et;
/**
 * MySQL PDO database adapter
 */
class DB_Adapter_MySQL extends DB_Adapter_Abstract {

	/**
	 * @var DB_Adapter_MySQL_Config
	 */
	protected $config;

	/**
	 * @param DB_Adapter_MySQL_Config $config
	 */
	function __construct(DB_Adapter_MySQL_Config $config){
		parent::__construct($config);
	}


	/**
	 * @return DB_Adapter_MySQL_Config
	 */
	function getConfig(){
		return parent::getConfig();
	}

	/**
	 * @return array
	 */
	protected function getDefaultDriverOptions(){

		$driver_options = parent::getDefaultDriverOptions();
		$driver_options[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES '{$this->config->getCharset()}'";

		return $driver_options;
	}

	/**
	 * @param string $column_name
	 *
	 * @throws DB_Adapter_Exception
	 * @return string
	 */
	function quoteTableOrColumn($column_name){
		$column_name = parent::quoteTableOrColumn($column_name);
		return "`" . str_replace(".", "`.`", $column_name) . "`";
	}

	/**
	 * @throws DB_Adapter_Exception
	 * @return array
	 */
	protected function _listTables() {
		return $this->fetchColumn("SHOW TABLES");
	}

	/**
	 * @param string $table_name
	 * @return int
	 * @throws DB_Adapter_Exception
	 *
	 */
	function truncateTable($table_name) {
		return $this->exec("TRUNCATE TABLE " . $this->quoteTableOrColumn($table_name));
	}

	/**
	 * @param string $source_table_name
	 * @param string $target_table_name
	 * @throws DB_Adapter_Exception
	 */
	function renameTable($source_table_name, $target_table_name) {
		$this->exec("RENAME TABLE {$this->quoteTableOrColumn($source_table_name)} TO {$this->quoteTableOrColumn($target_table_name)}");
	}

	/**
	 * @param string $source_table_name
	 * @param string $target_table_name
	 * @throws DB_Adapter_Exception
	 */
	function copyTable($source_table_name, $target_table_name) {
		$this->exec("CREATE TABLE {$this->quoteTableOrColumn($target_table_name)} LIKE {$this->quoteTableOrColumn($source_table_name)}");
		$this->exec("INSERT INTO {$this->quoteTableOrColumn($target_table_name)} SELECT * FROM {$this->quoteTableOrColumn($source_table_name)}");
	}

	/**
	 * @param string $table_name
	 * @return array
	 */
	function getTableColumnsNames($table_name) {
		$cols = $this->fetchRowsAssociative("DESCRIBE {$this->quoteTableOrColumn($table_name)}");
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

		$query = "INSERT INTO " . $this->quoteTableOrColumn($target_table) . "(";
		foreach($columns as $target_column_name){
			$query .= "\n\t{$this->quoteTableOrColumn($target_column_name)},";
		}
		$query = rtrim($query, ",");

		$query .= "\n) SELECT ";
		foreach($columns as $source_column_name => $target_column_name){
			if(is_numeric($source_column_name)){
				$source_column_name = $target_column_name;
			}
			$query .= "\n\t{$this->quoteTableOrColumn($source_column_name)},";
		}
		$query = rtrim($query, ",");
		$query .= "\nFROM\n\t" .  $this->quoteTableOrColumn($source_table);

		if(trim($where_query) !== ""){
			$query .= "\nWHERE\n\t{$where_query}";
		}

		try {
			$affected = $this->exec($query, $where_query_data);
			$this->commit();
			return $affected;
		} catch(\Exception $e){
			$this->rollBack();
			throw $e;
		}

	}

	/**
	 * @param DB_Table_Column_Definition $definition
	 * @return string
	 */
	protected function getColumnCreateTableQuery($definition){
		$default_value_supported = true;
		$query = "\n\t" . $this->quoteTableOrColumn($definition->getColumnName(false)) . " ";
		switch($definition->getColumnType()){
			case DB_Table_Column_Definition::TYPE_BOOL:
				$query .= "TINYINT(1) UNSIGNED";
				break;

			case DB_Table_Column_Definition::TYPE_FLOAT:
				if($definition->getColumnSize() > 16){
					$query .= "DOUBLE";
				} else {
					$query .= "FLOAT";
				}
				if($definition->isUnsigned()){
					$query .= " UNSIGNED";
				}
				break;

			case DB_Table_Column_Definition::TYPE_DATE:
			case DB_Table_Column_Definition::TYPE_DATETIME:
				$query .= "INT(11) UNSIGNED";
				break;

			case DB_Table_Column_Definition::TYPE_LOCALE:
				$query .= "CHAR(5) CHARACTER SET utf8 COLLATE utf8_bin";
				break;


			case DB_Table_Column_Definition::TYPE_BINARY_DATA:
			case DB_Table_Column_Definition::TYPE_SERIALIZED:
			case DB_Table_Column_Definition::TYPE_STRING:
				$use_blob = $definition->issBinaryDataColumn();
				if($definition->getColumnType() == DB_Table_Column_Definition::TYPE_SERIALIZED){
					if($definition->getColumnSize() > 0){
						$size = $definition->getColumnSize();
					} else {
						$size = 2000000000;
					}
					$binary = true;
				} else {
					$size = $definition->getColumnSize();
					$binary = $definition->isIndex();
				}

				$default_value_supported = false;
				if($size < 256){
					$default_value_supported = !$use_blob;
					$query .= $use_blob ? "VARBINARY({$size})" : "VARCHAR({$size})";
				} elseif($size < 65536){
					$query .= $use_blob ? "BLOB" : "TEXT";
				} elseif($size < pow(2, 24)){
					$query .= $use_blob ? "MEDIUMBLOB" : "MEDIUMTEXT";
				} else {
					$query .= $use_blob ? "LONGBLOB" : "LONGTEXT";
				}

				if($binary && !$use_blob){
					$query .= " CHARACTER SET utf8 COLLATE utf8_bin";
				}
				break;

			case DB_Table_Column_Definition::TYPE_INT:

				$size = $definition->getColumnSize();
				if($size <= 4){
					$query .= "TINYINT({$size})";
				} elseif($size <= 6){
					$query .= "SMALLINT({$size})";
				} elseif($size <= 8){
					$query .= "MEDIUMINT({$size})";
				} elseif($size <= 11){
					$query .= "INT({$size})";
				} else {
					$query .= "BIGINT({$size})";
				}

				if($definition->isUnsigned()){
					$query .= " UNSIGNED";
				}

				break;
		}

		if(!$definition->getAllowNull()){
			$query .= " NOT NULL";
		}

		if($default_value_supported){
			$default_value = $definition->getDefaultValueForDB();
			if($default_value === null && $definition->getAllowNull()){
				$query .= " DEFAULT NULL";
			} else {
				$query .= " DEFAULT ". $this->quoteString($default_value);
			}
		}

		if($definition->getColumnComment()){
			$query .= " COMMENT " . $this->quoteString($definition->getColumnComment());
		}

		return $query . ",";
	}

	/**
	 * @param DB_Table_Definition $table_definition
	 * @return string
	 */
	function getCreateTableQuery(DB_Table_Definition $table_definition) {
		$table_name = $this->quoteTableOrColumn($table_definition->getTableName());
		// HEADER
		$query = "CREATE TABLE IF NOT EXISTS {$table_name}(";

		// COLUMNS
		$columns = $table_definition->getColumnsDefinitions();
		foreach($columns as $column_definition){
			$query .= $this->getColumnCreateTableQuery($column_definition);
		}
		$query = rtrim($query, ",");

		// PRIMARY KEY
		$primary_key_columns = $table_definition->getPrimaryKeyColumns();
		if($primary_key_columns){
			foreach($primary_key_columns as &$col){
				$col = $this->quoteTableOrColumn($col);
			}
			$query .= ",\n\tPRIMARY KEY (" . implode(", ", $primary_key_columns) . ")";
		}

		// UNIQUE KEY
		$unique_key_columns = $table_definition->getUniqueKeyColumns();
		if($unique_key_columns){
			foreach($unique_key_columns as &$col){
				$col = $this->quoteTableOrColumn($col);
			}
			$query .= ",\n\tUNIQUE KEY (" . implode(", ", $unique_key_columns) . ")";
		}

		// INDEXES
		$indexes_columns = $table_definition->getIndexesColumns();

		foreach($indexes_columns as $index_name => $index_columns){
			foreach($index_columns as &$col){
				$col = $this->quoteTableOrColumn($col);
			}
			$index_name = $this->quoteTableOrColumn($index_name);
			$query .= ",\n\tKEY {$index_name} (" . implode(", ", $index_columns) . ")";
		}

		// FOOTER
		$query .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8";
		if($table_definition->getTableComment()){
			$query .= " COMMENT {$this->quote($table_definition->getTableComment())}";
		}
		$query .= ";";
		return $query;
	}
}