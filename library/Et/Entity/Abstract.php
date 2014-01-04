<?php
namespace Et;
abstract class Entity_Abstract extends Object {

	const COMPONENTS_TYPE = "entities";

	const DEF_TYPE = "type";
	const DEF_TITLE = "title";
	const DEF_DESCRIPTION = "description";
	const DEF_REQUIRED = "required";
	const DEF_VALIDATOR_TYPE = "validator_type";
	const DEF_FORM_FIELD_TYPE = "form_field_type";
	const DEF_ARRAY_VALUE_TYPE = "array_value_type";
	const DEF_ARRAY_KEY_TYPE = "array_key_type";
	const DEF_MINIMAL_VALUE = "minimal_value";
	const DEF_MAXIMAL_VALUE = "maximal_value";
	const DEF_MINIMAL_LENGTH = "minimal_length";
	const DEF_MAXIMAL_LENGTH = "maximal_length";
	const DEF_FORMAT = "format";
	const DEF_ERROR_MESSAGES = "error_messages";
	const DEF_RELATION_TYPE = "relation_type";
	const DEF_PART_TYPE = "part_type";
	const DEF_RELATED_ENTITY_CLASS = "related_entity_class";
	const DEF_ALLOWED_VALUES = "allowed_values";

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

	const ENTITY_ERROR = "_entity_error_";

	const ERR_ALREADY_EXISTS = "already_exists";
	const ERR_NOT_FOUND = "not_found";
	const ERR_REQUIRED = "required";
	const ERR_INVALID_FORMAT = "invalid_format";
	const ERR_INVALID_TYPE = "invalid_type";
	const ERR_INVALID_VALUE = "invalid_value";
	const ERR_OTHER = "other";
	const ERR_TOO_SHORT = "too_short";
	const ERR_TOO_LONG = "too_long";
	const ERR_TOO_LOW = "too_low";
	const ERR_TOO_HIGH = "too_high";


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
	 * @var Entity_Definition_Abstract[]
	 */
	protected static $__cached_definitions = array();

	/**
	 * @var array
	 */
	protected static $__checked_entities = array();

	/**
	 * @var DB_Adapter_Abstract
	 */
	protected static $__db;


	// DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION
	// DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION
	// DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION
	// DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION
	// DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION
	// DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION
	// DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION



	/**
	 * @var string
	 */
	protected static $_entity_name;

	/**
	 * @var string
	 */
	protected static $_entity_title = "";

	/**
	 * @var string
	 */
	protected static $_entity_description = "";

	/**
	 * @var string
	 */
	protected static $_entity_has_numeric_ID = false;

	/**
	 * @var array
	 */
	protected static $_entity_properties_definition = array();

	/**
	 * @var array
	 */
	protected static $_entity_unique_keys = array();

	/**
	 * @var array
	 */
	protected static $_entity_indexes = array();

	/**
	 * @var array
	 */
	protected static $_entity_error_messages = array(
		self::ERR_ALREADY_EXISTS => "Entity already exists",
		self::ERR_NOT_FOUND => "Entity not found",
	);

	/**
	 * @var array
	 */
	protected static $_default_properties_error_messages = array(
		self::ERR_REQUIRED => "Value may not be empty",
		self::ERR_NOT_FOUND => "Value not found",
		self::ERR_ALREADY_EXISTS => "Value already exists",
		self::ERR_INVALID_FORMAT => "Value has invalid format",
		self::ERR_INVALID_TYPE => "Invalid value type",
		self::ERR_INVALID_VALUE => "This value is not allowed",
		self::ERR_TOO_SHORT => "Value is too short (less than {MINIMAL_LENGTH} characters)",
		self::ERR_TOO_LONG => "Value is too long (more than {MAXIMAL_LENGTH} characters)",
		self::ERR_TOO_HIGH => "Value is too high (higher than {MAXIMAL_VALUE})",
		self::ERR_TOO_LOW => "Value is too low (lower than {MINIMAL_VALUE})",
		self::ERR_OTHER => "Value is not valid - {REASON}",
	);

	// DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION
	// DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION
	// DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION
	// DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION
	// DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION
	// DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION
	// DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION DEFINITION


	/**
	 * @var bool
	 */
	protected $_is_new = true;

	/**
	 * @var \Et\Data_Validation_Result|\Et\Data_Validation_Result_Error[]
	 */
	protected $__last_validation_result;




	/**
	 * @var string|int
	 */
	protected $ID;

	function __construct(){
		$this->checkEntity();

	}

	/**
	 * @throws Entity_Exception
	 */
	protected static function checkEntity(){
		if(isset(static::$__checked_entities[static::class])){
			return;
		}

		$class = static::class;

		if(!static::$_entity_name){
			throw new Entity_Exception(
				"Missing entity {$class} name definition - check {$class}::\$_entity_name",
				Entity_Exception::CODE_INVALID_DEFINITION
			);
		}

		static::$__checked_entities[static::class] = true;
	}

	/**
	 * @return int|string
	 */
	function getID(){
		return $this->ID;
	}

	function __sleep(){
		$properties = get_object_vars($this);
		$output = array();
		foreach($properties as $p => $v){
			if($p[0] == "_" && $p[1] == "_"){
				continue;
			}
			$output[] = $p;
		}
		return $output;
	}


	/**
	 * @return int|string
	 */
	protected function generateID(){
		if($this->hasNumericID()){
			return Entity::generateNumericID(static::class);
		} else {
			return Entity::generateTextID(static::class);
		}
	}

	/**
	 * @return bool
	 */
	function hasID(){
		if($this->hasNumericID()){
			return (int)$this->ID != 0;
		}
		return trim($this->ID) !== "";
	}


	/**
	 * @return string
	 */
	public static function hasNumericID() {
		return static::$_entity_has_numeric_ID;
	}


	/**
	 * @return bool
	 */
	public static function isMainEntity(){
		return false;
	}

	/**
	 * @return string
	 */
	public static function getEntityDescription() {
		return static::$_entity_description;
	}

	/**
	 * @return string
	 */
	public static function getEntityTitle() {
		return static::$_entity_title;
	}


	/**
	 * @return \Et\Entity_Definition_Abstract
	 */
	public static function getEntityDefinition(){
		if(isset(self::$__cached_definitions[static::class])){
			return self::$__cached_definitions[static::class];
		}

		$definition = System_Components::getComponent(static::COMPONENTS_TYPE, static::getClassID());
		if(!$definition instanceof Entity_Definition_Abstract){
			$definition = static::_createEntityDefinition();
		}

		self::$__cached_definitions[static::class] = $definition;
		return $definition;
	}

	/**
	 * @param array $where_properties_equal [optional]
	 * @return \Et\DB_Query
	 */
	public static function getQuery(array $where_properties_equal = array()){
		$query = DB_Query::getInstance(static::getEntityName());
		if($where_properties_equal){
			$query->getWhere()->addColumnsEqual($where_properties_equal, static::getEntityName());
		}
		return $query;
	}

	/**
	 * @param array $select_properties [optional] If empty, all properties are selected
	 * @param array $where_properties_equal [optional]
	 * @return \Et\DB_Query
	 */
	public static function getSelectQuery(array $select_properties = null, array $where_properties_equal = array()){
		$query = static::getQuery($where_properties_equal);

		if(!$select_properties){
			$select_properties = static::getEntityDefinition()->getPropertiesNames();
		}

		$query->selectColumns($select_properties, static::getEntityName());

		return $query;
	}

	/**
	 * @return Entity_Definition_Main|Entity_Definition_Part
	 */
	protected static function _createEntityDefinition(){
		if(static::isMainEntity()){
			return new Entity_Definition_Main(static::class, static::_createPropertyDefinitions());
		} else {
			return new Entity_Definition_Part(static::class, static::_createPropertyDefinitions());
		}
	}

