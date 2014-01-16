<?php
namespace EtTest\Et;
use EtTest;
use Et;

class Exception_Simple extends Et\Exception {

}

class Exception_WithCodes extends Et\Exception {
	const CODE_ONE = 10;
	const CODE_TWO = 20;
	const CODE_THREE = 30;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_ONE => "Code ONE",
		self::CODE_TWO => "Code TWO",
		self::CODE_THREE => "Code THREE",
	);
}

class Exception_WithCodesExtended extends Exception_WithCodes {

	const CODE_FOUR = 40;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_ONE => "Code ONE - overloaded",
		self::CODE_FOUR => "Code FOUR"
	);
}

class Test_Exception extends EtTest\TestCase {

	/**
	 * @var Et\Exception
	 */
	protected $blank_instance;

	function setUp(){
		$this->blank_instance = $this->getMockBuilder('Et\Exception')->disableOriginalConstructor()->getMockForAbstractClass();
	}

	function tearDown(){

	}

	/**
	 * @covers Et\Exception::setContextData
	 * @covers Et\Exception::getContextData
	 */
	function test_getSetContextData(){
		$this->assertNull($this->blank_instance->getContextData(), "Context data should be null by default");

		$this->blank_instance->setContextData(array("key" => "value"));
		$this->assertEquals(array("key" => "value"), $this->blank_instance->getContextData(), "The last value set should be read");

		$this->blank_instance->setContextData(array("key" => "newValue"));
		$this->assertEquals(array("key" => "newValue"), $this->blank_instance->getContextData(), "Context data should be modifiable (1 / 3)");

		$this->blank_instance->setContextData(array("newKey" => "otherValue"));
		$this->assertEquals(array("newKey" => "otherValue"), $this->blank_instance->getContextData(), "Context data should be modifiable (2 / 3)");

		$this->blank_instance->setContextData("Simple string");
		$this->assertEquals("Simple string", $this->blank_instance->getContextData(), "Context data should be modifiable (3 / 3)");

		$this->blank_instance->setContextData(null);
		$this->assertNull($this->blank_instance->getContextData(), "Context data should be nullable");
	}

	function provider_basicProvider(){
		$data = array(
			1 => array("First error message", 1),
			2 => array("Second error message", 55),
			3 => array("", 0),
		);
		return $data;
	}

	/**
	 * @covers Et\Exception::__construct
	 * @param $message
	 * @param $code
	 * @dataProvider provider_basicProvider
	 */
	function test_basicConstructor($message, $code){
		$exception = new Exception_Simple($message, $code);

		$this->assertInstanceOf("Et\\Exception", $exception, "Any exception should inherit from Et\\Exception");
		$this->assertEquals($message, $exception->getMessage(), "Exception message should be the one passed in constructor");
		$this->assertEquals($code, $exception->getCode(), "Exception code should be the one passed in constructor");
	}

	/**
	 * @covers Et\Exception::getErrorID
	 */
	function test_getErrorID(){
		$exceptionA = new Exception_Simple("First", 1);
		$exceptionB = new Exception_Simple("Second", 1);
		$exceptionC = new Exception_Simple("First", 2);
		$exceptionD = new Exception_Simple("First", 1);

		$this->assertNotEmpty($exceptionA->getErrorID(), "Exception ID should not be empty");
		$this->assertNotEquals($exceptionA->getErrorID(), $exceptionB->getErrorID(), "Two exceptions with different messages should not have the same ID");
		$this->assertNotEquals($exceptionA->getErrorID(), $exceptionC->getErrorID(), "Two exceptions with different codes should not have the same ID");
		$this->assertNotEquals($exceptionA->getErrorID(), $exceptionD->getErrorID(), "Two exceptions with the same codes and messages should not have the same ID");

	}

	/**
	 * @covers Et\Exception::__construct
	 */
	function test_advancedConstructor(){
		$exceptionA = new Exception_Simple("First", 1);
		$exceptionB = new Exception_Simple("First", 1, "Test context");
		$exceptionC = new Exception_Simple("First", 1, null, $exceptionA);
		$exceptionD = new Exception_Simple("First", 1, "Test context", $exceptionA);

		$this->assertNull($exceptionA->getContextData(), "Context data should be null when not set in constructor");
		$this->assertEquals("Test context", $exceptionB->getContextData(), "Context data should be set by constructor");
		$this->assertNull($exceptionA->getPrevious(), "Previous exception should be null when not set in constructor");
		$this->assertEquals($exceptionA, $exceptionC->getPrevious(), "Previous exception should be set by constructor");
		$this->assertEquals("Test context", $exceptionD->getContextData(), "Both context data and previous exception should be set by constructor (1 / 2)");
		$this->assertEquals($exceptionA, $exceptionD->getPrevious(), "Both context data and previous exception should be set by constructor (2 / 2)");
	}

