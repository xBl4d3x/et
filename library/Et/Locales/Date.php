<?php
namespace Et;
et_require("Locales_DateTime");
class Locales_Date extends Locales_DateTime {

	/**
	 * @param null|string|int $date [optional]
	 * @param null|string|\DateTimeZone $timezone [optional]
	 *
	 * @throws Locales_Exception
	 */
	function __construct($date = null, $timezone = null){
		if(!$date){
			$date = "today";
		}

		parent::__construct($date, $timezone);
		$this->setTime(0 ,0, 0);
	}

	/**
	 * @param null|string|int $date [optional]
	 * @param null|string|\DateTimeZone $timezone [optional]
	 * @return static|\Et\Locales_Date
	 */
	public static function getInstance($date = null, $timezone = null){
		return new static($date, $timezone);
	}

	/**
	 * Parse a string into a new DateTime object according to the specified format
	 * @param string $format Format accepted by date().
	 * @param string $time String representing the time.
	 * @param string|\DateTimeZone $timezone A DateTimeZone object representing the desired time zone.
	 * @throws Locales_Exception
	 * @return static|\Et\Locales_Date
	 * @link http://php.net/manual/en/datetime.createfromformat.php
	 */
	public static function createFromFormat ($format, $time, $timezone = null) {
		$dt = parent::createFromFormat($format, $time, $timezone);
		$dt->setTime(0, 0, 0);
		return $dt;
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