	/**
	 * @return \Et\Entity_Property_Abstract[]
	 */
	protected static function _createPropertyDefinitions(){

		$class = new \ReflectionClass(static::class);
		$properties = $class->getProperties();
		$entity_properties = array();

		$definitions_tree = array();

		/** @var $parent_class string|\Et\Entity_Abstract */
		$parent_class = get_parent_class(static::class);
		while($parent_class && $parent_class != __CLASS__){

			$definitions_tree[$parent_class] = $parent_class::$_entity_properties_definition;

			$parent_class = get_parent_class($parent_class);
		}

		$definitions = array();
		while($definitions_tree){
			$def = array_pop($definitions_tree);
			if(!$def){
				continue;
			}

			foreach($def as $k => $v){
				if(!$v){
					continue;
				}
				if(!isset($definitions[$k])){
					$definitions[$k] = $v;
				} else {
					$definitions[$k] = array_merge($definitions[$k], $v);
				}
			}
		}

		foreach($properties as $ref){

			$property_name = $ref->getName();
			if($ref->isStatic() || $property_name[0] == "_"){
				continue;
			}

			$def = isset($definitions[$property_name])
					? $definitions[$property_name]
					: array();

			$entity_properties[$property_name] = static::_createPropertyDefinition($property_name, $def);

		}

		return $entity_properties;
	}


	/**
	 * @param string $property_name
	 * @param array $definition
	 * @throws Entity_Exception
	 * @return \Et\Entity_Property_Abstract
	 */
	protected static function _createPropertyDefinition($property_name, array $definition){

		if(!isset($definition[self::DEF_TITLE])){
			$definition[self::DEF_TITLE] = ucfirst(str_replace("_", " ", $property_name));
		}

		$full_name = static::class . "::\${$property_name}";

		if(!isset($definition[self::DEF_TYPE])){
			$type = static::_resolvePropertyType($property_name);
			if(!$type){
				throw new Entity_Exception(
					"Cannot determine type of property {$full_name} - please fill "	. static::class . "::\$_entity_definition['{$property_name}']",
					Entity_Exception::CODE_INVALID_DEFINITION
				);
			}
		} else {
			$type = $definition[self::DEF_TYPE];
			unset($definition[self::DEF_TYPE]);
		}

		$definition_class = "Et\\Entity_Property_{$type}";
		$property_class = Factory::getClassName($definition_class, "Et\\Entity_Property_Abstract");

		return new $property_class(static::class, $property_name, $definition);
	}

	/**
	 * @param string $property_name
	 * @return bool|string
	 */
	protected static function _resolvePropertyType($property_name){
		$value = static::${$property_name};

		if(strpos($property_name, "locale") !== false && !is_array($value)){
			return self::PROPERTY_TYPE_LOCALE;
		}

		if(is_string($value) || preg_match("~language|ID~", $property_name)){
			return self::PROPERTY_TYPE_STRING;
		}

		if(is_bool($value) || preg_match('~^(is_|has_|can_)~', $property_name)){
			return self::PROPERTY_TYPE_BOOL;
		}

		if(is_float($value)){
			return self::PROPERTY_TYPE_FLOAT;
		}

		if(is_int($value)){
			return self::PROPERTY_TYPE_INT;
		}

		if(is_array($value)){
			return self::PROPERTY_TYPE_ARRAY;
		}

		if(preg_match('~(_when|_datetime|_time)$~', $property_name)){
			return self::PROPERTY_TYPE_DATETIME;
		}

		if(preg_match('~_date$~', $property_name)){
			return self::PROPERTY_TYPE_DATE;
		}


		return false;
	}

	/**
	 * @return string
	 */
	public static function getEntityName() {
		return static::$_entity_name;
	}



	public static function installEntity(){

	}

	public static function uninstallEntity(){

	}

