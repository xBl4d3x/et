<?php
namespace Et;
/**
 * Representation of $_POST
 */
class Http_Request_Data_POST extends Http_Request_Data {

	/**
	 * @param array|null $data [optional]
	 */
	function __construct(array $data = null){
		if($data === null){
			$data = $_POST;
			if($data instanceof Data_Array){
				$data = $data->getData();
			}
		}
		parent::__construct($data);
	}

}