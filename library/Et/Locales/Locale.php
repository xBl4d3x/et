<?php
namespace Et;
et_require("Object");
class Locales_Locale extends Object implements \JsonSerializable {

	/**
	 * @var string
	 */
	protected $language;

	/**
	 * @var string
	 */
	protected $region;

	/**
	 * @var string
	 */
	protected $locale;


	/**
	 * @param string $locale
	 *
	 * @throws Locales_Exception
	 */
	function __construct($locale){
		$locale = (string)$locale;
		Locales::checkLocale($locale);
		$this->locale = $locale;
		list($this->language, $this->region) = explode("_", $this->locale);
	}

	/**
	 * @return string
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * @return string
	 */
	public function getRegion() {
		return $this->region;
	}

	/**
	 * @param string $locale
	 * @return Locales_Locale
	 */
	public static function getInstance($locale){
		if($locale instanceof Locales_Locale){
			return $locale;
		}
		return new static($locale);
	}

	/**
	 * @param string $delimiter [optional] Default: '_'
	 * @param bool $lower_case [optional]
	 *
	 * @return string
	 */
	function getLocale($delimiter = '_', $lower_case = false){

		if($delimiter == '_'){
			$locale = $this->locale;
		} else {
			$locale = $this->language . $delimiter . $this->region;
		}

		if($lower_case){
			$locale = strtolower($locale);
		}

		return $locale;
	}

	/**
	 * @return string
	 */
	function __toString(){
		return $this->locale;
	}

	/**
	 * @param null|string|Locales_Locale $output_locale [optional] If NULL, application locale is used
	 *
	 * @return string
	 */
	function getLanguageName($output_locale = null){
		return \Locale::getDisplayLanguage($this->locale, (string)Locales::getLocale($output_locale));
	}

	/**
	 * @param null|string|Locales_Locale $output_locale [optional] If NULL, application locale is used
	 *
	 * @return string
	 */
	function getRegionName($output_locale = null){
		return \Locale::getDisplayRegion($this->locale, (string)Locales::getLocale($output_locale));
	}

	/**
	 * @param null|string|Locales_Locale $output_locale [optional] If NULL, application locale is used
	 *
	 * @return string
	 */
	function getLocaleName($output_locale = null){
		return \Locale::getDisplayName($this->locale, (string)Locales::getLocale($output_locale));
	}

	/**
	 * @return Locales_Formatter_Message
	 */
	public function getMessageFormatter(){
		return new Locales_Formatter_Message($this);
	}


	/**
	 * @link http://userguide.icu-project.org/formatparse/messages
	 * @link http://icu-project.org/apiref/icu4c/classMessageFormat.html#_details
	 * @link http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html
	 *
	 * @param string $message
	 * @param array $data [optional]
	 *
	 * @throws Locales_Exception
	 * @return string
	 */
	function formatMessage($message, array $data = array()){
		return $this->getMessageFormatter()->formatMessage($message, $data);
	}

	/**
	 * @link http://www.php.net/manual/en/class.numberformatter.php
	 *
	 * @param int|float $number
	 * @param null|int $max_fraction_digits [optional]
	 * @param array $formatter_attributes [optional]
	 *
	 * @throws Locales_Exception
	 * @return string
	 */
	function formatNumber($number, $max_fraction_digits = null, array $formatter_attributes = array()){
		return $this->getNumberFormatter($number, $max_fraction_digits, $formatter_attributes);
	}

	/**
	 * @param int $size_in_bytes
	 * @param null|string $target_units [optional] NULL = best unit, where output value is greater than 0
	 * @param int $max_fraction_digits [optional]
	 * @param array $formatter_attributes [optional]
	 *
	 * @return string
	 * @throws Locales_Exception
	 */
	function formatSize($size_in_bytes, $target_units = null, $max_fraction_digits = 3, array $formatter_attributes = array()){
		return $this->getNumberFormatter($size_in_bytes, $target_units, $max_fraction_digits, $formatter_attributes);
	}

	/**
	 * @param int|float $amount
	 * @param string $currency in 3-letter ISO format (like USD)
	 * @param null|int $max_fraction_digits [optional]
	 * @param array $formatter_attributes [optional]
	 *
	 * @throws Locales_Exception
	 * @return string
	 */
	function formatCurrency($amount, $currency, $max_fraction_digits = null, array $formatter_attributes = array()){
		return $this->getNumberFormatter($amount, $currency, $max_fraction_digits, $formatter_attributes);
	}

	/**
	 * @return Locales_Formatter_Number
	 */
	function getNumberFormatter(){
		return new Locales_Formatter_Number($this);
	}

	/**
	 * @param Locales_DateTime $datetime
	 * @param null|string|\DateTimeZone $target_timezone [optional]
	 *
	 * @return Locales_Formatter_DateTime
	 */
	function getDateTimeFormatter(Locales_DateTime $datetime, $target_timezone = null){
		return new Locales_Formatter_DateTime($datetime, $this, $target_timezone);
	}

	/**
	 * @param Locales_DateTime $datetime
	 * @param null|int $date_style [optional]
	 * @param null|string|\DateTimeZone $target_timezone [optional]
	 *
	 * @return string
	 */
	function formatDate(Locales_DateTime $datetime, $date_style = null, $target_timezone = null){
		return $this->getDateTimeFormatter($datetime, $target_timezone)->formatDate($date_style);
	}

	/**
	 * @param Locales_DateTime $datetime
	 * @param null|int $time_style [optional]
	 * @param null|string|\DateTimeZone $target_timezone [optional]
	 *
	 * @return string
	 */
	function formatTime(Locales_DateTime $datetime, $time_style = null, $target_timezone = null){
		return $this->getDateTimeFormatter($datetime, $target_timezone)->formatTime($time_style);
	}

	/**
	 * @param Locales_DateTime $datetime	 *
	 * @param null|int $date_style [optional]
	 * @param null|int $time_style [optional]
	 * @param null|string|\DateTimeZone $target_timezone [optional]
	 *
	 * @return string
	 */
	function formatDateTime(Locales_DateTime $datetime, $date_style = null, $time_style = null, $target_timezone = null){
		return $this->getDateTimeFormatter($datetime, $target_timezone)->formatDateTime($date_style, $time_style);
	}

	/**
	 * @return \Collator
	 */
	function getCollator(){
		return new \Collator($this->locale);
	}

	/**
	 * @return string
	 */
	public function jsonSerialize() {
		return $this->locale;
	}
}