<?php
namespace Et;
class Entity_Part_Multiple extends Entity_Part_Abstract implements \ArrayAccess,\Iterator,\Countable {

	/**
	 * @var bool
	 */
	protected $_is_parts_container = false;

	/**
	 * @var static[]|\Et\Entity_Part_Multiple[]
	 */
	protected $_items = array();

	/**
	 * @var static[]|\Et\Entity_Part_Multiple[]
	 */
	protected $__items_to_delete = array();

	/**
	 * @return \Et\Entity_Part_Multiple|static
	 */
	public static function getPartsContainerInstance(){
		/** @var $instance static|Entity_Part_Multiple */
		$instance = new static();

		$instance->_is_parts_container = true;

		return $instance;
	}

	/**
	 * @return bool
	 */
	public function isPartsContainer(){
		return $this->_is_parts_container;
	}

	/**
	 * @return bool|\Et\Entity_Part_Multiple|static
	 */
	public function current() {
		$ID = $this->key();
		if(!$ID){
			return false;
		}

		if(isset($this->_items[$ID])){
			return $this->_items[$ID];
		}

		$item = $this->getByID($ID);
		if(!$item){
			$this->next();
			return $this->current();
		}
		$this->_items[$ID] = $item;
		return $item;
	}


	public function next() {
		next($this->_items);
	}

	/**
	 * @return int|string|null
	 */
	public function key() {
		return key($this->_items);
	}

	/**
	 * @return bool
	 */
	public function valid() {
		return key($this->_items) !== null;
	}

	public function rewind() {
		reset($this->_items);
	}

	/**
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset) {
		return array_key_exists($offset, $this->_items);
	}

	/**
	 * @param mixed $offset
	 * @return bool|\Et\Entity_Part_Multiple|static
	 */
	public function offsetGet($offset) {
		if(!$this->offsetExists($offset)){
			return false;
		}

		if(isset($this->_items[$offset])){
			return $this->_items[$offset];
		}

		$this->_items[$offset] = static::getByID($offset);
		return $this->_items[$offset];
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		// TODO: Implement offsetSet() method.
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 * @return void
	 */
	public function offsetUnset($offset) {
		// TODO: Implement offsetUnset() method.
	}

	/**
	 * @return int
	 */
	public function count() {
		return count($this->_items);
	}
}