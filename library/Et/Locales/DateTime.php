<?php
namespace Et;
class Locales_DateTime {

	const FORMAT_DEFAULT_WITHOUT_TZ = 'Y-m-d\TH:i:s';
	const FORMAT_DEFAULT_WITH_TZ = self::FORMAT_ISO8601;

	const FORMAT_DATE = 'Y-m-d';
	const FORMAT_DATE_WITH_TZ = 'Y-m-dO';

	const FORMAT_TIME = 'H:i:s';
	const FORMAT_TIME_WITH_TZ = 'H:i:sO';

	const FORMAT_MYSQL = 'Y-m-d H:i:s';
	const FORMAT_MYSQL_SHORT = 'YmdHis';

	const FORMAT_ATOM = 'Y-m-d\TH:i:sP';
	const FORMAT_COOKIE = 'l, d-M-y H:i:s T';
	const FORMAT_ISO8601 = 'Y-m-d\TH:i:sO';
	const FORMAT_RFC822 = 'D, d M y H:i:s O';
	const FORMAT_RFC850 = 'l, d-M-y H:i:s T';
	const FORMAT_RFC1036 = 'D, d M y H:i:s O';
	const FORMAT_RFC1123 = 'D, d M Y H:i:s O';
	const FORMAT_RFC2822 = 'D, d M Y H:i:s O';
	const FORMAT_RFC3339 = 'Y-m-d\TH:i:sP';
	const FORMAT_RSS = 'D, d M Y H:i:s O';
	const FORMAT_W3C = 'Y-m-d\TH:i:sP';
	const FORMAT_TIMESTAMP = 'U';

	/**
	 * @var \DateTime
	 */
	protected $datetime;

	/**
	 * @param null|string|int|\DateTime $datetime [optional]
	 * @param null|string|\DateTimeZone|\Et\Locales_Timezone $timezone [optional]
	 */
	function __construct($datetime = null, $timezone = null){

		if($datetime instanceof \DateTime){
			$this->datetime = $datetime;
			return;
		}
		
		if(!$datetime){
			$datetime = "now";
		}
		
		if(is_numeric($datetime)){
			$datetime = "@{$datetime}";
		}
		
		$this->initialize($datetime, $timezone);
	}

	/**
	 * @param string $datetime
	 * @param null|string|\DateTimeZone|\Et\Locales_Timezone $timezone
	 * @throws Locales_Exception
	 */
	protected function initialize($datetime, $timezone){

		if($timezone && !$timezone instanceof \DateTimeZone){
			$timezone = Locales::getTimezone($timezone)->getDateTimeZone();
		}

		try {

			$this->datetime = new \DateTime($datetime, $timezone);
			
		} catch(\Exception $e){

			throw new Locales_Exception(
				"Failed to create DateTime object - {$e->getMessage()}",
				Locales_Exception::CODE_INVALID_DATE_TIME
			);

		}

		$errors = \DateTime::getLastErrors();
		if($errors["error_count"]){
			throw new Locales_Exception(
				array_shift($errors["errors"]),
				Locales_Exception::CODE_INVALID_DATE_TIME
			);
		}

		if($errors["warning_count"]){
			throw new Locales_Exception(
				array_shift($errors["warnings"]),
				Locales_Exception::CODE_INVALID_DATE_TIME
			);
		}

		$this->datetime = new \DateTime($datetime, $timezone);
	}

	/**
	 * @return \DateTime
	 */
	function getDateTimeInstance(){
		return $this->datetime;
	}

	/**
	 * @return \DateTimeZone
	 */
	function getDateTimeZoneInstance(){
		return $this->datetime->getTimezone();
	}

	/**
	 * @return string
	 */
	function getTimeZoneName(){
		return $this->getDateTimeZoneInstance()->getName();
	}

	/**
	 * @return array
	 */
	function getTimeZoneLocation(){
		return $this->getDateTimeZoneInstance()->getLocation();
	}

	/**
	 * @param null|string|int|\DateTime $datetime [optional]
	 * @param null|string|\DateTimeZone|\Et\Locales_Timezone $timezone [optional]
	 * @return \Et\Locales_DateTime
	 */
	public static function getInstance($datetime = null, $timezone = null){
		return new static($datetime, $timezone);
	}

	/**
	 * @param Locales_Locale|string $target_locale
	 * @param string|\DateTimeZone|\Et\Locales_TimeZone $target_timezone
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
	 * @return int
	 */
	function getTimestamp(){
		return $this->datetime->getTimestamp();
	}

	/**
	 * @param string $format
	 *
	 * @return string|int
	 */
	function format($format = null){
		if(!$format){
			$format = static::FORMAT_DEFAULT_WITHOUT_TZ;
		}
		return $this->datetime->format($format);
	}

	/**
	 * @param bool $with_timezone [optional]
	 *
	 * @return string
	 */
	function getDateTime($with_timezone = false){
		$format = $with_timezone
			? static::FORMAT_DEFAULT_WITH_TZ
			: static::FORMAT_DEFAULT_WITHOUT_TZ;
		return $this->format($format);
	}

