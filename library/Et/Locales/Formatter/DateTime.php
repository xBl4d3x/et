<?php
namespace Et;
/**
 * Date and time formatter
 * @link http://userguide.icu-project.org/formatparse/datetime
 */
class Locales_Formatter_DateTime {

	const CALENDAR_GREGORIAN = \IntlDateFormatter::GREGORIAN;
	const CALENDAR_TRADITIONAL = \IntlDateFormatter::TRADITIONAL;

	/**
	 * Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST)
	 */
	const STYLE_FULL = \IntlDateFormatter::FULL;

	/**
	 * Long style (January 12, 1952 or 3:30:32pm)
	 */
	const STYLE_LONG = \IntlDateFormatter::LONG;

	/**
	 * Medium style (Jan 12, 1952)
	 */
	const STYLE_MEDIUM = \IntlDateFormatter::MEDIUM;

	/**
	 * Most abbreviated style, only essential data (12/13/52 or 3:30pm)
	 */
	const STYLE_SHORT = \IntlDateFormatter::SHORT;

	/**
	 * Do not include this element
	 */
	const STYLE_NONE = \IntlDateFormatter::NONE;

	// see @link http://userguide.icu-project.org/formatparse/datetime for all patterns
	const PATTERN_QUARTER_NUMBER = "Q";
	const PATTERN_QUARTER = "QQQ";
	const PATTERN_QUARTER_FULL = "QQQQ";
	const PATTERN_QUARTER_NAME = "qqq";
	const PATTERN_QUARTER_NAME_FULL = "qqqq";

	const PATTERN_MONTH = "MMMM";
	const PATTERN_MONTH_SHORT = "MMM";
	const PATTERN_MONTH_NAME = "LLLL";
	const PATTERN_MONTH_NAME_SHORT = "LLL";

	const PATTERN_DAY = "EEEE";
	const PATTERN_DAY_SHORT = "EEE";
	const PATTERN_DAY_NAME = "cccc";
	const PATTERN_DAY_NAME_SHORT = "ccc";

	const PATTERN_TIMEZONE_LOCATION = "VVVV";

	/**
	 * @var Locales_Locale
	 */
	protected $locale;

	/**
	 * @var Locales_DateTime
	 */
	protected $datetime;

	/**
	 * @var Locales_Timezone
	 */
	protected $target_timezone;

	/**
	 * @var int
	 */
	protected $calendar;

	/**
	 * @var int
	 */
	protected $default_date_style = self::STYLE_MEDIUM;

	/**
	 * @var int
	 */
	protected $default_time_style = self::STYLE_MEDIUM;

	/**
	 * @param int|string|\DateTime|Locales_DateTime $datetime
	 * @param \Et\Locales_Locale|int|string $target_locale [optional[
	 * @param \Et\Locales_Timezone|mixed|string $target_timezone [optional] If not set, system timezone is used
	 * @param null|int $calendar [optional] If not set, Locales_Formatter_DateTime::CALENDAR_GREGORIAN is used
	 */
	function __construct($datetime, $target_locale = Locales::CURRENT_LOCALE, $target_timezone = Locales::CURRENT_TIMEZONE, $calendar = null){

		$this->datetime = Locales::getDateTime($datetime);
		$this->locale = Locales::getLocale($target_locale);
		$this->target_timezone = Locales::getTimezone($target_timezone);


		if($calendar === null){
			$calendar = self::CALENDAR_GREGORIAN;
		}

		$this->calendar = $calendar;
	}

	/**
	 * @param int $default_date_style
	 */
	public function setDefaultDateStyle($default_date_style) {
		$this->default_date_style = (int)$default_date_style;
	}

	/**
	 * @return int
	 */
	public function getDefaultDateStyle() {
		return $this->default_date_style;
	}

	/**
	 * @param int $default_time_style
	 */
	public function setDefaultTimeStyle($default_time_style) {
		$this->default_time_style = (int)$default_time_style;
	}

	/**
	 * @return int
	 */
	public function getDefaultTimeStyle() {
		return $this->default_time_style;
	}



	/**
	 * @param null|int $date_style [optional] NULL = Locales_Formatter_DateTime::STYLE_NONE
	 * @param null|int $time_style [optional] NULL = Locales_Formatter_DateTime::STYLE_NONE
	 *
	 * @return \IntlDateFormatter
	 */
	public function getFormatter($date_style = null, $time_style = null){
		if($date_style === null){
			$date_style = self::STYLE_NONE;
		}

		if($time_style === null){
			$time_style = self::STYLE_NONE;
		}

		return new \IntlDateFormatter(
			$this->locale->getLocale(),
			$date_style,
			$time_style,
			(string)$this->target_timezone,
			$this->calendar
		);
	}

