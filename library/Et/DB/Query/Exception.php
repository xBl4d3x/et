<?php
namespace Et;
class DB_Query_Exception extends Exception {

	const CODE_INVALID_COLUMN_NAME = 10;
	const CODE_INVALID_TABLE_NAME = 20;
	const CODE_INVALID_ORDER_BY_TYPE = 30;
	const CODE_INVALID_EXPRESSION = 40;
	const CODE_INVALID_OPERATOR = 50;
	const CODE_INVALID_VALUE = 60;
	const CODE_INVALID_JOIN_TYPE = 70;
	const CODE_UNRESOLVED_RELATIONS = 80;
	const CODE_INVALID_FETCH_TYPE = 90;
	const CODE_NOT_PERMITTED = 100;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_COLUMN_NAME => "Invalid column name",
		self::CODE_INVALID_TABLE_NAME => "Invalid table name",
		self::CODE_INVALID_ORDER_BY_TYPE => "Invalid order by type",
		self::CODE_INVALID_EXPRESSION => "Invalid expression",
		self::CODE_INVALID_OPERATOR => "Invalid operator",
		self::CODE_INVALID_VALUE => "Invalid value",
		self::CODE_INVALID_JOIN_TYPE => "Invalid join type",
		self::CODE_UNRESOLVED_RELATIONS => "Unresolved relations",
		self::CODE_INVALID_FETCH_TYPE => "Invalid fetch type",
		self::CODE_NOT_PERMITTED => "Not permitted"
	);
}