	/**
	 * @return \Et\DB_Adapter_Abstract
	 */
	public static function getDB() {
		return static::$__db
			? static::$__db
			: DB::get();
	}

	/**
	 * @param \Et\DB_Adapter_Abstract $_db
	 */
	public static function setDB(DB_Adapter_Abstract $_db) {
		static::$__db = $_db;
	}

	/**
	 * @return \Et\Data_Validation_Result|\Et\Data_Validation_Result_Error[]
	 */
	public function getLastValidationResult() {
		if(!$this->__last_validation_result){
			$this->validate();
		}
		return $this->__last_validation_result;
	}

	/**
	 * @param string $error_code
	 * @param array|null $error_message_data [optional]
	 * @param null|string $error_message [optional]
	 * @throws Entity_Exception
	 */
	function setEntityError($error_code, array $error_message_data = array(), $error_message = null){
		if(!$this->__last_validation_result){
			$this->__last_validation_result = new Data_Validation_Result();
		}

		if(!$error_message){
			if(!isset(static::$_entity_error_messages[$error_code])){
				throw new Entity_Exception(
					"Entity " . static::class . " has no error message with code '{$error_code}' defined, see " . static::class . "::\$_entity_error_messages",
					Entity_Exception::CODE_INVALID_ERROR_CODE
				);
			}
		}

		$this->__last_validation_result->setError(static::ENTITY_ERROR, $error_code, $error_message, $error_message_data);
	}


	function validate($force_revalidate = false){
		if($this->__last_validation_result && !$force_revalidate){
			return $this->__last_validation_result->isValid();
		}

		$this->__last_validation_result = new Data_Validation_Result();

		$properties = $this->getEntityDefinition()->getProperties();
		foreach($properties as $property => $definition){
			$res = $this->_validateProperty($properties, $this->{$property}, $definition);
			if($res instanceof Data_Validation_Result_Error){
				$this->__last_validation_result->setError($res);
			}
		}


		return $this->__last_validation_result->isValid();
	}

	protected function _validateProperty($property_name, $value, Entity_Property_Abstract $definition){
		return $definition->validate($value);
	}

	/**
	 * @param array $row
	 */
	protected static function formatRowFromDB(array &$row){
		$properties = static::getEntityDefinition()->getProperties();
		foreach($properties as $property => $definition){
			if(!isset($row[$property])){
				continue;
			}
			$row[$property] = $definition->formatValue($row[$property]);
		}
	}

	/**
	 * @param DB_Query $query
	 * @throws Entity_Exception
	 */
	protected static function checkQuery(DB_Query $query){
		if($query->getMainTableName() != static::getEntityName()){
			throw new Entity_Exception(
				"Cannot use query with main table {$query->getMainTableName()} within entity " . static::class . " - table " . static::getEntityName() . " is required",
				Entity_Exception::CODE_INVALID_QUERY
			);
		}
	}

	/**
	 * @param DB_Query $query
	 * @return bool|int|string
	 */
	protected static function fetchID(DB_Query $query){
		static::checkQuery($query);
		$query->select(array("ID"));
		$ID = static::getDB()->fetchValue($query);

		if(!$ID){
			return false;
		}

		if(static::hasNumericID()){
			$ID = (int)$ID;
		} else {
			$ID = (string)$ID;
		}

		return $ID;
	}

	/**
	 * @param DB_Query $query
	 * @return array
	 */
	protected static function fetchIDs(DB_Query $query){
		static::checkQuery($query);
		$query->select(array("ID"));
		$IDs = static::getDB()->fetchColumn($query);

		if(!$IDs){
			return array();
		}

		$has_numeric_ID = static::hasNumericID();
		foreach($IDs as $i => $ID){
			if($has_numeric_ID){
				$IDs[$i] = (int)$ID;
			} else {
				$IDs[$i] = (string)$ID;
			}
		}

		return $IDs;
	}

