<?php
namespace Et;
et_require("Object");
class Data_Validator extends Object {

	const TYPE_ARRAY = "Array";
	const TYPE_BOOL = "Bool";
	const TYPE_DATE = "Date";
	const TYPE_DATETIME = "DateTime";
	const TYPE_EMAIL = "Email";
	const TYPE_FLOAT = "Float";
	const TYPE_INT = "Int";
	const TYPE_LOCALE = "Locale";
	const TYPE_SCALAR = "Scalar";
	const TYPE_STRING = "String";

	const DEF_TYPE = "type";
	const DEF_ALLOW_EMPTY_VALUE = "allow_empty_value";
	const DEF_ALLOWED_VALUES = "allowed_values";
	const DEF_VALIDATION_PATTERN = "validation_pattern";
	const DEF_VALIDATION_CALLBACK = "validation_callback";
	const DEF_ERROR_MESSAGES = "error_messages";
	const DEF_MINIMAL_VALUE = "minimal_value";
	const DEF_MAXIMAL_VALUE = "maximal_value";
	const DEF_MINIMAL_ITEMS_COUNT = "minimal_items_count";
	const DEF_MAXIMAL_ITEMS_COUNT = "maximal_items_count";
	const DEF_KEY_VALIDATOR = "key_validator";
	const DEF_VALUE_VALIDATOR = "value_validator";
	const DEF_MINIMAL_DATE = "minimal_date";
	const DEF_MAXIMAL_DATE = "maximal_date";
	const DEF_MINIMAL_DATETIME = "minimal_datetime";
	const DEF_MAXIMAL_DATETIME = "maximal_datetime";
	const DEF_CHECK_MX_RECORD = "check_mx_record";
	const DEF_PRECISION = "precision";
	const DEF_MINIMAL_LENGTH = "minimal_length";
	const DEF_MAXIMAL_LENGTH = "maximal_length";



	/**
	 * @param string $validator_type
	 * @return string
	 */
	public static function getValidatorClassName($validator_type){
		return Factory::getClassName('Et\Data_Validator_' . $validator_type, 'Et\Data_Validator_Abstract');
	}

	/**
	 * @param string $validator_type
	 * @param array $validator_parameters [optional]
	 * @return Data_Validator_Abstract
	 */
	public static function getValidatorInstance($validator_type, array $validator_parameters = array()){
		$real_class_name = static::getValidatorClassName($validator_type);
		return new $real_class_name($validator_parameters);
	}

}