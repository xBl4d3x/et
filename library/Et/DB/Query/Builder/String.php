<?php
namespace Et;
class DB_Query_Builder_String extends DB_Query_Builder_Abstract {

	/**
	 * @param string $identifier
	 * @return string
	 */
	function quoteIdentifier($identifier) {
		return (string)$identifier;
	}

	/**
	 * @param mixed $value
	 * @throws DB_Adapter_Exception
	 * @return string
	 */
	function quoteValue($value) {
		switch(true){
			// string
			case is_string($value):
				return "'" . addslashes($value) . "'";


			// numbers
			case is_numeric($value):
				if(is_int($value)){
					return (int)$value;
				}
				return (float)$value;

			// boolean
			case is_bool($value):
				return (int)$value;

			// NULL
			case $value === null:
				return "NULL";

			// DB expression
			case $value instanceof DB_Expression:
				return (string)$value;

			// table column
			case $value instanceof DB_Table_Column:
				return $this->quoteIdentifier((string)$value);

			// date and time
			case $value instanceof \DateTime:
				if($value instanceof Locales_Date){
					return $this->quoteDate($value);
				}
				return $this->quoteDateTime($value);

			// locale
			case $value instanceof Locales_Locale:
				return "'" . (string)$value . "'";

			// timezone
			case $value instanceof \DateTimeZone:
				return "'" . addslashes($value->getName()) . "'";

			// array
			case is_array($value):
				return $this->quoteJSON($value);
		}

		throw new DB_Adapter_Exception(
			'Invalid value to quote for DB',
			DB_Adapter_Exception::CODE_QUOTE_FAILED
		);

	}



	/**
	 * @param mixed $value
	 * @return string
	 */
	public function quoteJSON($value){
		return "'" . addslashes(json_encode(
			$value,
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		)) . "'";
	}


	/**
	 * @param null|string|int|\DateTime|\Et\Locales_DateTime $date [optional]
	 * @return string
	 */
	public function quoteDate($date){
		if(!$date){
			return "''";
		}

		if(!$date instanceof \DateTime){
			$date = Locales::getDate($date);
		}

		return "'{$date->format("Y-m-d")}'";
	}

	/**
	 * @param null|string|int|\DateTime|\Et\Locales_DateTime $datetime [optional]
	 * @return string
	 */
	public function quoteDateTime($datetime){
		if(!$datetime){
			return "''";
		}

		if(!$datetime instanceof \DateTime){
			$datetime = Locales::getDateTime($datetime);
		}

		return "'{$datetime->format("Y-m-d H:i:s")}'";
	}



}