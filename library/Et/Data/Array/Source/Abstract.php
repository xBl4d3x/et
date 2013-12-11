<?php
namespace Et;
et_require("Object");
abstract class Data_Array_Source_Abstract extends Object {

	/**
	 * @return array
	 * @throws Data_Array_Source_Exception
	 */
	abstract function loadData();

	/**
	 * @param \Et\Data_Array $data
	 * @throws Data_Array_Source_Exception
	 */
	abstract function storeData(Data_Array $data);

	/**
	 * @return bool
	 */
	abstract function isReadable();

	/**
	 * @return bool
	 */
	abstract function isWritable();
}
