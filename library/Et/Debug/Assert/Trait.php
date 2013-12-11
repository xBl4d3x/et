<?php
namespace Et;
trait Debug_Assert_Trait {

	/**
	 * @var Debug_Assert
	 */
	protected static $__assert;

	/**
	 * @param Debug_Assert $_assert
	 */
	public static function __setAssert(Debug_Assert $_assert) {
		static::$__assert = $_assert;
	}

	/**
	 * @return Debug_Assert|null
	 */
	public static function __getAssert() {
		return static::$__assert;
	}

	/**
	 * @return Debug_Assert
	 */
	public static function assert(){
		if(!static::$__assert){
			et_require('Debug_Assert');
			static::$__assert = Debug_Assert::getInstance();
		}
		return static::$__assert;
	}
}
