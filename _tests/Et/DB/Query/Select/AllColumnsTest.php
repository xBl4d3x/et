<?php
namespace EtTest\Et;
use EtTest;
use Et;


class DB_Query_Select_AllColumnsTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
		parent::setUp();
	}

	/**
	 * @covers Et\DB_Query_Select_AllColumns::__construct()
	 * @covers Et\DB_Query_Select_AllColumns::getTableName()
	 * @covers Et\DB_Query_Select_AllColumns::__toString()
	 */
	function test_getters(){

		$query = new Et\DB_Query("my_table");

		$column = new Et\DB_Query_Select_AllColumns($query);
		$this->assertNull($column->getTableName());
		$this->assertEquals(Et\DB_Query::ALL_COLUMNS, (string)$column);

		$column = new Et\DB_Query_Select_AllColumns($query, Et\DB_Query::MAIN_TABLE_ALIAS);
		$this->assertEquals("my_table", $column->getTableName());
		$this->assertEquals("my_table." . Et\DB_Query::ALL_COLUMNS, (string)$column);

		$column = new Et\DB_Query_Select_AllColumns($query, "other_table");
		$this->assertEquals("other_table", $column->getTableName());
		$this->assertEquals("other_table." . Et\DB_Query::ALL_COLUMNS, (string)$column);
	}



}