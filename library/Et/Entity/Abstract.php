<?php
namespace Et;
abstract class Entity_Abstract extends Object {

	const DEF_TYPE = "type";
	const DEF_NAME = "name";
	const DEF_DESCRIPTION = "description";
	const DEF_REQUIRED = "required";
	const DEF_ARRAY_VALUE_TYPE = "array_value_type";
	const DEF_MIN_VALUE = "min_value";
	const DEF_MAX_VALUE = "max_value";
	const DEF_MIN_LENGTH = "min_length";
	const DEF_MAX_LENGTH = "max_length";
	const DEF_FORMAT = "format";
	const DEF_RELATION_TYPE = "relation_type";
	const DEF_PART_TYPE = "part_type";
	const DEF_ENTITY_CLASS = "entity_class";


	const PROPERTY_TYPE_INT = "Int";
	const PROPERTY_TYPE_BOOL = "Bool";
	const PROPERTY_TYPE_FLOAT = "Float";
	const PROPERTY_TYPE_ARRAY = "Array";
	const PROPERTY_TYPE_STRING = "String";
	const PROPERTY_TYPE_LOCALE = "Locale";
	const PROPERTY_TYPE_DATE = "Date";
	const PROPERTY_TYPE_DATETIME = "DateTime";
	const PROPERTY_TYPE_RELATION = "Relation";
	const PROPERTY_TYPE_PART = "Part";


	const KEY_TYPE_UNIQUE = "unique";
	const KEY_TYPE_INDEX = "index";


	const ID_TYPE_NUMBER = "number";
	const ID_TYPE_STRING = "string";


	const STRING_LENGTH_SHORT_ID = 32;
	const STRING_LENGTH_ID = 40;
	const STRING_LENGTH_SHORT = 255;
	const STRING_LENGTH_MEDIUM = 65535;
	const STRING_LENGTH_LONG = 2147483647;

	const DEFAULT_STRING_LENGTH = self::STRING_LENGTH_SHORT;


	const PART_TYPE_SINGLE = "Single";
	const PART_TYPE_MULTIPLE = "Multiple";


	const RELATION_TYPE_1_TO_1 = "1to1";
	const RELATION_TYPE_1_TO_N = "1toN";
	const RELATION_TYPE_M_TO_N = "MtoN";


	/**
	 * @var array
	 */
	protected static $__cached_definitions = array();

	/**
	 * @var DB_Adapter_Abstract
	 */
	protected static $__db;


	/**
	 * @var string
	 */
	protected static $_entity_name;


	protected static $_entity_definition;

	/**
	 * @var array
	 */
	protected static $_entity_unique_keys = array();

	/**
	 * @var array
	 */
	protected static $_entity_indexes = array();

	protected static $_relations;



	protected $_ID;


	/**
	 * @return bool
	 */
	public static function isMainEntity(){
		return false;
	}

	/**
	 * @return string
	 */
	public static function getEntityName() {
		return static::$_entity_name;
	}

	/**
	 * @param \Et\Entity_Key_Abstract|string|int|array $ID
	 * @return bool
	 */
	public static function getIDExists($ID){
		if(!$ID instanceof Entity_Key_Abstract){
			$ID = static::getEmptyIDInstance()->setValue($ID);
		} else {
			static::_checkKey($ID);
		}

		//todo: implement
		return false;
	}

	/**
	 * @return Entity_Key_Abstract
	 */
	public static function getEmptyIDInstance(){
		return new Entity_Key_String(static::class, "ID");
	}


	/**
	 * @param Entity_Key_Abstract $key
	 * @return bool
	 */
	public static function getKeyExists(Entity_Key_Abstract $key){
		static::_checkKey($key);
		//todo: implement
		return false;
	}

	/**
	 * @param Entity_Key_Abstract $key
	 * @throws Entity_Exception
	 */
	protected static function _checkKey(Entity_Key_Abstract $key){
		if($key->getEntityClass() != static::class){
			throw new Entity_Exception(
				"Cannot use key for entity '{$key->getEntityClass()}' in '" . static::class . "' queries",
				Entity_Exception::CODE_INVALID_KEY
			);
		}
	}
}