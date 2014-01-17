<?php
namespace Et;
class DB_Table_Builder_MySQL extends DB_Table_Builder_Abstract {

	const DEFAULT_ENGINE = "InnoDB";
	const TABLE_OPTION_ENGINE = "mysql_engine";
	const COLUMN_OPTION_AUTO_INCREMENT = "auto_increment";

	/**
	 * @var DB_Adapter_MySQL
	 */
	protected $db_adapter;

	/**
	 * @param DB_Adapter_MySQL $db_adapter
	 */
	function __construct(DB_Adapter_MySQL $db_adapter){
		$this->db_adapter = $db_adapter;
	}

	/**
	 * @param DB_Table_Definition $table_definition
	 * @return array
	 */
	function getCreateTableQueries(DB_Table_Definition $table_definition) {
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

		// UNIQUE KEY
		$unique_keys = $table_definition->getUniqueKeys();
		foreach($unique_keys as $key_name => $key){
			$key_columns = $key->getColumnNames();
			$key_columns = implode(", ", $this->db_adapter->quoteIdentifiers($key_columns));
			$query .= ",\n\tUNIQUE KEY {$this->db_adapter->quoteIdentifier($key_name)} ({$key_columns})";
		}

		// KEY
		$indexes = $table_definition->getIndexes();
		foreach($indexes as $key_name => $key){
			$key_columns = $key->getColumnNames();
			$key_columns = implode(", ", $this->db_adapter->quoteIdentifiers($key_columns));
			$query .= ",\n\tKEY {$this->db_adapter->quoteIdentifier($key_name)} ({$key_columns})";
		}


		// FOOTER
		$query .= "\n) ENGINE={$table_definition->getBackendOption(static::TABLE_OPTION_ENGINE, static::DEFAULT_ENGINE)} DEFAULT CHARSET=utf8";
		if($table_definition->getComment()){
			$query .= " COMMENT {$this->db_adapter->quoteString($table_definition->getComment())}";
		}
		$query .= ";";

		return array($query);
	}

	/**
	 * @param DB_Table_Column_Definition $column_definition
	 * @param DB_Table_Definition $table_definition
	 * @return string
	 */
	protected function getColumnCreateTableQuery(DB_Table_Column_Definition $column_definition, DB_Table_Definition $table_definition){
		$default_value_supported = true;
		$query = "\n\t" . $this->db_adapter->quoteIdentifier($column_definition->getColumnName(false)) . " ";
		switch($column_definition->getType()){
			case DB_Table_Column_Definition::TYPE_BOOL:
				$query .= "TINYINT(1) UNSIGNED";
				break;

			case DB_Table_Column_Definition::TYPE_FLOAT:
				if($column_definition->getSize() > 16){
					$query .= "DOUBLE";
				} else {
					$query .= "FLOAT";
				}
				if($column_definition->isUnsigned()){
					$query .= " UNSIGNED";
				}
				break;

			case DB_Table_Column_Definition::TYPE_DATE:
				$query .= "DATE";
				break;

			case DB_Table_Column_Definition::TYPE_DATETIME:
				$query .= "DATETIME";
				break;

			case DB_Table_Column_Definition::TYPE_LOCALE:
				$query .= "CHAR(5) CHARACTER SET utf8 COLLATE utf8_bin";
				break;


			case DB_Table_Column_Definition::TYPE_BINARY_DATA:
			case DB_Table_Column_Definition::TYPE_STRING:
				$is_binary = $column_definition->isBinary();
				$size = $column_definition->getSize();
				$default_value_supported = false;

				if($is_binary){

					if(!$size){
						$query .= "LONGBLOB";
					} elseif($size < 256){
						$query .= "VARBINARY({$size})";
					} elseif($size < 65536){
						$query .= "BLOB";
					} elseif($size < pow(2, 24)){
						$query .= "MEDIUMBLOB";
					} else {
						$query .= "LONGBLOB";
					}


				} else {

					if(!$size){
						$size = 255;
					}

					$is_part_of_key = (bool)$table_definition->getKeysContainingColumn($column_definition->getColumnName(false));

					if($size < 256){
						$default_value_supported = true;
						$query .= "VARCHAR({$size})";
					} elseif($size < 65536){
						$query .= "TEXT";
					} elseif($size < pow(2, 24)){
						$query .= "MEDIUMTEXT";
					} else {
						$query .= "LONGTEXT";
					}

					if($is_part_of_key){
						$query .= " CHARACTER SET utf8 COLLATE utf8_bin";
					}
				}

				break;

			case DB_Table_Column_Definition::TYPE_INT:

				$size = $column_definition->getSize();
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

				if($column_definition->isUnsigned()){
					$query .= " UNSIGNED";
				}

				if($column_definition->getBackendOption(static::COLUMN_OPTION_AUTO_INCREMENT)){
					$query .= " auto_increment";
				}

				break;
		}

		if(!$column_definition->getAllowNull()){
			$query .= " NOT NULL";
		}

		if($default_value_supported){
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

				$query .= " DEFAULT {$default_value}";
			}
		}

		if($column_definition->getComment()){
			$query .= " COMMENT " . $this->db_adapter->quoteString($column_definition->getComment());
		}

		return $query . ",";
	}
}