	/**
	 * @covers Et\Exception::getFile
	 * @covers Et\Exception::getLine
	 */
	function test_getFileAndLine(){
		$exception = new Exception_Simple("First", 1); $exceptionLine = __LINE__;

		$this->assertEquals($exceptionLine, $exception->getLine(), "getLine() should return the line, on which the exception was created");
		$this->assertEquals(__FILE__, $exception->getFile(), "getFile() should return the line, in which the exception was created");
	}

	/**
	 * Helper function used to test exception backtrace handling in nested functions
	 *
	 * @param $foo mixed First arbitrary argument to test argument display in the backtrace
	 * @param $bar mixed Second arbitrary argument to test argument display in the backtrace
	 * @param $goo mixed Third arbitrary argument to test argument display in the backtrace
	 * @param null|int $backtrace_offset [optional] Passed to Et\Exception constructor, null = use default
	 * @param null|int $backtrace_limit [optional] Passed to Et\Exception constructor, null = use default
	 * @return Exception_Simple
	 */
	function backtraceLevelOne($foo, $bar, $goo, $backtrace_offset = null, $backtrace_limit = null){
		return $this->backtraceLevelTwo($foo, $bar, $backtrace_offset, $backtrace_limit);
	}

	/**
	 * Helper function used to test exception backtrace handling in nested functions
	 *
	 * @param $first_arg mixed First arbitrary argument to test argument display in the backtrace
	 * @param $second_arg mixed Second arbitrary argument to test argument display in the backtrace
	 * @param null|int $backtrace_offset [optional] Passed to Et\Exception constructor, null = use default
	 * @param null|int $backtrace_limit [optional] Passed to Et\Exception constructor, null = use default
	 * @return Exception_Simple
	 */
	function backtraceLevelTwo($first_arg, $second_arg, $backtrace_offset = null, $backtrace_limit = null){
		return $this->backtraceLevelThree($first_arg, __LINE__, $backtrace_offset, $backtrace_limit);
	}

	/**
	 * Helper function used to test exception backtrace handling in nested functions
	 *
	 * @param $some_arg mixed First arbitrary argument to test argument display in the backtrace
	 * @param $parent_line int Should be set to the line from which this function gets called (to verify line number computation in backtrace)
	 * @param null|int $backtrace_offset [optional] Passed to Et\Exception constructor, null = use default
	 * @param null|int $backtrace_limit [optional] Passed to Et\Exception constructor, null = use default
	 * @return Exception_Simple
	 */
	function backtraceLevelThree($some_arg, $parent_line, $backtrace_offset = null, $backtrace_limit = null){
		if($backtrace_offset === null){
			if($backtrace_limit === null){
				return new Exception_Simple("Backtrace testing exception, no offset or limit", 100, $parent_line);
			}
			else {
				return new Exception_Simple("Backtrace testing exception, offset 0, limit {$backtrace_limit}", 101, $parent_line, null, 0, $backtrace_limit);
			}
		}
		else {
			if($backtrace_limit === null){
				return new Exception_Simple("Backtrace testing exception, offset {$backtrace_offset}, no limit", 110, $parent_line, null, $backtrace_offset);
			}
			else {
				return new Exception_Simple("Backtrace testing exception, offset {$backtrace_offset}, limit {$backtrace_limit}", 111, $parent_line, null, $backtrace_offset, $backtrace_limit);
			}
		}
	}

