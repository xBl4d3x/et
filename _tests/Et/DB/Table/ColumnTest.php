<?php
namespace EtTest\Et;
use EtTest;
use Et;


class DB_Table_ColumnTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Et\DB_Table_Column
	 */
	protected $column;


	protected function setUp() {
		$this->column = new Et\DB_Table_Column("some_column", "some_table");
		parent::setUp();
	}

	/**
	 * @covers Et\DB_Table_Column::__construct()
	 * @covers Et\DB_Table_Column::getColumnName()
	 * @covers Et\DB_Table_Column::__toString()
	 */
	function test_getters(){
		$this->assertEquals("some_column", $this->column->getColumnName(false));
		$this->assertEquals("some_column", $this->column->getColumnName());
		$this->assertEquals("some_table.some_column", $this->column->getColumnName(true));
		$this->assertEquals("some_table.some_column",  (string)$this->column);

		$column = new Et\DB_Table_Column("some_column");
		$this->assertEquals("some_column", $column->getColumnName(false));
		$this->assertEquals("some_column", $column->getColumnName());
		$this->assertEquals("some_column", $column->getColumnName(true));
		$this->assertEquals("some_column",  (string)$column);
		$this->assertNull($column->getTableName());
	}

	function provider_wrongColumnName(){
		return array(
			array("WRONG COLUMN"),
			array("WRONG COLUMN.table_name"),
			array("WRONG COLUMN", "table_name"),
			array("WRONG TABLE.column_name")
		);
	}

	/**
	 * @covers Et\DB_Table_Column::__construct()
	 * @expectedException Et\DB_Exception
	 * @expectedExceptionCode Et\DB_Exception::CODE_INVALID_COLUMN_NAME
	 * @dataProvider provider_wrongColumnName
	 */
	function test_wrongColumnName($column_name, $table_name = null){
		new Et\DB_Table_Column($column_name, $table_name);
	}

	function provider_wrongTableName(){
		return array(
			array("column_name", "WRONG TABLE")
		);
	}

	/**
	 * @covers Et\DB_Table_Column::__construct()
	 * @expectedException Et\DB_Exception
	 * @expectedExceptionCode Et\DB_Exception::CODE_INVALID_TABLE_NAME
	 * @dataProvider provider_wrongTableName
	 */
	function test_wrongTableName($column_name, $table_name = null){
		new Et\DB_Table_Column($column_name, $table_name);
	}

}