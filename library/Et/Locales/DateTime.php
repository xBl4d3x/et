<?php
namespace Et;
class Locales_DateTime extends \DateTime {

	const DATE = 'Y-m-d';
	const DATE_WITH_TZ = 'Y-m-dO';

	const TIME = 'H:i:s';
	const TIME_WITH_TZ = 'H:i:sO';

	const DATETIME = "Y-m-d\\TH:i:s";
	const DATETIME_WITH_TZ = "Y-m-d\\TH:i:sO";

	const MYSQL_DATE = 'Y-m-d';
	const MYSQL_DATETIME = 'Y-m-d H:i:s';

	const TIMESTAMP = 'U';

	/**
	 * @param null|string|int|$datetime [optional]
	 * @param null|string|\DateTimeZone $timezone [optional]
	 * @throws \Et\Locales_Exception
	 */
	function __construct($datetime = null, $timezone = null){

		if($datetime instanceof \DateTime){
			if(!$timezone){
				$timezone = $datetime->getTimezone();
			}
			$datetime = $datetime->format(self::DATETIME);
		}

		if(!$datetime){
			$datetime = "now";
		}
		
		if(is_numeric($datetime)){
			$datetime = "@{$datetime}";
		}

		if($timezone && !$timezone instanceof \DateTimeZone){
			$timezone = Locales::getTimezone($timezone);
		}

		try {

			parent::__construct($datetime, $timezone);

		} catch(\Exception $e){

			throw new Locales_Exception(
				"Failed to create DateTime object - {$e->getMessage()}",
				Locales_Exception::CODE_INVALID_DATE_TIME
			);

		}

		$errors = self::getLastErrors();
		if(!empty($errors["error_count"])){
			throw new Locales_Exception(
				array_shift($errors["errors"]),
				Locales_Exception::CODE_INVALID_DATE_TIME
			);
		}

		if(!empty($errors["warning_count"])){
			throw new Locales_Exception(
				array_shift($errors["warnings"]),
				Locales_Exception::CODE_INVALID_DATE_TIME
			);
		}

	}

	/**
	 * Get the TimeZone associated with the DateTime
	 * @return \Et\Locales_Timezone
	 * @link http://php.net/manual/en/datetime.gettimezone.php
	 */
	public function getTimezone () {
		$tz = parent::getTimezone();
		if(!$tz || $tz instanceof Locales_Timezone){
			return $tz;
		}
		return new Locales_Timezone($tz->getName());
	}

	/**
	 * @return string
	 */
	function getTimeZoneName(){
		return $this->getTimezone()->getName();
	}

	/**
	 * @return array
	 */
	function getTimeZoneLocation(){
		return $this->getTimezone()->getLocation();
	}

	/**
	 * @param null|string|int $datetime [optional]
	 * @param null|string|\DateTimeZone $timezone [optional]
	 * @return \Et\Locales_DateTime|static
	 */
	public static function getInstance($datetime = null, $timezone = null){
		return new static($datetime, $timezone);
	}

	/**
	 * @param Locales_Locale|string $target_locale
	 * @param string|\DateTimeZone $target_timezone
	 *
	 * @return Locales_Formatter_DateTime
	 */
	function getFormatter($target_locale = Locales::CURRENT_LOCALE, $target_timezone = Locales::CURRENT_TIMEZONE){
		return new Locales_Formatter_DateTime($this, $target_locale, $target_timezone);
	}

	/**
	 * @param bool $two_digits [optional]
	 *
	 * @return string|int
	 */
	function getYear($two_digits = false){
		return $this->format($two_digits ? "y" : "Y");
	}

	/**
	 * @param bool $two_digits [optional]
	 *
	 * @return string|int
	 */
	function getMonth($two_digits = false){
		return $this->format($two_digits ? "m" : "n");
	}

	/**
	 * @param bool $short_format [optional]
	 *
	 * @return string
	 */
	function getMonthName($short_format = false){
		return $this->format($short_format ? "M" : "F");
	}

