<?php
namespace Et;
class DB_Table_Builder_SQLite extends DB_Table_Builder_Abstract {

	const COLUMN_OPTION_AUTO_INCREMENT = "auto_increment";

	/**
	 * @var DB_Adapter_SQLite
	 */
	protected $db_adapter;

	/**
	 * @param DB_Adapter_SQLite $db_adapter
	 */
	function __construct(DB_Adapter_SQLite $db_adapter){
		$this->db_adapter = $db_adapter;
	}

	/**
	 * @param DB_Table_Definition $table_definition
	 * @return array
	 */
	function getCreateTableQueries(DB_Table_Definition $table_definition) {

		$queries = array();

		$table_name = $this->db_adapter->quoteIdentifier($table_definition->getTableName());
		// HEADER
		$query = "CREATE TABLE "."IF NOT EXISTS {$table_name}(";

		// COLUMNS
		$columns = $table_definition->getColumns();
		foreach($columns as $column_definition){
			$query .= $this->getColumnCreateTableQuery($column_definition, $table_definition);
		}
		$query = rtrim($query, ",");

		// PRIMARY KEY
		$pk_columns = $table_definition->getPrimaryKeyColumnNames();
		$pk_columns = implode(", ", $this->db_adapter->quoteIdentifiers($pk_columns));
		$query .= ",\n\tPRIMARY KEY ({$pk_columns})";


		$query .= ";";
		$queries[] = $query;

		// UNIQUE KEY
		$unique_keys = $table_definition->getUniqueKeys();
		foreach($unique_keys as $key_name => $key){
			$key_columns = $key->getColumnNames();
			$key_columns = implode(", ", $this->db_adapter->quoteIdentifiers($key_columns));
			$queries[] = "CREATE UNIQUE INDEX {$this->db_adapter->quoteIdentifier($key_name)} IF NOT EXISTS ON {$table_name} ({$key_columns})";
		}

		// KEY
		$indexes = $table_definition->getIndexes();
		foreach($indexes as $key_name => $key){
			$key_columns = $key->getColumnNames();
			$key_columns = implode(", ", $this->db_adapter->quoteIdentifiers($key_columns));
			$queries[] = "CREATE "."INDEX {$this->db_adapter->quoteIdentifier($key_name)} IF NOT EXISTS ON {$table_name} ({$key_columns})";
		}


		return $queries;
	}

	/**
	 * @param DB_Table_Column_Definition $column_definition
	 * @param DB_Table_Definition $table_definition
	 * @return string
	 */
	protected function getColumnCreateTableQuery(DB_Table_Column_Definition $column_definition, DB_Table_Definition $table_definition){

		$query = "\n\t" . $this->db_adapter->quoteIdentifier($column_definition->getColumnName(false)) . " ";
		switch($column_definition->getType()) {

			case DB_Table_Column_Definition::TYPE_FLOAT:
				$query .= "REAL";
				break;

			case DB_Table_Column_Definition::TYPE_DATE:
			case DB_Table_Column_Definition::TYPE_DATETIME:
			case DB_Table_Column_Definition::TYPE_LOCALE:
			case DB_Table_Column_Definition::TYPE_BINARY_DATA:
			case DB_Table_Column_Definition::TYPE_STRING:
				$query .= "TEXT";

				break;

			case DB_Table_Column_Definition::TYPE_BOOL:
			case DB_Table_Column_Definition::TYPE_INT:

				$query .= "INTEGER";

				break;
		}

		if(!$column_definition->getAllowNull()){
			$query .= " NOT NULL";
		}

		$default_value = $column_definition->getDefaultValue();
		if($default_value === null && $column_definition->getAllowNull()){
			$query .= " DEFAULT NULL";
		} else {

			if(!is_string($default_value)){
				$default_value = $this->db_adapter->quote($default_value);
			}

			if(isset($default_value[0]) && $default_value[0] != "'"){
				$default_value = $this->db_adapter->quoteString($default_value);
			}

			$query .= " DEFAULT ({$default_value})";
		}

		return $query . ",";
	}
}