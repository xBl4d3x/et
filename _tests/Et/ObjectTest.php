<?php
namespace EtTest\Et;
use EtTest;
use Et;

require_once (__DIR__ . "/ObjectTest/TestingObject.php");

class ObjectTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var ObjectTest_TestingObject
	 */
	protected $object;


	protected function setUp() {
		$this->object = new ObjectTest_TestingObject("Hello", "World");
		parent::setUp();
	}

	/**
	 * @covers Et\Object::getInstanceWithoutConstructor()
	 */
	function test_getInstanceWithoutConstructor(){
		$this->assertEquals("Hello", $this->object->getVisibleVar1());
		$this->assertEquals("World", $this->object->getVisibleVar2());

		$instance = ObjectTest_TestingObject::getInstanceWithoutConstructor();
		$this->assertInstanceOf("EtTest\\Et\\ObjectTest_TestingObject", $instance);
		$this->assertEquals("Test", $instance->getVisibleVar1());
		$this->assertEquals(10, $instance->getVisibleVar2());
	}

	/**
	 * @covers Et\Object::className()
	 * @covers Et\Object::getClassID()
	 * @covers Et\Object::getClassNameWithoutNamespace()
	 */
	function test_classNameMethods(){
		$this->assertEquals("EtTest\\Et\\ObjectTest_TestingObject", ObjectTest_TestingObject::className());
		$this->assertEquals("ObjectTest_TestingObject", ObjectTest_TestingObject::getClassNameWithoutNamespace());
		$this->assertEquals("ettest_et_objecttest_testingobject", ObjectTest_TestingObject::getClassID());
	}

	/**
	 * @covers Et\Object::getSetterMethodName()
	 */
	function test_getSetterMethodName(){
		$this->assertEquals("setvisiblevar1", $this->object->getSetterMethodName("visible_var1"));
		$this->assertFalse($this->object->getSetterMethodName("visible_var10"));
	}

	/**
	 * @param string|object $object_or_class
	 * @param string $method
	 * @param array $arguments
	 * @return mixed
	 */
	protected function _callProtectedMethod($object_or_class, $method, array $arguments = array()){
		$method = new \ReflectionMethod($object_or_class, $method);
		$method->setAccessible(true);
		return $method->invokeArgs(is_object($object_or_class) ? $object_or_class : null, $arguments);
	}

	/**
	 * @covers Et\Object::_getVisiblePropertiesNames()
	 * @covers Et\Object::_getVisiblePropertiesValues()
	 * @covers Et\Object::_getVisibleClassPropertiesNames()
	 * @covers Et\Object::_getVisibleClassPropertiesValues()
	 */
	function test_VisiblePropertiesTrait(){
		$this->assertEquals(
			array(
				"visible_var1",
				"visible_var2"
			),
			$this->_callProtectedMethod($this->object, "_getVisiblePropertiesNames")
		);

		$this->assertEquals(
			array(
				"visible_var1" => "Hello",
				"visible_var2" => "World"
			),
			$this->_callProtectedMethod($this->object, "_getVisiblePropertiesValues")
		);

		$this->assertEquals(
			array(
				"visible_var1",
				"visible_var2",
				"visible_static_var1",
				"visible_static_var2",
			),
			$this->_callProtectedMethod("EtTest\\Et\\ObjectTest_TestingObject", "_getVisibleClassPropertiesNames")
		);

		$this->assertEquals(
			array(
				"visible_var1" => "Test",
				"visible_var2" => 10,
				"visible_static_var1" => "static1",
				"visible_static_var2" => "static2"
			),
			$this->_callProtectedMethod("EtTest\\Et\\ObjectTest_TestingObject", "_getVisibleClassPropertiesValues")
		);

		$this->assertTrue($this->_callProtectedMethod($this->object, "_hasVisibleProperty", array("visible_var1")));
		$this->assertFalse($this->_callProtectedMethod($this->object, "_hasVisibleProperty", array("visible_var10")));
		$this->assertFalse($this->_callProtectedMethod($this->object, "_hasVisibleProperty", array("visible_static_var1")));

		$this->assertTrue($this->_callProtectedMethod("EtTest\\Et\\ObjectTest_TestingObject", "_hasVisibleClassProperty", array("visible_var1")));
		$this->assertFalse($this->_callProtectedMethod("EtTest\\Et\\ObjectTest_TestingObject", "_hasVisibleClassProperty", array("visible_var10")));
		$this->assertTrue($this->_callProtectedMethod("EtTest\\Et\\ObjectTest_TestingObject", "_hasVisibleClassProperty", array("visible_static_var1")));
	}

	/**
	 * @covers Et\Object::__sleep()
	 */
	function test_MagicSleepTrait(){

		$this->assertEquals("Hello", $this->object->getVisibleVar1());
		$this->assertEquals("protected", $this->object->getProtectedVar());
		$this->assertEquals("hidden", $this->object->getHiddenVar());

		$this->object->setProtectedVar("prot");
		$this->object->setHiddenVar("hid");
		$this->object->setVisibleVar1("vis");

		/** @var $object ObjectTest_TestingObject */
		$object = unserialize(serialize($this->object));
		$this->assertEquals("vis", $object->getVisibleVar1());
		$this->assertEquals("prot", $object->getProtectedVar());
		$this->assertEquals("hidden", $object->getHiddenVar());

	}

	/**
	 * @covers Et\Object::__get()
	 * @expectedException Et\Object_Exception
	 * @expectedExceptionCode Et\Object_Exception::CODE_UNKNOWN_PROPERTY_ACCESS
	 * @expectedExceptionMessage Property EtTest\Et\ObjectTest_TestingObject->unknown_property does not exist
	 */
	function test_MagicGetTrait_notExistingProperty(){
		$this->object->unknown_property;
	}

	/**
	 * @covers Et\Object::__get()
	 * @expectedException Et\Object_Exception
	 * @expectedExceptionCode Et\Object_Exception::CODE_PROTECTED_PROPERTY_ACCESS
	 * @expectedExceptionMessage Cannot get EtTest\Et\ObjectTest_TestingObject->visible_var1 property value - permission denied
	 */
	function test_MagicGetTrait_existingProperty(){
		$this->object->visible_var1;
	}

	/**
	 * @covers Et\Object::__get()
	 * @expectedException Et\Object_Exception
	 * @expectedExceptionCode Et\Object_Exception::CODE_UNKNOWN_PROPERTY_ACCESS
	 * @expectedExceptionMessage Property EtTest\Et\ObjectTest_TestingObject->unknown_property does not exist
	 */
	function test_MagicSetTrait_notExistingProperty(){
		$this->object->unknown_property = 10;
	}

	/**
	 * @covers Et\Object::__get()
	 * @expectedException Et\Object_Exception
	 * @expectedExceptionCode Et\Object_Exception::CODE_PROTECTED_PROPERTY_ACCESS
	 * @expectedExceptionMessage Cannot set EtTest\Et\ObjectTest_TestingObject->visible_var1 property value - permission denied
	 */
	function test_MagicSetTrait_existingProperty(){
		$this->object->visible_var1 = 10;
	}

	/**
	 * @covers Et\Object::__get()
	 * @expectedException Et\Object_Exception
	 * @expectedExceptionCode Et\Object_Exception::CODE_UNKNOWN_PROPERTY_ACCESS
	 * @expectedExceptionMessage Property EtTest\Et\ObjectTest_TestingObject->unknown_property does not exist
	 */
	function test_MagicUnsetTrait_notExistingProperty(){
		unset($this->object->unknown_property);
	}

	/**
	 * @covers Et\Object::__get()
	 * @expectedException Et\Object_Exception
	 * @expectedExceptionCode Et\Object_Exception::CODE_PROTECTED_PROPERTY_ACCESS
	 * @expectedExceptionMessage Cannot remove EtTest\Et\ObjectTest_TestingObject->visible_var1 property value - permission denied
	 */
	function test_MagicUnsetTrait_existingProperty(){
		unset($this->object->visible_var1);
	}
}