	/**
	 * @covers Et\Exception::__constructor
	 * @covers Et\Exception::getDebugBacktrace
	 */
	function test_backtraceBasic(){
		$exception = $this->backtraceLevelOne("varFoo", 123, null);
		$backtrace = $exception->getDebugBacktrace();

		$this->assertInternalType("array", $backtrace, "Backtrace should be an array");
		$this->assertNotEmpty($backtrace, "Backtrace should not be empty");
		$this->assertGreaterThan(2, count($backtrace), "There should be at least 3 entries in the backtrace for this test case");

		$first_entry = $backtrace[0];

		$expected_keys = array("function", "line", "file", "class", "object", "type", "args");
		foreach($expected_keys as $key){
			$this->assertArrayHasKey($key, $first_entry, "First entry in backtrace should provide key '{$key}''");
		}

		$this->assertEquals("backtraceLevelThree", $first_entry["function"], "Function name of the first entry should correspond to the last function call prior to creating the exception");
		$this->assertEquals($exception->getContextData(), $first_entry["line"], "Line of the first entry should correspond to the line from which the last function call was made");
		$this->assertEquals(__FILE__, $first_entry["file"], "File of the first entry should correspond to the file from which the last function call was made");
		$this->assertEquals(__CLASS__, $first_entry["class"], "Class of the first entry should correspond to the class the last called function belongs to");
		$this->assertEquals($this, $first_entry["object"], "Object of the first entry should correspond to the object for which the last function call was made");
		$this->assertEquals("->", $first_entry["type"], "Call type of the first entry should be '->', if it was called as a member function");
		$this->assertInternalType("array", $first_entry["args"], "Arguments of the first entry should be an array");
		$this->assertCount(4, $first_entry["args"], "There should be 4 arguments for the first entry in this test case");
		$this->assertEquals("varFoo", $first_entry["args"][0], "The first argument should be 'varFoo' for this test case");

		$second_entry = $backtrace[1];
		$this->assertEquals("backtraceLevelTwo", $second_entry["function"], "Function name of the second entry should be 'backtraceLevelTwo' for this test case");
		$this->assertCount(4, $second_entry["args"], "There should be 4 arguments for the second entry for this test case");
		$this->assertEquals(123, $second_entry["args"][1], "The second argument for the second entry should be 123 for this test case");

		$third_entry = $backtrace[2];
		$this->assertEquals("backtraceLevelOne", $third_entry["function"], "Function name of the third entry should be 'backtraceLevelOne' for this test case");
		$this->assertCount(3, $third_entry["args"], "There should be 3 arguments for the third entry for this test case (default args do not count)");
		$this->assertEquals(null, $third_entry["args"][2], "The third argument for the third entry should be null for this test case");
	}

	/**
	 * @covers Et\Exception::__constructor
	 * @covers Et\Exception::getDebugBacktrace
	 */
	function test_backtraceOffset(){
		$exception = $this->backtraceLevelOne("varFoo", 123, null);
		$backtrace_complete = $exception->getDebugBacktrace();

		$this->assertEquals("backtraceLevelThree", $backtrace_complete[0]["function"], "By default, the backtrace should start with the last function called");

		$exception = $this->backtraceLevelOne("varFoo", 123, null, 1);
		$backtrace_one = $exception->getDebugBacktrace();

		$this->assertCount(count($backtrace_complete) - 1, $backtrace_one, "Backtrace with offset 1 should have one less entry, than the complete backtrace");
		$this->assertEquals("backtraceLevelTwo", $backtrace_one[0]["function"], "Backtrace with offset 1 should begin with function 'backtraceLevelTwo' in this test case");

		$exception = $this->backtraceLevelOne("varFoo", 123, null, 2);
		$backtrace_two = $exception->getDebugBacktrace();

		$this->assertCount(count($backtrace_complete) - 2, $backtrace_two, "Backtrace with offset 2 should have two entries less, than the complete backtrace");
		$this->assertEquals("backtraceLevelOne", $backtrace_two[0]["function"], "Backtrace with offset 2 should begin with function 'backtraceLevelOne' in this test case");

		$exception = $this->backtraceLevelOne("varFoo", 123, null, count($backtrace_complete));
		$backtrace_empty = $exception->getDebugBacktrace();

		$this->assertEmpty($backtrace_empty, "Backtrace should be empty if offset is larger than the number of entries in a complete backtrace");
		$this->assertInternalType("array", $backtrace_empty, "Empty backtrace should still be an array");

		$exception = $this->backtraceLevelOne("varFoo", 123, null, -10);
		$backtrace_negative = $exception->getDebugBacktrace();

		$this->assertCount(count($backtrace_complete), $backtrace_negative, "Negative offset should result in a complete backtrace");
	}