	/**
	 * @param bool $short_format [optional]
	 * @param null|Locales_Locale|string $target_locale [optional]
	 *
	 * @return string
	 */
	function getMonthNameLocalized($short_format = false, $target_locale = Locales::CURRENT_LOCALE){
		return $this->getFormatter($target_locale)->getMonthName($short_format);
	}

	/**
	 * @return int
	 */
	function getMonthDaysCount(){
		return $this->format("t");
	}


	/**
	 * @param bool $two_digits [optional]
	 *
	 * @return string
	 */
	function getDay($two_digits = false){
		return $this->format($two_digits ? "d" : "j");
	}

	/**
	 * @param bool $short_format [optional]
	 *
	 * @return string
	 */
	function getDayName($short_format = false){
		return $this->format($short_format ? "D" : "l");
	}

	/**
	 * @param bool $short_format [optional]
	 * @param null|Locales_Locale|string $target_locale [optional]
	 *
	 * @return string
	 */
	function getDayNameLocalized($short_format = false, $target_locale = Locales::CURRENT_LOCALE){
		return $this->getFormatter($target_locale)->getDayName($short_format);
	}

	/**
	 * @param bool $two_digits [optional]
	 * @param bool $twelve_hour_format [optional]
	 *
	 * @return string|int
	 */
	function getHours($two_digits = false, $twelve_hour_format = false){
		if($two_digits){
			if($twelve_hour_format){
				$format = 'h';
			} else {
				$format = 'H';
			}
		} else {
			if($twelve_hour_format){
				$format = 'g';
			} else {
				$format = 'G';
			}
		}
		return $this->format($format);
	}

	/**
	 * @param bool $upper_case [optional]
	 *
	 * @return string
	 */
	function getAMorPM($upper_case = true){
		return $this->format($upper_case ? 'A' : 'a');
	}

	/**
	 * @return bool
	 */
	function isAM(){
		return $this->getAMorPM(true) == "AM";
	}

	/**
	 * @return bool
	 */
	function isPM(){
		return $this->getAMorPM(true) == "PM";
	}

	/**
	 * @param int $hours_from
	 * @param int $hours_to
	 * @param bool $twelve_hour_format [optional]
	 *
	 * @return bool
	 */
	function areHoursInRange($hours_from, $hours_to, $twelve_hour_format = false){
		$hours = $this->getHours(false, $twelve_hour_format);
		return $hours >= $hours_from && $hours <= $hours_to;
	}

	/**
	 * @param bool $two_digits [optional]
	 *
	 * @return string|int
	 */
	function getMinutes($two_digits = false){
		return $this->format($two_digits ? "m" : "n");
	}

	/**
	 * @param bool $two_digits [optional]
	 *
	 * @return string|int
	 */
	function getSeconds($two_digits = false){
		return $this->format($two_digits ? "m" : "n");
	}

	/**
	 * @return int
	 */
	function getMicroseconds(){
		return $this->format("u");
	}


	/**
	 * @return int
	 */
	function getDayOfYear(){
		return $this->format("z");
	}

	/**
	 * @return int
	 */
	function getWeek(){
		return $this->format("W");
	}

	/**
	 * @return bool
	 */
	function getYearIsLeapYear(){
		return (bool)$this->format("L");
	}


	/**
	 * @param bool $with_timezone [optional]
	 *
	 * @return string
	 */
	function getDateTime($with_timezone = true){
		$format = $with_timezone
			?  static::DATETIME_WITH_TZ
			:  static::DATETIME;
		return $this->format($format);
	}

	/**
	 * @see Locales_DateTime_Formatter::formatDateTime()
	 *
	 * @param null|int $date_style [optional]
	 * @param null|int $time_style [optional]
	 * @param Locales_Locale|string|null $target_locale [optional]
	 * @param null|string|\DateTimeZone $target_timezone [optional]
	 *
	 * @return string
	 */
	function getDateTimeLocalized($date_style = null, $time_style = null, $target_locale = Locales::CURRENT_LOCALE, $target_timezone = Locales::CURRENT_TIMEZONE){
		return $this->getFormatter($target_locale, $target_timezone)->formatDateTime($date_style, $time_style);
	}

