<?php
namespace EtTest\Et;
use EtTest;
use Et;

class DB_Table_Column_DefinitionTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
		parent::setUp();
	}


	function provider_columnTypes(){
		return array(
			array(Et\DB_Table_Column_Definition::TYPE_BOOL),
			array(Et\DB_Table_Column_Definition::TYPE_INT, 11),
			array(Et\DB_Table_Column_Definition::TYPE_STRING, 255),
			array(Et\DB_Table_Column_Definition::TYPE_FLOAT),
			array(Et\DB_Table_Column_Definition::TYPE_LOCALE, 5),
			array(Et\DB_Table_Column_Definition::TYPE_DATETIME),
			array(Et\DB_Table_Column_Definition::TYPE_DATE),
			array(Et\DB_Table_Column_Definition::TYPE_BINARY_DATA)
		);
	}

	/**
	 * @dataProvider provider_columnTypes
	 *
	 * @param $type
	 * @param null $size
	 */
	function test_columnTypes($type, $size = null){
		$column = new Et\DB_Table_Column_Definition("some_column", $type);

		$this->assertEquals($type, $column->getType());
		$this->assertEquals($size, $column->getSize());
		$this->assertEquals("some_column", $column->getColumnName());
	}

	function provider_getValueFromDB(){
		return array(
			array(
				Et\DB_Table_Column_Definition::TYPE_BOOL,
				1,
				true
			),
			array(
				Et\DB_Table_Column_Definition::TYPE_BOOL,
				0,
				false
			),

			array(
				Et\DB_Table_Column_Definition::TYPE_INT,
				10,
				10
			),

			array(
				Et\DB_Table_Column_Definition::TYPE_STRING,
				"Test",
				"Test"
			),

			array(
				Et\DB_Table_Column_Definition::TYPE_BINARY_DATA,
				"Test\0Test",
				"Test\0Test"
			),

			array(
				Et\DB_Table_Column_Definition::TYPE_FLOAT,
				10.5,
				10.5
			),

			array(
				Et\DB_Table_Column_Definition::TYPE_LOCALE,
				"cs_CZ",
				new Et\Locales_Locale("cs_CZ")
			),

			array(
				Et\DB_Table_Column_Definition::TYPE_LOCALE,
				"",
				null
			),


			array(
				Et\DB_Table_Column_Definition::TYPE_DATETIME,
				"2012-02-03 10:50:30",
				new Et\Locales_DateTime("2012-02-03 10:50:30")
			),

			array(
				Et\DB_Table_Column_Definition::TYPE_DATE,
				"2012-02-03",
				new Et\Locales_Date("2012-02-03")
			),

		);
	}

	/**
	 * @dataProvider provider_getValueFromDB
	 *
	 * @param $type
	 * @param $value
	 * @param $expected_value
	 */
	function test_getValueFromDB($type, $value, $expected_value){
		$column = new Et\DB_Table_Column_Definition("some_column", $type);
		$this->assertEquals($expected_value, $column->getValueFromDB($value));
	}



	function provider_defaultValue(){
		return array(
			array(
				Et\DB_Table_Column_Definition::TYPE_BOOL,
				1,
				true
			),
			array(
				Et\DB_Table_Column_Definition::TYPE_BOOL,
				0,
				false
			),

			array(
				Et\DB_Table_Column_Definition::TYPE_INT,
				10,
				10
			),

			array(
				Et\DB_Table_Column_Definition::TYPE_STRING,
				"Test",
				"Test"
			),

			array(
				Et\DB_Table_Column_Definition::TYPE_BINARY_DATA,
				"Test\0Test",
				"Test\0Test"
			),

			array(
				Et\DB_Table_Column_Definition::TYPE_FLOAT,
				10.5,
				10.5
			),

			array(
				Et\DB_Table_Column_Definition::TYPE_LOCALE,
				"cs_CZ",
				new Et\Locales_Locale("cs_CZ")
			),

			array(
				Et\DB_Table_Column_Definition::TYPE_LOCALE,
				"",
				null
			),


			array(
				Et\DB_Table_Column_Definition::TYPE_DATETIME,
				"2012-02-03 10:50:30",
				new Et\Locales_DateTime("2012-02-03 10:50:30")
			),

			array(
				Et\DB_Table_Column_Definition::TYPE_DATE,
				"2012-02-03",
				new Et\Locales_Date("2012-02-03")
			),
		);
	}


	/**
	 * @dataProvider provider_defaultValue
	 *
	 * @param $type
	 * @param $value
	 * @param $expected_value
	 */
	function test_defaultValue($type, $value, $expected_value){
		$column = new Et\DB_Table_Column_Definition("some_column", $type);
		$column->setDefaultValue($value);
		$this->assertEquals($expected_value, $column->getDefaultValue());
	}
}