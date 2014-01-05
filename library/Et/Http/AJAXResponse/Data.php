<?php
namespace Et;
class Http_AJAXResponse_Data extends Http_AJAXResponse_Abstract implements \ArrayAccess,\Countable,\Iterator {

	/**
	 * @var \Et\Data_Array
	 */
	protected $data;

	/**
	 * @param null|array|\Et\Data_Array $data [optional]
	 */
	function __construct($data = null){
		parent::__construct();

		if(!$data instanceof Data_Array){
			$data = new Data_Array($data);
		}
		$this->data = $data;
	}



	/**
	 * @param null|array|\Et\Data_Array $data [optional]
	 * @return static|\Et\Http_AJAXResponse_Data
	 */
	public static function getInstance($data = null){
		return new static($data);
	}


	/**
	 * @return \Et\Data_Array
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * @return bool|mixed
	 */
	public function current() {
		return $this->data->current();
	}

	public function next() {
		$this->data->next();
	}

	/**
	 * @return mixed|null
	 */
	public function key() {
		return $this->data->key();
	}

	/**
	 * @return bool
	 */
	public function valid() {
		return $this->data->valid();
	}

	public function rewind() {
		$this->data->rewind();
	}

	/**
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset) {
		return $this->data->getValueExists($offset);
	}

	/**
	 * @param mixed $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return $this->data->getValue($offset);
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value) {
		$this->data->setValue($offset, $value);
	}

	public function offsetUnset($offset) {
		$this->data->removeValue($offset);
	}

	/**
	 * @return int
	 */
	public function count() {
		return $this->data->count();
	}
}