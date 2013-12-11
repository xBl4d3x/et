<?php
namespace Et;
abstract class Debug_VarDump_Abstract {

	const VAR_TYPE_BOOL = "boolean";
	const VAR_TYPE_INT = "integer";
	const VAR_TYPE_FLOAT = "double";
	const VAR_TYPE_NULL = "NULL";
	const VAR_TYPE_STRING = "string";
	const VAR_TYPE_OBJECT = "object";
	const VAR_TYPE_ARRAY = "array";
	const VAR_TYPE_RESOURCE = "resource";

	const DEFAULT_MAX_DEPTH = 5;

	/**
	 * @var int
	 */
	protected $max_depth = self::DEFAULT_MAX_DEPTH;

	/**
	 * @var int
	 */
	protected $max_text_length = 0;

	/**
	 * @param null|int $max_depth [optional]
	 * @param null|int $max_text_length [optional]
	 */
	function __construct($max_depth = null, $max_text_length = null){
		if($max_depth !== null){
			$this->setMaxDepth($max_depth);
		}
		if($max_text_length !== null){
			$this->setMaxTextLength($max_text_length);
		}
	}

	/**
	 * @param object $object
	 * @return string
	 */
	protected function getObjectID($object){
		$class = get_class($object);
		$ID = sprintf("%u", crc32("{$class}#" . spl_object_hash($object)));
		return "{$class}#{$ID}";
	}

	/**
	 * @param int $max_depth
	 */
	public function setMaxDepth($max_depth) {
		$this->max_depth = max(0, (int)$max_depth);
	}

	/**
	 * @return int
	 */
	public function getMaxDepth() {
		return $this->max_depth;
	}

	/**
	 * @param int $max_text_length
	 */
	public function setMaxTextLength($max_text_length) {
		$this->max_text_length = max(0, (int)$max_text_length);
	}

	/**
	 * @return int
	 */
	public function getMaxTextLength() {
		return $this->max_text_length;
	}

	/**
	 * @param mixed $variable
	 */
	public function dump($variable){
		echo $this->getDump($variable);
	}

	/**
	 * @param mixed $variable
	 * @return string
	 */
	abstract public function getDump($variable);

	/**
	 * @param string $variable
	 * @return string
	 */
	function __invoke($variable){
		return $this->getDump($variable);
	}

}