	/**
	 * @covers Et\Exception::__constructor
	 * @covers Et\Exception::getDebugBacktrace
	*/
	function test_backtraceLimit(){
		$exception = $this->backtraceLevelOne("varFoo", 123, null);
		$backtrace_complete = $exception->getDebugBacktrace();

		$this->assertGreaterThan(2, count($backtrace_complete), "By default, the backtrace should be complete (= at least 3 entries for this test case)");

		$exception = $this->backtraceLevelOne("varFoo", 123, null, null, 2);
		$backtrace_two = $exception->getDebugBacktrace();

		$this->assertCount(2, $backtrace_two, "Two entries should be present if backtrace_limit = 2");
		$this->assertEquals("backtraceLevelThree", $backtrace_two[0]["function"], "The first entry function name should be 'backtraceLevelThree' for this test case");
		$this->assertEquals("backtraceLevelTwo", $backtrace_two[1]["function"], "The second entry function name should be 'backtraceLevelTwo' for this test case");

		$exception = $this->backtraceLevelOne("varFoo", 123, null, null, 1);
		$backtrace_one = $exception->getDebugBacktrace();

		$this->assertCount(1, $backtrace_one, "One entry should be present if backtrace_limit = 1");
		$this->assertEquals("backtraceLevelThree", $backtrace_one[0]["function"], "The first entry function name should be 'backtraceLevelThree' for this test case");

		$exception = $this->backtraceLevelOne("varFoo", 123, null, null, -10);
		$backtrace_negative = $exception->getDebugBacktrace();

		$this->assertCount(count($backtrace_complete), $backtrace_negative, "Negative limit should result in a complete backtrace");

		$exception = $this->backtraceLevelOne("varFoo", 123, null, null, 0);
		$backtrace_zero = $exception->getDebugBacktrace();

		$this->assertCount(count($backtrace_complete), $backtrace_zero, "Zero limit should result in a complete backtrace");

		$exception = $this->backtraceLevelOne("varFoo", 123, null, null, count($backtrace_complete));
		$backtrace_equal = $exception->getDebugBacktrace();

		$this->assertCount(count($backtrace_complete), $backtrace_equal, "Backtrace should be complete, if backtrace_limit is set to the number of entries in a complete backtrace");

		$exception = $this->backtraceLevelOne("varFoo", 123, null, null, count($backtrace_complete) + 10);
		$backtrace_greater = $exception->getDebugBacktrace();

		$this->assertCount(count($backtrace_complete), $backtrace_greater, "Backtrace should not contain more entries, even if backtrace_limit is set to a value greater than the number of entries in a complete backtrace");
	}

	/**
	 * @covers Et\Exception::__constructor
	 * @covers Et\Exception::getDebugBacktrace
	 */
	function test_backtraceOffsetLimit(){
		$exception = $this->backtraceLevelOne("varFoo", 123, null);
		$backtrace_complete = $exception->getDebugBacktrace();

		$this->assertGreaterThan(2, count($backtrace_complete), "By default, the backtrace should be complete (= at least 3 entries for this test case)");

		$exception = $this->backtraceLevelOne("varFoo", 123, null, 1, 2);
		$backtrace_1_2 = $exception->getDebugBacktrace();

		$this->assertCount(2, $backtrace_1_2, "Two entries should be present if backtrace_limit = 2");
		$this->assertEquals("backtraceLevelTwo", $backtrace_1_2[0]["function"], "The first entry function name should be 'backtraceLevelTwo' for this test case");
		$this->assertEquals("backtraceLevelOne", $backtrace_1_2[1]["function"], "The second entry function name should be 'backtraceLevelOne' for this test case");

		$exception = $this->backtraceLevelOne("varFoo", 123, null, 2, 0);
		$backtrace_2_0 = $exception->getDebugBacktrace();

		$this->assertCount(count($backtrace_complete) - 2, $backtrace_2_0, "For backtrace_offset = 2 and backtrace_limit = 0, there should be two less entries than in the complete backtrace");

		$exception = $this->backtraceLevelOne("varFoo", 123, null, count($backtrace_complete) - 1, 5);
		$backtrace_back = $exception->getDebugBacktrace();

		$this->assertCount(1, $backtrace_back, "If backtrace_offset points to the last entry in the complete backtrace, there should be only one entry, even if backtrace_limit is set to a higher number");
	}

}