	/**
	 * @param DB_Query $query
	 * @return array|bool
	 */
	protected static function fetchRow(DB_Query $query){

		static::checkQuery($query);
		$row = static::getDB()->fetchRow($query);
		if(!$row){
			return false;
		}
		static::formatRowFromDB($row);

		return $row;
	}

	/**
	 * @param DB_Query $query
	 * @return array
	 */
	protected static function fetchRows(DB_Query $query){
		static::checkQuery($query);
		$rows = static::getDB()->fetchRows($query);
		if(!$rows){
			return array();
		}

		foreach($rows as $i => $row){
			static::formatRowFromDB($row);
			$rows[$i] = $row;
		}


		return $rows;
	}

	/**
	 * @param DB_Query $query
	 * @param null|string $key_property_name [optional]
	 * @return array
	 */
	protected static function fetchRowsAssociative(DB_Query $query, $key_property_name = null){
		static::checkQuery($query);

		$rows = static::getDB()->fetchRowsAssociative($query, array(), null, $key_property_name);
		if(!$rows){
			return array();
		}

		foreach($rows as $k => $row){
			static::formatRowFromDB($row);
			$rows[$k] = $row;
		}

		return $rows;
	}


	/**
	 * @param DB_Query $query
	 * @param null|string $property_name [optional]
	 * @return bool|mixed
	 * @throws Entity_Exception
	 */
	protected static function fetchValue(DB_Query $query, $property_name = null){
		static::checkQuery($query);
		$row = static::fetchRow($query);

		if(!$row){
			return false;
		}

		if(!$property_name){
			return array_shift($row);
		}

		if(!array_key_exists($property_name, $row)){
			throw new Entity_Exception(
				"Property '{$property_name}' not found in fetch result",
				Entity_Exception::CODE_INVALID_QUERY
			);
		}

		return $row[$property_name];
	}

	/**
	 * @param DB_Query $query
	 * @param null|string $property_name [optional]
	 * @return array
	 * @throws Entity_Exception
	 */
	protected static function fetchValues(DB_Query $query, $property_name = null){
		static::checkQuery($query);
		$rows = static::fetchRows($query);

		if(!$rows){
			return array();
		}

		$output = array();
		$checked = false;

		foreach($rows as $row){
			if($property_name === null){
				reset($row);
				$property_name = key($row);
			}

			if(!$checked){

				if(!array_key_exists($property_name, $row)){
					throw new Entity_Exception(
						"Property '{$property_name}' not found in fetch result",
						Entity_Exception::CODE_INVALID_QUERY
					);
				}

				$checked = true;
			}

			$output[] = $row[$property_name];
		}

		return $output;
	}

	/**
	 * @param DB_Query $query
	 * @return bool
	 */
	protected static function fetchEntityExists(DB_Query $query){
		static::checkQuery($query);
		return static::getDB()->fetchRowExists($query);
	}

	/**
	 * @param DB_Query $query
	 * @param bool $ignore_query_limit_and_offset [optional]
	 * @return int
	 */
	protected static function fetchEntitiesCount(DB_Query $query, $ignore_query_limit_and_offset = false){
		static::checkQuery($query);
		return static::getDB()->fetchRowsCount($query, array(), $ignore_query_limit_and_offset);
	}

	/**
	 * @param DB_Query $query
	 * @return bool|static|\Et\Entity_Main|\Et\Entity_Part_Single|\Et\Entity_Part_Multiple
	 */
	protected static function fetchEntity(DB_Query $query){

		$query->select(static::getEntityDefinition()->getPropertiesNames());
		$row = static::fetchRow($query);
		if(!$row){
			return false;
		}

		return static::initFromRowData($row);
	}

	/**
	 * @param DB_Query $query
	 * @return static[]|\Et\Entity_Main[]|\Et\Entity_Part_Single[]|\Et\Entity_Part_Multiple[]
	 */
	protected static function fetchEntities(DB_Query $query){

		$query->select(static::getEntityDefinition()->getPropertiesNames());
		$rows = static::fetchRows($query);
		if(!$rows){
			return array();
		}

		$output = array();
		foreach($rows as $row){
			$output[] = static::initFromRowData($row);
		}

		return $output;
	}

