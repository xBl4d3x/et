<?php
namespace Et;
/**
 * Date and time formatter
 * @link http://userguide.icu-project.org/formatparse/datetime
 */
class Locales_Formatter_DateTime extends Object {

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
	 * @var \Et\Locales_Locale
	 */
	protected $target_locale;

	/**
	 * @var \Et\Locales_Timezone
	 */
	protected $target_timezone;

	/**
	 * @var \Et\Locales_DateTime
	 */
	protected $datetime;


	/**
	 * @var int
	 */
	protected $default_date_style = self::STYLE_MEDIUM;

	/**
	 * @var int
	 */
	protected $default_time_style = self::STYLE_MEDIUM;

	/**
	 * @param null|string|int|\Et\Locales_DateTime $datetime
	 * @param \Et\Locales_Locale|string $target_locale [optional[
	 * @param \DateTimeZone|string $target_timezone [optional] If not set, system timezone is used
	 */
	function __construct($datetime = null, $target_locale = Locales::CURRENT_LOCALE, $target_timezone = Locales::CURRENT_TIMEZONE){
		$this->setDatetime($datetime);
		$this->setTargetLocale($target_locale);
		$this->setTargetTimezone($target_timezone);

	}

	/**
	 * @param null|int $date_style [optional]
	 * @param null|int $time_style [optional]
	 * @return \IntlDateFormatter
	 */
	function getFormatter($date_style = null, $time_style = null){
		if($date_style === null){
			$date_style = $this->getDefaultDateStyle();
		}

		if($time_style === null){
			$time_style = $this->getDefaultTimeStyle();
		}

		return new \IntlDateFormatter(
			(string)$this->target_locale,
			$date_style,
			$time_style,
			(string)$this->target_timezone,
			\IntlDateFormatter::GREGORIAN
		);
	}

	/**
	 * @return \Et\Locales_Locale
	 */
	public function getTargetLocale() {
		return $this->target_locale;
	}

	/**
	 * @return \Et\Locales_Timezone
	 */
	public function getTargetTimezone() {
		return $this->target_timezone;
	}

	/**
	 * @return \Et\Locales_DateTime
	 */
	public function getDatetime() {
		return $this->datetime;
	}

	/**
	 * @param \Et\Locales_DateTime|string|int $datetime
	 */
	public function setDatetime($datetime) {
		if(!$datetime instanceof Locales_DateTime){
			$datetime = Locales::getDateTime($datetime);
		}
		$this->datetime = $datetime;
	}

	/**
	 * @param \Et\Locales_Locale|string $target_locale
	 */
	public function setTargetLocale($target_locale) {
		$this->target_locale = Locales::getLocale($target_locale);
	}

	/**
	 * @param \Et\Locales_Timezone|string $target_timezone
	 */
	public function setTargetTimezone($target_timezone) {
		$this->target_timezone = Locales::getTimezone($target_timezone);
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
		return $formatter->format($this->datetime);
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
		$formatter = $this->getFormatter($date_style, self::STYLE_NONE);
		return $formatter->format($this->datetime);
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
		$formatter = $this->getFormatter(self::STYLE_NONE, $time_style);
		return $formatter->format($this->datetime);
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
		return $formatter->format($this->datetime);
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