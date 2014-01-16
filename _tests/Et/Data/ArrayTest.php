<?php
namespace EtTest\Et;
use EtTest;
use Et;


class Data_ArrayTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Et\Data_Array
	 */
	protected $array;

	protected $default_data = array(
		"k_string" => "Hello world",
		"k_int" => 10,
		"k_float" => 10.5,
		"k_bool" => true,
		"k_array" => array(
			"k_k_string" => "EHLO",
			"k_k_int" => 20,
			"k_k_float" => 20.5,
			"k_k_bool" => false,
		)
	);


	protected function setUp() {
		$this->array = new Et\Data_Array($this->default_data);
		parent::setUp();
	}

	function test_dataHandling(){
		$this->assertEquals($this->default_data, $this->array->getData());

	}
}