<?php
namespace Et;
et_require("Locales_DateTime");
class Locales_Date extends Locales_DateTime {
	
	const FORMAT_DEFAULT = self::FORMAT_DATE;
	const FORMAT_DEFAULT_WITH_TZ = self::FORMAT_DATE_WITH_TZ;
	const FORMAT_MYSQL = 'Y-m-d';
	const FORMAT_MYSQL_SHORT = 'Ymd';

	/**
	 * @param null|string|int|\DateTime $date [optional]
	 * @param null|string|\DateTimeZone|\Et\Locales_Timezone $timezone [optional]
	 *
	 * @throws Locales_Exception
	 */
	function __construct($date = null, $timezone = null){
		if(!$date){
			$date = "today";
		}
		parent::__construct($date, $timezone);
		$this->datetime->setTime(0 ,0, 0);
	}

	/**
	 * @param null|string|int|\DateTime $date [optional]
	 * @param null|string|\DateTimeZone|\Et\Locales_Timezone $timezone [optional]
	 * @return \Et\Locales_Date
	 */
	public static function getInstance($date = null, $timezone = null){
		return new static($date, $timezone);
	}


	/**
	 * @param string $formatted_string
	 * @param string $formatted_string_format [optional]
	 * @param null|string|\DateTimeZone $timezone [optional]
	 *
	 * @return Locales_Date
	 * @throws Locales_Exception
	 */
	public static function getInstanceFromFormat($formatted_string, $formatted_string_format = null, $timezone = null){
		$date = parent::getInstanceFromFormat($formatted_string, $formatted_string_format, $timezone);
		$date->datetime->setTime(0, 0, 0);
		return $date;
	}

	/**
	 * @param bool $with_timezone [optional]
	 *
	 * @return string
	 */
	function toString($with_timezone = false) {
		return $this->getDate($with_timezone);
	}

	/**
	 * @param Locales_Locale|string $target_locale [optional]
	 *
	 * @return string
	 */
	function getLocalized($target_locale = Locales::CURRENT_LOCALE){
		return $this->getDateLocalized($target_locale);
	}
}