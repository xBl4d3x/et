<?php
namespace Et;
class Entity_Key_Complex extends Entity_Key_Abstract {

	const DEFAULT_KEY_PARTS_DELIMITER = "|";

	const TYPE_SCALAR = "Scalar";
	const TYPE_INT = "Int";
	const TYPE_BOOL = "Bool";
	const TYPE_FLOAT = "Float";
	const TYPE_STRING = "String";
	const TYPE_LOCALE = "Locale";
	const TYPE_DATE = "Date";
	const TYPE_DATETIME = "DateTime";

	/**
	 * @var string
	 */
	protected $key_parts_delimiter = self::DEFAULT_KEY_PARTS_DELIMITER;

	/**
	 * @var string|Entity_Abstract
	 */
	protected $entity_class;

	/**
	 * @var string
	 */
	protected $entity_name;

	/**
	 * @var array
	 */
	protected $values = array();

	/**
	 * @var array
	 */
	protected $values_types;




}