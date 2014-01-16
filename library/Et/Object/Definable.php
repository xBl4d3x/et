<?php
namespace Et;
et_require("Object");
abstract class Object_Definable extends Object {

	const ERR_NOT_DEFINED = "not_defined";
	const ERR_REQUIRED = "required";
	const ERR_INVALID_FORMAT = "invalid_format";
	const ERR_INVALID_TYPE = "invalid_type";
	const ERR_NOT_ALLOWED_VALUE = "not_allowed_value";
	const ERR_OTHER = "other";
	const ERR_TOO_SHORT = "too_short";
	const ERR_TOO_LONG = "too_long";
	const ERR_TOO_LOW = "too_low";
	const ERR_TOO_HIGH = "too_high";

	const DEF_TYPE = "type";
	const DEF_TITLE = "title";
	const DEF_DESCRIPTION = "description";
	const DEF_REQUIRED = "required";
	const DEF_ARRAY_KEY_DEFINITION = "array_key_definition";
	const DEF_ARRAY_VALUE_DEFINITION = "array_value_definition";
	const DEF_MINIMAL_VALUE = "minimal_value";
	const DEF_MAXIMAL_VALUE = "maximal_value";
	const DEF_MINIMAL_LENGTH = "minimal_length";
	const DEF_MAXIMAL_LENGTH = "maximal_length";
	const DEF_ALLOWED_VALUES = "allowed_values";
	const DEF_FORMAT = "format";

	const TYPE_BOOL = "Bool";
	const TYPE_INT = "Int";
	const TYPE_STRING = "String";
	const TYPE_LOCALE = "Locale";
	const TYPE_DATE = "Date";
	const TYPE_DATETIME = "DateTime";
	const TYPE_FLOAT = "Float";
	const TYPE_ARRAY = "Array";


	protected static $__cached_definitions = array();


	/**
	 * @var array
	 */
	protected static $_supported_properties_types = array(
		self::TYPE_BOOL => "Boolean",
		self::TYPE_INT  => "Integer",
		self::TYPE_STRING  => "Integer",
		self::TYPE_FLOAT  => "Integer",
		self::TYPE_LOCALE => "Locale",
		self::TYPE_DATE => "Date",
		self::TYPE_DATETIME => "Date and time",
		self::TYPE_ARRAY => "Array",
	);

	protected static $_definition = array();


}