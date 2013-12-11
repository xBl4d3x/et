<?php
namespace Et;
et_require("Data_Array");
class Data_Array_Reference extends Data_Array {

	/**
	 * Create array wrapper
	 *
	 * @param array|null $data [optional]
	 */
	public function __construct(array &$data = null) {
		if($data !== null){
			$this->data = &$data;
		}
	}
}