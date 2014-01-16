<?php
namespace EtTest\Et;
use EtTest;
use Et;


class DB_Query_Builder_StringTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Et\DB_Query_Builder_String
	 */
	protected $builder;

	protected function setUp() {
		$this->builder = new Et\DB_Query_Builder_String();
		parent::setUp();
	}

	function test_quote(){
		$b = $this->builder;
		$this->assertEquals("test_table", $b->quoteIdentifier("test_table"));
		$this->assertEquals("'Sample \\' string'", $b->quoteValue("Sample ' string"));
		$this->assertEquals(10, $b->quoteValue(10));
		$this->assertEquals(10.5, $b->quoteValue(10.5));
		$this->assertEquals(1, $b->quoteValue(true));
		$this->assertEquals("NULL", $b->quoteValue(null));
		$this->assertEquals("some value", $b->quoteValue(new Et\DB_Expression("some value")));
		$this->assertEquals("table.column", $b->quoteValue(new Et\DB_Table_Column("column", "table")));
		$this->assertEquals("'2012-05-06'", $b->quoteValue(new Et\Locales_Date("2012-05-06")));
		$this->assertEquals("'2012-05-06 10:30:20'", $b->quoteValue(new Et\Locales_DateTime("2012-05-06 10:30:20")));
		$this->assertEquals("'cs_CZ'", $b->quoteValue(new Et\Locales_Locale("cs_CZ")));
		$this->assertEquals("'Europe/Prague'", $b->quoteValue(new Et\Locales_Timezone("Europe/Prague")));
		$this->assertEquals("'{\n    \\\"a\\\": 10\n}'", $b->quoteValue(array("a" => 10)));
	}

	function test_buildSelectExpression(){
		$query = new Et\DB_Query("some_table");
		$b = $this->builder;
		$this->assertEquals("*", $b->buildSelectExpression($query->getSelect()));

		$select = new Et\DB_Query_Select($query, array("*"));
		$this->assertEquals("*", $b->buildSelectExpression($select));

		$select = new Et\DB_Query_Select($query, array("some_table.*"));
		$this->assertEquals("*", $b->buildSelectExpression($select));

		$select = new Et\DB_Query_Select($query, array("column_alias" => "column_name"));
		$this->assertEquals("column_name AS column_alias", $b->buildSelectExpression($select));
	}
}