<?php
namespace Et;
abstract class DB_Table_Builder_Abstract extends Object {

	/**
	 * @param DB_Table_Definition $table_definition
	 * @return array
	 */
	abstract function getCreateTableQueries(DB_Table_Definition $table_definition);


}