	/**
	 * @param bool $with_timezone [optional]
	 *
	 * @return string
	 */
	function getDate($with_timezone = false){
		if($with_timezone){
			$format = self::DATE_WITH_TZ;
		} else {
			$format = self::DATE;
		}
		return $this->format($format);
	}

	/**
	 * @see Locales_DateTime_Formatter::formatDate()
	 *
	 * @param null|int $date_style [optional]
	 * @param Locales_Locale|string $target_locale [optional]
	 * @param string|\DateTimeZone|\Et\Locales_Timezone $target_timezone [optional]
	 *
	 * @return string
	 */
	function getDateLocalized($date_style = null, $target_locale = Locales::CURRENT_LOCALE, $target_timezone = Locales::CURRENT_TIMEZONE){
		return $this->getFormatter($target_locale, $target_timezone)->formatDate($date_style);
	}

	/**
	 * @param bool $with_timezone [optional]
	 *
	 * @return string
	 */
	function getTime($with_timezone = false){
		if($with_timezone){
			$format = self::TIME_WITH_TZ;
		} else {
			$format = self::TIME;
		}
		return $this->format($format);
	}

	/**
	 * Sets the current time of the DateTime object to a different time.
	 *
	 * @param int $hour
	 * @param int $minute [optional]
	 * @param int $second [optional]
	 * @return static|\Et\Locales_DateTime
	 */
	public function setTime ($hour, $minute = 0, $second=0) {
		parent::setTime($hour, $minute, $second);
		return $this;
	}

	/**
	 * Sets the current date of the DateTime object to a different date.
	 *
	 * @param int|null $year [optional]
	 * @param int|null $month [optional]
	 * @param int $day [optional]
	 * @return static|\Et\Locales_DateTime
	 */
	public function setDate ($year = null, $month = null, $day = 1) {
		if(!$year){
			$year = date("Y");
		}

		if(!$month){
			$month = date("n");
		}

		$year = (int)$year;
		$month = (int)$month;

		parent::setDate($year, $month, $day);
		return $this;
	}

	/**
	 * @see Locales_DateTime_Formatter::formatTime()
	 *
	 * @param null|int $time_style [optional]
	 * @param Locales_Locale|string|null $target_locale [optional]
	 * @param string|\DateTimeZone $target_timezone [optional]
	 *
	 * @return string
	 */
	function getTimeLocalized($time_style = null, $target_locale = Locales::CURRENT_LOCALE, $target_timezone = Locales::CURRENT_TIMEZONE){
		return $this->getFormatter($target_locale, $target_timezone)->formatTime($time_style);
	}


	/**
	 * @param Locales_Locale|string|null $target_locale [optional]
	 * @param string|\DateTimeZone $target_timezone [optional]
	 *
	 * @return string
	 */
	function getLocalized($target_locale = Locales::CURRENT_LOCALE, $target_timezone = Locales::CURRENT_TIMEZONE){
		return $this->getDateTimeLocalized(null, null, $target_locale, $target_timezone);
	}


	/**
	 * Parse a string into a new DateTime object according to the specified format
	 * @param string $format Format accepted by date().
	 * @param string $time String representing the time.
	 * @param string|\DateTimeZone $timezone A DateTimeZone object representing the desired time zone.
	 * @throws Locales_Exception
	 * @return static|\Et\Locales_DateTime
	 * @link http://php.net/manual/en/datetime.createfromformat.php
	 */
	public static function createFromFormat ($format, $time, $timezone = null) {
		if($timezone && !$timezone instanceof \DateTimeZone){
			$timezone = Locales::getTimezone($timezone);
		}

		try {

			$datetime = parent::createFromFormat($format, $time, $timezone);

		} catch(\Exception $e){

			throw new Locales_Exception(
				"Failed to create DateTime object - {$e->getMessage()}",
				Locales_Exception::CODE_INVALID_DATE_TIME
			);
		}

		$errors = self::getLastErrors();
		if(!empty($errors["error_count"])){
			throw new Locales_Exception(
				array_shift($errors["errors"]),
				Locales_Exception::CODE_INVALID_DATE_TIME
			);
		}

		if(!empty($errors["warning_count"])){
			throw new Locales_Exception(
				array_shift($errors["warnings"]),
				Locales_Exception::CODE_INVALID_DATE_TIME
			);
		}

		return $datetime;
	}
	