	/**
	 * @see Locales_DateTime_Formatter::formatDateTime()
	 *
	 * @param null|int $date_style [optional]
	 * @param null|int $time_style [optional]
	 * @param Locales_Locale|string|null $target_locale [optional]
	 * @param null|string|\DateTimeZone|\Et\Locales_TimeZone $target_timezone [optional]
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
			$format = self::FORMAT_DATE_WITH_TZ;
		} else {
			$format = self::FORMAT_DATE;
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
			$format = self::FORMAT_TIME_WITH_TZ;
		} else {
			$format = self::FORMAT_TIME;
		}
		return $this->format($format);
	}

	/**
	 * Sets the current time of the DateTime object to a different time.
	 *
	 * @param int $hour
	 * @param int $minute [optional]
	 * @param int $second [optional]
	 */
	public function setTime ($hour, $minute = 0, $second=0) {
		$this->datetime->setTime($hour, $minute, $second);
	}

	/**
	 * Sets the current date of the DateTime object to a different date.
	 *
	 * @param int|null $year
	 * @param int|null $month
	 * @param int $day
	 */
	public function setDate ($year = null, $month = null, $day = 1) {
		if($year === null){
			$year = date("Y");
		}

		if($month === null){
			$month = date("n");
		}

		$year = (int)$year;
		$month = (int)$month;

		$this->datetime->setDate($year, $month, $day);
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
	 * @param string $formatted_string
	 * @param string $formatted_string_format [optional]
	 * @param null|string|\DateTimeZone $timezone [optional]
	 *
	 * @return Locales_DateTime
	 * @throws Locales_Exception
	 */
	public static function getInstanceFromFormat($formatted_string, $formatted_string_format = null, $timezone = null){
		if(!$formatted_string_format){
			$formatted_string_format = static::FORMAT_DEFAULT_WITHOUT_TZ;
		}

		if($timezone && !$timezone instanceof \DateTimeZone){
			$timezone = Locales::getTimezone($timezone)->getDateTimeZone();
		}

		try {

			$datetime = \DateTime::createFromFormat($formatted_string_format, $formatted_string, $timezone);

		} catch(\Exception $e){

			throw new Locales_Exception(
				"Failed to create DateTime object - {$e->getMessage()}",
				Locales_Exception::CODE_INVALID_DATE_TIME
			);
		}

		$errors = \DateTime::getLastErrors();
		if($errors["error_count"]){
			throw new Locales_Exception(
				array_shift($errors["errors"]),
				Locales_Exception::CODE_INVALID_DATE_TIME
			);
		}

		if($errors["warning_count"]){
			throw new Locales_Exception(
				array_shift($errors["warnings"]),
				Locales_Exception::CODE_INVALID_DATE_TIME
			);
		}

		return new static($datetime);
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
	function toString($with_timezone = false) {
		return $this->getDateTime($with_timezone);
	}

	public function resetDate(){
		$this->datetime->setDate(1970, 1, 1);
	}

	public function resetTime(){
		$this->datetime->setTime(0, 0, 0);
	}

	/**
	 * @param null|string|\DateTimeZone|\Et\Locales_TimeZone $timezone [optional]
	 *
	 * @return Locales_DateTime
	 */
	public static function getNowInstance($timezone = null){
		return static::getInstance("now", $timezone);
	}

	/**
	 * @param null|string|\DateTimeZone|\Et\Locales_TimeZone $timezone [optional]
	 *
	 * @return Locales_DateTime
	 */
	public static function getTodayInstance($timezone = null){
		return static::getInstance("today", $timezone);
	}


	/**
	 * @param int $year [optional]
	 * @param null|string|\DateTimeZone|\Et\Locales_TimeZone $timezone [optional]
	 *
	 * @return Locales_DateTime
	 */
	public static function getInstanceByYear($year = null, $timezone = null){
		if($year === null){
			$year = date("Y");
		}

		$year = (int)$year;

		return static::getInstance("{$year}-01-01", $timezone);
	}

	/**
	 * @param null|int $year [optional]
	 * @param null|int $week [optional]
	 * @param null|string|\DateTimeZone|\Et\Locales_TimeZone $timezone [optional]
	 *
	 * @return Locales_DateTime
	 */
	public static function getInstanceByWeek($year = null, $week = null, $timezone = null){
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
	 * @param null|string|\DateTimeZone|\Et\Locales_TimeZone $timezone [optional]
	 *
	 * @return Locales_DateTime
	 */
	public static function getInstanceByWeekDay($year = null, $week = null, $day_of_week = null, $timezone = null){
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
	 * @param null|string|\DateTimeZone|\Et\Locales_TimeZone $timezone [optional]
	 *
	 * @return Locales_DateTime
	 */
	public static function getInstanceByMonth($year = null, $month = null, $timezone = null){
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
	 * @param null|string|\DateTimeZone|\Et\Locales_TimeZone $timezone [optional]
	 *
	 * @return Locales_DateTime
	 */
	public static function getInstanceByDate($year = null, $month = null, $day = null, $timezone = null){
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