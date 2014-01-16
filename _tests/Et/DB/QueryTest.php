<?php
namespace EtTest\Et;
use EtTest;
use Et;

class DB_QueryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Et\DB_Query
	 */
	protected $query;

	protected function setUp() {
		$this->query = new Et\DB_Query("some_table");
		parent::setUp();
	}

	/**
	 * @covers Et\DB_Query::__construct()
	 * @covers Et\DB_Query::getMainTableName()
	 * @covers Et\DB_Query::getTablesInQuery()
	 * @covers Et\DB_Query::getTablesInQueryCount()
	 */
	function test_newQuery(){
		$this->assertEquals("some_table", $this->query->getMainTableName());
		$this->assertEquals(array("some_table" => "some_table"), $this->query->getTablesInQuery());
		$this->assertEquals(1, $this->query->getTablesInQueryCount());
	}

	/**
	 * @param $method_name
	 * @return \ReflectionMethod
	 */
	protected function getQueryMethodReflection($method_name){
		$method = new \ReflectionMethod($this->query, "isTableAlias");
		$method->setAccessible(true);
		return $method;
	}

	function provider_newWrongQuery(){
		return array(
			array("table name"),
			array(Et\DB_Query::MAIN_TABLE_ALIAS),
			array(Et\DB_Query::SELECTED_COLUMN_ALIAS)
		);
	}

	/**
	 * @covers Et\DB_Query::__construct()
	 * @dataProvider provider_newWrongQuery
	 * @expectedException Et\DB_Query_Exception
	 * @expectedExceptionCode Et\DB_Query_Exception::CODE_INVALID_TABLE_NAME
	 * @param $table_name
	 */
	function test_newWrongQuery($table_name){
		new Et\DB_Query($table_name);
	}

	/**
	 * @covers Et\DB_Query::isTableAlias()
	 */
	function test_isTableAlias(){
		$method = $this->getQueryMethodReflection("isTableAlias");
		$this->assertFalse($method->invoke(null, "some_table"));
		$this->assertTrue($method->invoke(null, Et\DB_Query::MAIN_TABLE_ALIAS));
		$this->assertTrue($method->invoke(null, Et\DB_Query::SELECTED_COLUMN_ALIAS));
	}

	function provider_addTableToQuery(){
		return array(
			array(
				"other_table",
				array(
					"some_table" => "some_table",
					"other_table" => "other_table"
				)
			),
			array(
				Et\DB_Query::MAIN_TABLE_ALIAS,
				array(
					"some_table" => "some_table",
				)
			),
			array(
				Et\DB_Query::SELECTED_COLUMN_ALIAS,
				array(
					"some_table" => "some_table",
				)
			)
		);
	}

	/**
	 * @covers Et\DB_Query::addTableToQuery()
	 * @dataProvider provider_addTableToQuery
	 * @param $table_name
	 * @param array $expected_tables
	 */
	function test_addTableToQuery($table_name, array $expected_tables){
		$this->assertSame($this->query, $this->query->addTableToQuery($table_name));
		$this->assertEquals($expected_tables, $this->query->getTablesInQuery());
		$this->assertEquals(count($expected_tables), $this->query->getTablesInQueryCount());
	}

	/**
	 * @covers Et\DB_Query::getMainColumnName()
	 * @covers Et\DB_Query::getMainColumnNames()
	 */
	function test_mainColumnNames(){
		$this->assertEquals("some_table.test_column", $this->query->getMainColumnName("test_column"));
		$this->assertEquals(
			array(
				"some_table.test_column",
				"some_table.other_column"
			),
			$this->query->getMainColumnNames(
				array(
					"test_column",
					"other_column"
				)
			)
		);
	}

	/**
	 * @covers Et\DB_Query::resolveTableName()
	 */
	function test_resolveTableName(){
		$this->assertEquals("some_table", $this->query->resolveTableName(""));
		$this->assertEquals("some_table", $this->query->resolveTableName("some_table"));
		$this->assertEquals("some_table", $this->query->resolveTableName(Et\DB_Query::MAIN_TABLE_ALIAS));
		$this->assertNull( $this->query->resolveTableName(Et\DB_Query::SELECTED_COLUMN_ALIAS));
	}

	/**
	 * @covers Et\DB_Query::orderBy()
	 * @covers Et\DB_Query::getOrderBy()
	 * @covers Et\DB_Query::orderByColumn()
	 */
	function test_orderBy(){

		$this->assertEquals(array(), $this->query->getOrderBy());
		$this->assertSame($this->query, $this->query->orderBy(
			array(
				1 => "ASC",
				"some_column" => "desc",
				Et\DB_Query::MAIN_TABLE_ALIAS . ".other_column" => "asc",
				Et\DB_Query::SELECTED_COLUMN_ALIAS . ".result_column" => "DESC"
			)
		));

		$this->assertEquals(array(
			1 => "ASC",
			"some_table.some_column" => "DESC",
			"some_table.other_column" => "ASC",
			"result_column" => "DESC",

		), $this->query->getOrderBy());

		$this->assertSame($this->query, $this->query->orderBy(
			array(
				"some_column" => "desc",
				1 => "ASC",

			)
		));

		$this->assertEquals(array(
			"some_table.some_column" => "DESC",
			1 => "ASC",
		), $this->query->getOrderBy());




		$this->assertSame($this->query, $this->query->orderByColumn(1));
		$this->assertEquals(array(1 => "ASC"), $this->query->getOrderBy());

		$this->assertSame($this->query, $this->query->orderByColumn(1, "desc"));
		$this->assertEquals(array(1 => "DESC"), $this->query->getOrderBy());

		$this->assertSame($this->query, $this->query->orderByColumn("some_column"));
		$this->assertEquals(array("some_table.some_column" => "ASC"), $this->query->getOrderBy());

		$this->assertSame($this->query, $this->query->orderByColumn(Et\DB_Query::MAIN_TABLE_ALIAS . ".some_column"));
		$this->assertEquals(array("some_table.some_column" => "ASC"), $this->query->getOrderBy());

		$this->assertSame($this->query, $this->query->orderByColumn(Et\DB_Query::SELECTED_COLUMN_ALIAS . ".some_column"));
		$this->assertEquals(array("some_column" => "ASC"), $this->query->getOrderBy());

	}



	/**
	 * @covers Et\DB_Query::groupBy()
	 * @covers Et\DB_Query::getGroupBy()
	 * @covers Et\DB_Query::groupByColumn()
	 */
	function test_groupBy(){

		$this->assertEquals(array(), $this->query->getGroupBy());
		$this->assertSame($this->query, $this->query->groupBy(
			array(
				"some_column",
				Et\DB_Query::MAIN_TABLE_ALIAS . ".other_column",
				Et\DB_Query::SELECTED_COLUMN_ALIAS . ".result_column"
			)
		));

		$this->assertEquals(array(
			"some_table.some_column" => "some_table.some_column",
			"some_table.other_column" => "some_table.other_column",
			"result_column" => "result_column",

		), $this->query->getGroupBy());

		$this->assertSame($this->query, $this->query->groupBy(
			array(
				"some_column"

			)
		));

		$this->assertEquals(array(
			"some_table.some_column" => "some_table.some_column"
		), $this->query->getGroupBy());




		$this->assertSame($this->query, $this->query->groupByColumn("some_column"));
		$this->assertEquals(
			array("some_table.some_column" => "some_table.some_column"),
			$this->query->getGroupBy()
		);

		$this->assertSame($this->query, $this->query->groupByColumn(Et\DB_Query::MAIN_TABLE_ALIAS . ".some_column"));
		$this->assertEquals(
			array("some_table.some_column" => "some_table.some_column"),
			$this->query->getGroupBy()
		);

		$this->assertSame($this->query, $this->query->groupByColumn(Et\DB_Query::SELECTED_COLUMN_ALIAS . ".some_column"));
		$this->assertEquals(
			array("some_column" => "some_column"),
			$this->query->getGroupBy()
		);

	}


	/**
	 * @covers Et\DB_Query::getLimit()
	 * @covers Et\DB_Query::getOffset()
	 * @covers Et\DB_Query::setLimit()
	 * @covers Et\DB_Query::setOffset()
	 * @covers Et\DB_Query::limit()
	 */
	function test_limitOffset(){
		$this->assertEquals(0, $this->query->getLimit());
		$this->assertEquals(0, $this->query->getOffset());

		$this->assertSame($this->query, $this->query->setLimit(10));
		$this->assertEquals(10, $this->query->getLimit());
		$this->assertEquals(0, $this->query->getOffset());

		$this->assertSame($this->query, $this->query->setOffset(5));
		$this->assertEquals(10, $this->query->getLimit());
		$this->assertEquals(5, $this->query->getOffset());

		$this->assertSame($this->query, $this->query->limit(15, 25));
		$this->assertEquals(15, $this->query->getLimit());
		$this->assertEquals(25, $this->query->getOffset());
	}

	/**
	 * @covers Et\DB_Query::getColumn()
	 */
	function test_getColumn(){
		$column = $this->query->getColumn("first");
		$this->assertInstanceOf("Et\\DB_Query_Column", $column);
		$this->assertEquals("some_table.first", (string)$column);
		$this->assertEquals(
			array("some_table" => "some_table"),
			$this->query->getTablesInQuery()
		);

		$column = $this->query->getColumn(Et\DB_Query::MAIN_TABLE_ALIAS.".second");
		$this->assertEquals("some_table.second", (string)$column);
		$this->assertEquals(
			array("some_table" => "some_table"),
			$this->query->getTablesInQuery()
		);

		$column = $this->query->getColumn(Et\DB_Query::SELECTED_COLUMN_ALIAS.".selected");
		$this->assertEquals("selected", (string)$column);
		$this->assertEquals(
			array("some_table" => "some_table"),
			$this->query->getTablesInQuery()
		);

		$column = $this->query->getColumn("other_table.third");
		$this->assertEquals("other_table.third", (string)$column);
		$this->assertEquals(
			array(
				"some_table" => "some_table",
				"other_table" => "other_table"
			),
			$this->query->getTablesInQuery()
		);
	}

}