	/**
	 * @param DB_Query $query
	 * @param null|string $key_property [optional[
	 * @return static[]|\Et\Entity_Main[]|\Et\Entity_Part_Abstract[]
	 */
	protected static function fetchEntitiesAssociative(DB_Query $query, $key_property = null){

		$query->select(static::getEntityDefinition()->getPropertiesNames());
		$rows = static::fetchRowsAssociative($query, $key_property);
		if(!$rows){
			return array();
		}

		$output = array();
		foreach($rows as $k => $row){
			$output[$k] = static::initFromRowData($row);
		}

		return $output;
	}


	/**
	 * @param string|int $ID
	 * @return bool|static|\Et\Entity_Main|\Et\Entity_Part_Abstract
	 */
	public static function getByID($ID){
		if(static::hasNumericID()){
			$ID = (int)$ID;
		} else {
			$ID = (string)$ID;
		}

		$query = static::getQuery(array("ID" => $ID));
		return static::fetchEntity($query);
	}

	/**
	 * @param array $IDs
	 * @param DB_Query $query [optional]
	 * @return static[]|\Et\Entity_Main[]|\Et\Entity_Part_Abstract[]
	 */
	public static function getByIDs(array $IDs, DB_Query $query = null){
		if(!$IDs){
			return array();
		}

		$has_numeric_ID = static::hasNumericID();
		foreach($IDs as $i => $ID){
			if($has_numeric_ID){
				$IDs[$i] = (int)$ID;
			} else {
				$IDs[$i] = (string)$ID;
			}
		}


		if(!$query){
			$query = static::getQuery();
		}
		$query->getWhere()->addColumnCompare("ID", "IN", $IDs);

		return static::fetchEntitiesAssociative($query, "ID");
	}

	/**
	 * @param array $properties_values
	 * @param null|string $key_property [optional]
	 * @param DB_Query $query [optional]
	 * @return \Et\Entity_Main[]|\Et\Entity_Part_Abstract[]|static[]
	 */
	public static function getByPropertiesValues(array $properties_values, $key_property = null, DB_Query $query = null){
		if(!$query){
			$query = static::getQuery();
		}
		$query->getWhere()->addColumnsEqual($properties_values);

		if(!$key_property){
			$key_property = "ID";
		}

		return static::fetchEntitiesAssociative($query, $key_property);
	}


	/**
	 * @param string|int $ID
	 * @return bool
	 */
	public static function getIDExists($ID){

		if(static::hasNumericID()){
			$ID = (int)$ID;
		} else {
			$ID = (string)$ID;
		}

		$query = static::getQuery(array("ID" => $ID));
		return static::fetchID($query) !== false;
	}

	protected function _save(){
		$row = $this->getRowData();

		if($this->_is_new){
			if(!$row["ID"]){
				$row["ID"] = $this->generateID();
			}
			static::getDB()->insert(static::getEntityName(), $row);
			$this->ID = $row["ID"];
		} else {
			static::getDB()->update(static::getEntityName(), $row, static::getQuery(array("ID" => $row["ID"])));
		}


		return true;
	}

	/**
	 * @return array
	 */
	protected function getRowData(){
		$row = array();
		$definitions = static::getEntityDefinition()->getProperties();

		foreach($definitions as $property => $definition){
			$row[$property] = $this->{$property};
		}

		return $row;
	}

	/**
	 * @param array $row
	 * @return static|static|\Et\Entity_Main|\Et\Entity_Part_Single|\Et\Entity_Part_Multiple
	 */
	protected function initFromRowData(array $row){
		/** @var $entity Entity_Abstract */
		$entity = new static();
		foreach($row as $k => $v){
			$entity->{$k} = $v;
		}
		$entity->_is_new = false;
		return $entity;
	}

}