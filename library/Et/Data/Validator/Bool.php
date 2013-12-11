<?php
namespace Et;
class Data_Validator_Bool extends Data_Validator_Scalar {
	/**
	 * @param mixed $value
	 * @return bool
	 */
	function formatValue($value) {
		return (bool)$value;
	}
}