	/**
	 * @return string
	 */
	function  __toString() {
		return $this->toString();
	}

	/**
	 * @param bool $with_timezone [optional]
	 *
	 * @return string
	 */
	function toString($with_timezone = true) {
		return $this->getDateTime($with_timezone);
	}

	/**
	 * @return \Et\Locales_DateTime|static
	 */
	public function resetDate(){
		return $this->setDate(1970, 1, 1);
	}

	/**
	 * @return \Et\Locales_DateTime|static
	 */
	public function resetTime(){
		return $this->setTime(0, 0, 0);
	}

	/**
	 * @param null|string|\DateTimeZone $timezone [optional]
	 *
	 * @return static|\Et\Locales_DateTime
	 */
	public static function getNow($timezone = null){
		return static::getInstance("now", $timezone);
	}

	/**
	 * @param null|string|\DateTimeZone $timezone [optional]
	 *
	 * @return static|\Et\Locales_DateTime
	 */
	public static function getToday($timezone = null){
		return static::getInstance("today", $timezone);
	}


	/**
	 * @param int $year [optional]
	 * @param null|string|\DateTimeZone $timezone [optional]
	 *
	 * @return Locales_DateTime
	 */
	public static function getByYear($year = null, $timezone = null){
		if($year === null){
			$year = date("Y");
		}

		$year = (int)$year;
		return static::getInstance("{$year}-01-01", $timezone);
	}

	/**
	 * @param null|int $year [optional]
	 * @param null|int $week [optional]
	 * @param null|string|\DateTimeZone $timezone [optional]
	 *
	 * @return Locales_DateTime
	 */
	public static function getByWeek($year = null, $week = null, $timezone = null){
		if($year === null){
			$year = date("Y");
		}
		if($week === null){
			$week = date("W");
		}

		$year = (int)$year;
		$week = (int)$week;

		return static::getInstance("{$year}-W{$week}", $timezone);
	}

	/**
	 * @param null|int $year [optional]
	 * @param null|int $week [optional]
	 * @param null|int $day_of_week [optional]
	 * @param null|string|\DateTimeZone $timezone [optional]
	 *
	 * @return Locales_DateTime
	 */
	public static function getByWeekDay($year = null, $week = null, $day_of_week = null, $timezone = null){
		if($year === null){
			$year = date("Y");
		}

		if($week === null){
			$week = date("W");
		}

		if($day_of_week === null){
			$day_of_week = date("w");
		}

		$year = (int)$year;
		$week = (int)$week;
		$day_of_week = (int)$day_of_week;

		return static::getInstance("{$year}-W{$week}-{$day_of_week}", $timezone);
	}

	/**
	 * @param null|int $year [optional]
	 * @param null|int $month [optional]
	 * @param null|string|\DateTimeZone $timezone [optional]
	 *
	 * @return Locales_DateTime
	 */
	public static function getByMonth($year = null, $month = null, $timezone = null){
		if($year === null){
			$year = date("Y");
		}

		if($month === null){
			$month = date("n");
		}

		$year = (int)$year;
		$month = (int)$month;

		$month = str_pad($month, 2, "0", STR_PAD_LEFT);
		return static::getInstance("{$year}-{$month}-01", $timezone);
	}

	/**
	 * @param null|int $year [optional]
	 * @param null|int $month [optional]
	 * @param null|int $day [optional]
	 * @param null|string|\DateTimeZone $timezone [optional]
	 *
	 * @return Locales_DateTime
	 */
	public static function getByDate($year = null, $month = null, $day = null, $timezone = null){
		if($year === null){
			$year = date("Y");
		}

		if($month === null){
			$month = date("n");
		}

		if($day === null){
			$day = date("j");
		}

		$year = (int)$year;
		$month = (int)$month;
		$day = (int)$day;


		$month = str_pad($month, 2, "0", STR_PAD_LEFT);
		$day = str_pad($day, 2, "0", STR_PAD_LEFT);

		return static::getInstance("{$year}-{$month}-{$day}", $timezone);
	}
}