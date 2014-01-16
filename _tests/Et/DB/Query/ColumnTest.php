<?php
namespace EtTest\Et;
use EtTest;
use Et;


class DB_Query_ColumnTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
		parent::setUp();
	}

	/**
	 * @covers Et\DB_Query_Column::__construct()
	 * @covers Et\DB_Query_Column::getColumnName()
	 * @covers Et\DB_Query_Column::__toString()
	 */
	function test_getters(){

		$query = new Et\DB_Query("my_table");

		$column = new Et\DB_Query_Column($query, "some_column");
		$this->assertEquals("my_table", $column->getTableName());
		$this->assertEquals("some_column", $column->getColumnName(false));
		$this->assertEquals("some_column", $column->getColumnName());
		$this->assertEquals("my_table.some_column", $column->getColumnName(true));
		$this->assertEquals("my_table.some_column",  (string)$column);

		$column = new Et\DB_Query_Column($query, "other_table.some_column");
		$this->assertEquals("other_table", $column->getTableName());
		$this->assertEquals("some_column", $column->getColumnName(false));
		$this->assertEquals("some_column", $column->getColumnName());
		$this->assertEquals("other_table.some_column", $column->getColumnName(true));
		$this->assertEquals("other_table.some_column",  (string)$column);

		$column = new Et\DB_Query_Column($query, Et\DB_Query::MAIN_TABLE_ALIAS . ".some_column");
		$this->assertEquals("my_table", $column->getTableName());
		$this->assertEquals("some_column", $column->getColumnName(false));
		$this->assertEquals("some_column", $column->getColumnName());
		$this->assertEquals("my_table.some_column", $column->getColumnName(true));
		$this->assertEquals("my_table.some_column",  (string)$column);

		$column = new Et\DB_Query_Column($query, Et\DB_Query::SELECTED_COLUMN_ALIAS . ".some_column");
		$this->assertNull($column->getTableName());
		$this->assertEquals("some_column", $column->getColumnName(false));
		$this->assertEquals("some_column", $column->getColumnName());
		$this->assertEquals("some_column", $column->getColumnName(true));
		$this->assertEquals("some_column",  (string)$column);
	}



}