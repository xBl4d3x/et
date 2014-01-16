<?php
namespace EtTest\Et;
use EtTest;
use Et;

class ObjectTest_TestingObject extends Et\Object {

	protected static $visible_static_var1 = "static1";
	protected static $visible_static_var2 = "static2";

	/**
	 * @var string
	 */
	protected $visible_var1 = "Test";

	/**
	 * @var int
	 */
	protected $visible_var2 = 10;

	/**
	 * @var string
	 */
	protected $_protected_var = "protected";

	/**
	 * @var string
	 */
	protected $__hidden_var = "hidden";

	/**
	 * @param $var1
	 * @param $var2
	 */
	function __construct($var1, $var2){
		$this->visible_var1 = $var1;
		$this->visible_var2 = $var2;
	}

	/**
	 * @param string $visible_var1
	 */
	public function setVisibleVar1($visible_var1) {
		$this->visible_var1 = $visible_var1;
	}

	/**
	 * @return string
	 */
	public function getVisibleVar1() {
		return $this->visible_var1;
	}

	/**
	 * @param int $visible_var2
	 */
	public function setVisibleVar2($visible_var2) {
		$this->visible_var2 = $visible_var2;
	}

	/**
	 * @return int
	 */
	public function getVisibleVar2() {
		return $this->visible_var2;
	}

	/**
	 * @param string $protected_var
	 */
	public function setProtectedVar($protected_var) {
		$this->_protected_var = $protected_var;
	}

	/**
	 * @return string
	 */
	public function getProtectedVar() {
		return $this->_protected_var;
	}

	/**
	 * @param string $_hidden_var
	 */
	public function setHiddenVar($_hidden_var) {
		$this->__hidden_var = $_hidden_var;
	}

	/**
	 * @return string
	 */
	public function getHiddenVar() {
		return $this->__hidden_var;
	}



}