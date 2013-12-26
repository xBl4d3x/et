<?php
namespace Et;
class Data_Pagination_Exception extends  Exception {

	const CODE_INVALID_DATA_SOURCE = 10;
	const CODE_INVALID_FETCH_TYPE = 20;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_DATA_SOURCE => "Invalid data source",
		self::CODE_INVALID_FETCH_TYPE => "Invalid fetch type"
	);
}