	/**
	 * @return int
	 */
	public function getCalendar() {
		return $this->calendar;
	}

	/**
	 * @return Locales_Locale
	 */
	public function getLocale() {
		return $this->locale;
	}

	/**
	 * @return Locales_Timezone
	 */
	public function getTargetTimezone() {
		return $this->target_timezone;
	}

	/**
	 * @return Locales_DateTime
	 */
	public function getDatetime() {
		return $this->datetime;
	}

	/**
	 * @param null|string $format_pattern [optional]
	 * @return string
	 * @link http://userguide.icu-project.org/formatparse/datetime
	 */
	function format($format_pattern = null){
		if($format_pattern){
			return $this->formatByPattern($format_pattern);
		}

		if($this->datetime instanceof Locales_Date){
			return $this->formatDate();
		}

		return $this->formatDateTime();
	}

	/**
	 * @param string $format_pattern
	 * @link http://userguide.icu-project.org/formatparse/datetime
	 *
	 * @return string
	 */
	function formatByPattern($format_pattern){
		$formatter = $this->getFormatter();
		$formatter->setPattern($format_pattern);
		return $formatter->format($this->datetime->getDateTimeInstance());
	}

	/**
	 * @param null|int $date_style [optional] If NULL, default date style is used ($default_date_style)
	 *
	 * @return string
	 */
	function formatDate($date_style = null){
		if($date_style === null){
			$date_style = $this->getDefaultDateStyle();
		}
		$formatter = $this->getFormatter($date_style);
		return $formatter->format($this->datetime->getDateTimeInstance());
	}

	/**
	 * @param null|int $time_style [optional] If NULL, default date style is used ($default_time_style)
	 *
	 * @return string
	 */
	function formatTime($time_style = null){
		if($time_style === null){
			$time_style = $this->getDefaultTimeStyle();
		}
		$formatter = $this->getFormatter(null, $time_style);
		return $formatter->format($this->datetime->getDateTimeInstance());
	}

	/**
	 * @param null|int $date_style [optional] If NULL, default date style is used ($default_date_style)
	 * @param null|int $time_style [optional] If NULL, default date style is used ($default_time_style)
	 *
	 * @return string
	 */
	function formatDateTime($date_style = null, $time_style = null){
		if($date_style === null){
			$date_style = $this->getDefaultDateStyle();
		}
		if($time_style === null){
			$time_style = $this->getDefaultTimeStyle();
		}
		$formatter = $this->getFormatter($date_style, $time_style);
		return $formatter->format($this->datetime->getDateTimeInstance());
	}

	/**
	 * @return string
	 */
	function getQuarterNumber(){
		return $this->formatByPattern(self::PATTERN_QUARTER_NUMBER);
	}

	/**
	 * @param bool $short_format [optional]
	 *
	 * @return string
	 */
	function formatQuarter($short_format = false){
		return $this->formatByPattern($short_format ? self::PATTERN_QUARTER : self::PATTERN_QUARTER_FULL);
	}

	/**
	 * @param bool $short_format [optional]
	 *
	 * @return string
	 */
	function getQuarterName($short_format = false){
		return $this->formatByPattern($short_format ? self::PATTERN_QUARTER_NAME : self::PATTERN_QUARTER_NAME_FULL);
	}

	/**
	 * @param bool $short_format [optional]
	 *
	 * @return string
	 */
	function formatMonth($short_format = false){
		return $this->formatByPattern($short_format ? self::PATTERN_MONTH_SHORT : self::PATTERN_MONTH);
	}

	/**
	 * @param bool $short_format [optional]
	 *
	 * @return string
	 */
	function getMonthName($short_format = false){
		return $this->formatByPattern($short_format ? self::PATTERN_MONTH_NAME_SHORT : self::PATTERN_MONTH_NAME);
	}

	/**
	 * @param bool $short_format [optional]
	 *
	 * @return string
	 */
	function formatDay($short_format = false){
		return $this->formatByPattern($short_format ? self::PATTERN_DAY_SHORT : self::PATTERN_DAY);
	}

	/**
	 * @param bool $short_format [optional]
	 *
	 * @return string
	 */
	function getDayName( $short_format = false){
		return $this->formatByPattern($short_format ? self::PATTERN_DAY_NAME_SHORT : self::PATTERN_DAY_NAME);
	}

	/**
	 * @return string
	 */
	function getTimezoneLocation(){
		return $this->formatByPattern($this->datetime, self::PATTERN_TIMEZONE_LOCATION);
	}
}