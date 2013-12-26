<?php
namespace Et;
class Data_Pagination_Default extends  Data_Pagination_Abstract {

	const FETCH_VALUES = "fetch_values";
	const FETCH_ASSOCIATIVE = "fetch_associative";

	/**
	 * @var array
	 */
	protected static $_allowed_fetch_types = array(
		self::FETCH_VALUES,
		self::FETCH_ASSOCIATIVE,
	);

	/**
	 * @var array
	 */
	protected $items_data = array();

	/**
	 * @var array
	 */
	protected $items_data_keys = array();

	/**
	 * @var string
	 */
	protected $fetch_type = self::FETCH_ASSOCIATIVE;

	/**
	 * @param array|Data_Array|\Iterator $items_data [optional]
	 * @param null|string $fetch_type [optional]
	 */
	function __construct($items_data = null, $fetch_type = null){
		if($items_data){
			$this->setItemsData($items_data);
		}
		if(!$fetch_type){
			$fetch_type = static::getDefaultFetchType();
		}
		$this->setFetchType($fetch_type);
	}

	/**
	 * @return string
	 */
	public static function getDefaultFetchType(){
		return static::FETCH_ASSOCIATIVE;
	}


	/**
	 * @param string $fetch_type
	 * @throws Data_Pagination_Exception
	 */
	protected function checkFetchType($fetch_type){
		if(!in_array($fetch_type, static::$_allowed_fetch_types)){
			throw new Data_Pagination_Exception(
				"Invalid fetch type '{$fetch_type}' used - allowed types: '" . implode("', '", static::$_allowed_fetch_types) . "'",
				Data_Pagination_Exception::CODE_INVALID_FETCH_TYPE
			);
		}
	}

	/**
	 * @param string $fetch_type
	 */
	public function setFetchType($fetch_type) {
		$this->checkFetchType($fetch_type);
		if($fetch_type != $this->fetch_type){
			$this->items = array();
		}
		$this->fetch_type = $fetch_type;
	}

	/**
	 * @return string
	 */
	public function getFetchType() {
		return $this->fetch_type;
	}

	/**
	 * @return array
	 */
	public function getItemsData() {
		return $this->items_data;
	}

	/**
	 * @return array
	 */
	public function getItemsDataKeys() {
		return $this->items_data_keys;
	}

	/**
	 * @param array|Data_Array|\Iterator $items_data
	 * @param bool $merge [optional]
	 * @throws Data_Pagination_Exception
	 */
	function setItemsData($items_data, $merge = true){
		if(!$merge){
			$this->items_data = array();
		}

		if($items_data instanceof Data_Array){
			$this->items_data = array_merge($this->items_data, $items_data->getData());

		} elseif($items_data instanceof \Iterator){

			foreach($items_data as $k => $v){
				$this->items_data[$k] = $v;;
			}

		}

		if(!is_array($items_data)){
			throw new Data_Pagination_Exception(
				"Invalid items data - must be an array, instance of Et\\Data_Array or iterator",
				Data_Pagination_Exception::CODE_INVALID_DATA_SOURCE
			);
		}

		$this->items_data = array_merge($this->items_data, $items_data);
		$this->items_data_keys = array_keys($this->items_data);
		$this->refresh();

	}



	/**
	 * @return int
	 */
	protected function fetchItemsCount() {
		return count($this->items_data);
	}

	/**
	 * @param int $page
	 * @return array
	 */
	protected function fetchItems($page) {
		$offset_from = $this->calculateOffsetFrom($page, $this->getItemsPerPage(), $this->getItemsCount());
		$offset_to = $this->calculateOffsetTo($page, $this->getItemsPerPage(), $this->getItemsCount());

		$output = array();
		$i = 0;
		for($idx = $offset_from; $idx <= $offset_to; $idx++){
			if(!isset($this->items_data_keys[$idx])){
				continue;
			}
			$key = $this->items_data_keys[$idx];
			if($this->fetch_type == static::FETCH_VALUES){
				$output[$i++] = $this->items_data[$key];
			} else {
				$output[$key] = $this->items_data[$key];
			}
		}

		return $output;
	}
}