<?php
namespace EtTest\Et;
use EtTest;
use Et;

class SampleException extends \Exception {
	const CODE_ONE = 10;

	/**
	 * @return int
	 */
	function getNewCode(){
		return $this->code + 10;
	}
}

class Test_Stub extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \stdClass
	 */
	protected $some_object;

	function setUp(){
		$this->some_object = new \stdClass();
		$this->some_object->some_property = "Hello";
	}

	function tearDown(){

	}

	function test_getSomeProperty(){
		$this->assertEquals("Hello", $this->some_object->some_property);
	}

	/**
	 * Example of catching exception by annotation
	 *
	 * @expectedException EtTest\Et\SampleException
	 * @expectedExceptionCode EtTest\Et\SampleException::CODE_ONE
	 */
	function test_exceptionSimple(){
		throw new SampleException("HELLO", SampleException::CODE_ONE);
	}

	/**
	 * Example of catching exception by annotation -> test should fail
	 *
	 * @expectedException EtTest\Et\SampleException
	 * @expectedExceptionCode EtTest\Et\SampleException::CODE_ONE
	 */
	function test_exceptionSimpleFail(){
		$this->assertTrue(true);
	}

	/**
	 * Example of catching exception by method code
	 */
	function test_exceptionOther(){
		$this->setExpectedException('EtTest\Et\SampleException', "Hello world", SampleException::CODE_ONE);
		throw new SampleException("Hello world", SampleException::CODE_ONE);
	}

	/**
	 * Example of catching exception by method code -> test should fail
	 */
	function test_exceptionOtherFail(){
		$this->setExpectedException('EtTest\Et\SampleException', "Hello world", SampleException::CODE_ONE);
		$this->assertTrue(true);
	}

	/**
	 * Example of covers annotation
	 *
	 * @covers EtTest\Et\SampleException::getNewCode
	 */
	function test_getNewCode(){
		$exception = new SampleException("HELLO", SampleException::CODE_ONE);
		$this->assertEquals(20, $exception->getNewCode());
	}


	/**
	 * Data provider returns test samples array (test method arguments)
	 *
	 * Provider for @see test_DataProvider
	 * @return array
	 */
	function provider_dataProvider(){
		$samples = array(
			"Sample 1" => array("John", "Doe", "John Doe"),
			"Sample 2" => array("John", "NoDoe", "John NoDoe"),
			"Sample FAIL" => array("John", "Doe", "John NoDoe")
		);
		return $samples;
	}


	/**
	 * Arguments provided by @see provider_dataProvider
	 *
	 * @param $name
	 * @param $surname
	 * @param $expected_full_name
	 * @dataProvider provider_dataProvider
	 */
	function test_dataProvider($name, $surname, $expected_full_name){
		$this->assertEquals($expected_full_name, "{$name} {$surname}");
	}
}