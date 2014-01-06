<?php
namespace Et;
class Locales {

	const CURRENT_LOCALE = "_current_";
	const APPLICATION_LOCALE = "_application_";
	const USER_LOCALE = "_user_";
	
	const CURRENT_TIMEZONE = "_current_";
	const APPLICATION_TIMEZONE = "_application_";
	const USER_TIMEZONE = "_user_";
	const SYSTEM_TIMEZONE = "_system_";

	/**
	 * @var array
	 */
	protected static $locale_names = array(
		'sq_AL' => 'Albanian (Albania) ',
		'ar_DZ' => 'Arabic (Algeria) ',
		'ar_BH' => 'Arabic (Bahrain) ',
		'ar_EG' => 'Arabic (Egypt) ',
		'ar_IQ' => 'Arabic (Iraq) ',
		'ar_JO' => 'Arabic (Jordan) ',
		'ar_KW' => 'Arabic (Kuwait) ',
		'ar_LB' => 'Arabic (Lebanon) ',
		'ar_LY' => 'Arabic (Libya) ',
		'ar_MA' => 'Arabic (Morocco) ',
		'ar_OM' => 'Arabic (Oman) ',
		'ar_QA' => 'Arabic (Qatar) ',
		'ar_SA' => 'Arabic (Saudi Arabia) ',
		'ar_SD' => 'Arabic (Sudan) ',
		'ar_SY' => 'Arabic (Syria) ',
		'ar_TN' => 'Arabic (Tunisia) ',
		'ar_AE' => 'Arabic (United Arab Emirates) ',
		'ar_YE' => 'Arabic (Yemen) ',
		'be_BY' => 'Belarusian (Belarus) ',
		'bg_BG' => 'Bulgarian (Bulgaria) ',
		'ca_ES' => 'Catalan (Spain) ',
		'zh_CN' => 'Chinese (China) ',
		'zh_HK' => 'Chinese (Hong Kong) ',
		'zh_SG' => 'Chinese (Singapore) ',
		'zh_TW' => 'Chinese (Taiwan) ',
		'hr_HR' => 'Croatian (Croatia) ',
		'cs_CZ' => 'Czech (Czech Republic) ',
		'da_DK' => 'Danish (Denmark) ',
		'nl_BE' => 'Dutch (Belgium) ',
		'nl_NL' => 'Dutch (Netherlands) ',
		'en_AU' => 'English (Australia) ',
		'en_CA' => 'English (Canada) ',
		'en_IN' => 'English (India) ',
		'en_IE' => 'English (Ireland) ',
		'en_MT' => 'English (Malta) ',
		'en_NZ' => 'English (New Zealand) ',
		'en_PH' => 'English (Philippines) ',
		'en_SG' => 'English (Singapore) ',
		'en_ZA' => 'English (South Africa) ',
		'en_GB' => 'English (United Kingdom) ',
		'en_US' => 'English (United States) ',
		'et_EE' => 'Estonian (Estonia) ',
		'fi_FI' => 'Finnish (Finland) ',
		'fr_BE' => 'French (Belgium) ',
		'fr_CA' => 'French (Canada) ',
		'fr_FR' => 'French (France) ',
		'fr_LU' => 'French (Luxembourg) ',
		'fr_CH' => 'French (Switzerland) ',
		'de_AT' => 'German (Austria) ',
		'de_DE' => 'German (Germany) ',
		'de_LU' => 'German (Luxembourg) ',
		'de_CH' => 'German (Switzerland) ',
		'el_CY' => 'Greek (Cyprus) ',
		'el_GR' => 'Greek (Greece) ',
		'iw_IL' => 'Hebrew (Israel) ',
		'hi_IN' => 'Hindi (India) ',
		'hu_HU' => 'Hungarian (Hungary) ',
		'is_IS' => 'Icelandic (Iceland) ',
		'in_ID' => 'Indonesian (Indonesia) ',
		'ga_IE' => 'Irish (Ireland) ',
		'it_IT' => 'Italian (Italy) ',
		'it_CH' => 'Italian (Switzerland) ',
		'ja_JP' => 'Japanese (Japan) ',
		'ko_KR' => 'Korean (South Korea) ',
		'lv_LV' => 'Latvian (Latvia) ',
		'lt_LT' => 'Lithuanian (Lithuania) ',
		'mk_MK' => 'Macedonian (Macedonia) ',
		'ms_MY' => 'Malay (Malaysia) ',
		'mt_MT' => 'Maltese (Malta) ',
		'no_NO' => 'Norwegian (Norway) ',
		'pl_PL' => 'Polish (Poland) ',
		'pt_BR' => 'Portuguese (Brazil) ',
		'pt_PT' => 'Portuguese (Portugal) ',
		'ro_RO' => 'Romanian (Romania) ',
		'ru_RU' => 'Russian (Russia) ',
		'sr_BA' => 'Serbian (Bosnia and Herzegovina) ',
		'sr_ME' => 'Serbian (Montenegro) ',
		'sr_CS' => 'Serbian (Serbia and Montenegro) ',
		'sr_RS' => 'Serbian (Serbia) ',
		'sk_SK' => 'Slovak (Slovakia) ',
		'sl_SI' => 'Slovenian (Slovenia) ',
		'es_AR' => 'Spanish (Argentina) ',
		'es_BO' => 'Spanish (Bolivia) ',
		'es_CL' => 'Spanish (Chile) ',
		'es_CO' => 'Spanish (Colombia) ',
		'es_CR' => 'Spanish (Costa Rica) ',
		'es_DO' => 'Spanish (Dominican Republic) ',
		'es_EC' => 'Spanish (Ecuador) ',
		'es_SV' => 'Spanish (El Salvador) ',
		'es_GT' => 'Spanish (Guatemala) ',
		'es_HN' => 'Spanish (Honduras) ',
		'es_MX' => 'Spanish (Mexico) ',
		'es_NI' => 'Spanish (Nicaragua) ',
		'es_PA' => 'Spanish (Panama) ',
		'es_PY' => 'Spanish (Paraguay) ',
		'es_PE' => 'Spanish (Peru) ',
		'es_PR' => 'Spanish (Puerto Rico) ',
		'es_ES' => 'Spanish (Spain) ',
		'es_US' => 'Spanish (United States) ',
		'es_UY' => 'Spanish (Uruguay) ',
		'es_VE' => 'Spanish (Venezuela) ',
		'sv_SE' => 'Swedish (Sweden) ',
		'th_TH' => 'Thai (Thailand) ',
		'tr_TR' => 'Turkish (Turkey) ',
		'uk_UA' => 'Ukrainian (Ukraine) ',
		'vi_VN' => 'Vietnamese (Vietnam) ',
	);

	/**
	 * @var array
	 */
	protected static $language_names;

	/**
	 * @var array[]
	 */
	protected static $locale_names_localized = array();

	/**
	 * @var array[]
	 */
	protected static $language_names_localized = array();

	/**
	 * @var \Et\Locales_Locale[]
	 */
	protected static $locales = array();

	/**
	 * @var \Et\Locales_Timezone[]
	 */
	protected static $timezones = array();

	/**
	 * @var array
	 */
	protected static $timezone_names;

	/**
	 * @var \Et\Locales_Locale
	 */
	protected static $current_locale;
	
	/**
	 * @var \Et\Locales_Timezone
	 */
	protected static $current_timezone;
	

	/**
	 * @param string $locale
	 *
	 * @return string
	 * @throws Locales_Exception
	 */
	public static function getLocaleName($locale){
		self::checkLocale($locale);
		return static::$locale_names[(string)$locale];
	}

	/**
	 * @return array
	 */
	public static function getLocaleNames(){
		return static::$locale_names;
	}
	

	/**
	 * @param string|Locales_Locale $target_locale [optional]
	 * @return array
	 */
	public static function getLocaleNamesLocalized($target_locale = self::CURRENT_LOCALE){

		$target_locale = static::getLocale($target_locale);
		$target_locale_string = (string)$target_locale;
		if(isset(self::$locale_names_localized[$target_locale_string])){
			return self::$locale_names_localized[$target_locale_string];
		}

		self::$locale_names_localized[$target_locale_string] = array();
		foreach(static::$locale_names as $locale => $name){
			self::$locale_names_localized[$target_locale_string][$locale] = \Locale::getDisplayName($locale, $target_locale_string);
		}

		/** @noinspection PhpUndefinedMethodInspection */
		$target_locale->getCollator()->asort(self::$locale_names_localized[$target_locale_string]);

		return self::$locale_names_localized[$target_locale_string];
	}

	/**
	 * @return array
	 */
	public static function getLanguageNames(){
		if(self::$language_names){
			return self::$language_names;
		}

		foreach(self::$locale_names as $locale => $name){
			$language = substr($locale, 0, 2);
			list($language_name) = explode("(", $name, 2);
			self::$language_names[$language] = trim($language_name);
		}

		asort(self::$language_names);
		return self::$language_names;
	}

	/**
	 * @return array
	 */
	public static function getLanguageCodes(){
		$codes = array_keys(static::getLanguageNames());
		sort($codes);
		return $codes;
	}

	/**
	 * @param Locales_Locale|string $target_locale [optional]
	 * @return array
	 */
	public static function getLanguageNamesLocalized($target_locale = self::CURRENT_LOCALE){

		$target_locale = static::getLocale($target_locale);
		$target_locale_string = (string)$target_locale;

		if(isset(self::$language_names_localized[$target_locale_string])){
			return self::$language_names_localized[$target_locale_string];
		}
		
		$languages_codes = static::getLanguageNames();

		self::$language_names_localized[$target_locale_string] = array();
		foreach($languages_codes as $language){
			self::$language_names_localized[$target_locale_string][$language] = \Locale::getDisplayLanguage($language, $target_locale_string);
		}

		$target_locale->getCollator()->asort(self::$language_names_localized[$target_locale_string]);

		return self::$language_names_localized[$target_locale_string];
	}

	/**
	 * @param string $language_code
	 * @param Locales_Locale|string $target_locale
	 * @return bool|string
	 */
	public static function getLanguageNameLocalized($language_code, $target_locale = self::CURRENT_LOCALE){
		$names = static::getLanguageNamesLocalized($target_locale);
		return isset($names[$language_code])
				? $names[$language_code]
				: false;
	}

	/**
	 * @param \Et\Locales_Locale $current_locale
	 */
	public static function setCurrentLocale($current_locale) {
		static::$current_locale = self::getLocale($current_locale);
	}

	/**
	 * @return \Et\Locales_Locale
	 */
	public static function getCurrentLocale() {
		if(!static::$current_locale){
			return self::getLocale(ET_DEFAULT_LOCALE);
		}
		return static::$current_locale;
	}

	/**
	 * @param \Et\Locales_Timezone $current_timezone
	 */
	public static function setCurrentTimezone($current_timezone) {
		static::$current_timezone = self::getTimezone($current_timezone);
	}

	/**
	 * @return \Et\Locales_Timezone
	 */
	public static function getCurrentTimezone() {
		if(!static::$current_timezone){
			return self::getSystemTimezone();
		}
		return static::$current_timezone;
	}
	
	

	/**
	 * @param string $locale
	 *
	 * @throws Locales_Exception
	 */
	public static function checkLocale($locale){
		if(!self::getLocaleExists($locale)){
			throw new Locales_Exception(
				"Invalid locale '{$locale}' - not found in locales list",
				Locales_Exception::CODE_INVALID_LOCALE
			);
		}
	}

	/**
	 * @param string $locale
	 * @return bool
	 */
	public static function getLocaleExists($locale){
		if($locale instanceof Locales_Locale){
			return true;
		}

		$locale = (string)$locale;
		if(isset(static::$locale_names[$locale])){
			return true;
		}

		return in_array($locale, array(
			self::CURRENT_LOCALE,
			self::APPLICATION_LOCALE,
			self::USER_LOCALE
		));
	}

	/**
	 * @param string $language
	 * @return bool
	 */
	public static function getLanguageExists($language){
		if(!static::$language_names){
			static::getLanguageNames();
		}
		return isset(static::$language_names[$language]);
	}

	/**
	 * @param string $language
	 * @throws Locales_Exception
	 *
	 */
	public static function checkLanguage($language){
		if(!static::getLanguageExists($language)){
			throw new Locales_Exception(
				"Language '{$language}' not exists",
				Locales_Exception::CODE_INVALID_LANGUAGE
			);
		}
	}


	/**
	 * @param null|string $accept_string [optional] NULL = use $_SERVER['HTTP_ACCEPT_LANGUAGE'] if set
	 *
	 * @return bool|Locales_Locale
	 */
	public static function getLocaleFromHttpAccept($accept_string = null){
		if($accept_string === null && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			$accept_string = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		}

		if(!$accept_string || !is_string($accept_string)){
			return false;
		}

		$locale = \Locale::acceptFromHttp($accept_string);
		if(!$locale){
			return false;
		}

		return static::getLocale($locale);
	}

	/**
	 * @param string|Locales_Locale|\Locale|null $locale If NULL, application locale is returned
	 *
	 * @return Locales_Locale
	 * @throws Locales_Exception
	 */
	public static function getLocale($locale){
		if($locale === static::CURRENT_LOCALE){
			return static::getCurrentLocale();
		}

		//todo: application and user locale

		if(isset(static::$locales[(string)$locale])){
			return static::$locales[(string)$locale];
		}

		static::$locales[$locale] = new Locales_Locale($locale);

		return static::$locales[$locale];
	}



	/**
	 * @param array $locales
	 *
	 * @return Locales_Locale[]
	 * @throws Locales_Exception
	 */
	public static function getLocales(array $locales){
		$output = array();
		foreach($locales as $locale){
			$output[(string)$locale] = static::getLocale($locale);
		}
		return $output;
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
	public static function formatNumber($number, $max_fraction_digits = null, array $formatter_attributes = array()){
		return static::getCurrentLocale()->formatNumber($number, $max_fraction_digits, $formatter_attributes);
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
	public static function formatSize($size_in_bytes, $target_units = null, $max_fraction_digits = 3, array $formatter_attributes = array()){
		return static::getCurrentLocale()->formatSize($size_in_bytes, $target_units, $max_fraction_digits, $formatter_attributes);
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
	public static function formatCurrency($amount, $currency, $max_fraction_digits = null, array $formatter_attributes = array()){
		return static::getCurrentLocale()->formatSize($amount, $currency, $max_fraction_digits, $formatter_attributes);
	}

	/**
	 * @link http://userguide.icu-project.org/formatparse/messages
	 * @link http://icu-project.org/apiref/icu4c/classMessageFormat.html#_details
	 * @link http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html
	 *
	 * @param string $message
	 * @param array $arguments [optional]
	 *
	 * @throws Locales_Exception
	 * @return string
	 */
	public static function formatMessage($message, array $arguments = array()){
		return static::getCurrentLocale()->formatMessage($message, $arguments);
	}

	/**
	 * @param string|int|\DateTime|Locales_DateTime $datetime
	 * @param null|int $date_style [optional]
	 * @param null|string|\DateTimeZone $target_timezone [optional]
	 *
	 * @return string
	 */
	function formatDate($datetime, $date_style = null, $target_timezone = null){
		return static::getCurrentLocale()->formatDate($datetime, $date_style, $target_timezone);
	}

	/**
	 * @param string|int|\DateTime|Locales_DateTime $datetime
	 * @param null|int $time_style [optional]
	 * @param null|string|\DateTimeZone $target_timezone [optional]
	 *
	 * @return string
	 */
	function formatTime($datetime, $time_style = null, $target_timezone = null){
		return static::getCurrentLocale()->formatTime($datetime, $time_style, $target_timezone);
	}

	/**
	 * @param string|int|\DateTime|Locales_DateTime $datetime	 *
	 * @param null|int $date_style [optional]
	 * @param null|int $time_style [optional]
	 * @param null|string|\DateTimeZone $target_timezone [optional]
	 *
	 * @return string
	 */
	function formatDateTime($datetime, $date_style = null, $time_style = null, $target_timezone = null){
		return static::getCurrentLocale()->formatDateTime($datetime, $date_style, $time_style, $target_timezone);
	}

	/**
	 * @param null|string|Locales_Locale $locale [optional]
	 * @return \Collator
	 */
	public static function getCollator($locale = self::CURRENT_LOCALE){
		return self::getLocale($locale)->getCollator();
	}

	/**
	 * @param string $header [optional]
	 * @return bool|\Et\Locales_Locale
	 */
	public static function detectLocaleFromHeader($header = null){
		if(!$header){
			if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
				return false;
			}
			$header = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		}

		$locale = \Locale::acceptFromHttp($header);
		if(!$locale || !static::getLocaleExists($locale)){
			return false;
		}

		return static::getLocale($locale);
	}

	/**
	 * @param string|Locales_Timezone $timezone
	 * @return \Et\Locales_Timezone
	 */
	public static function getTimezone($timezone){
		if(is_string($timezone)){
			if($timezone == static::CURRENT_TIMEZONE){
				return static::getCurrentTimezone();
			} elseif($timezone == static::SYSTEM_TIMEZONE){
				return static::getSystemTimezone();
			}

			//todo: application and user timezone

		} elseif($timezone instanceof Locales_Timezone){
			return $timezone;
		}

		$timezone = (string)$timezone;
		if(isset(static::$timezones[$timezone])){
			return static::$timezones[$timezone];
		}

		static::$timezones[$timezone] = new Locales_Timezone($timezone);
		return static::$timezones[$timezone];
	}

	/**
	 * @return array
	 */
	public static function getTimezoneNames() {
		if(static::$timezone_names){
			return static::$timezone_names;
		}

		static::$timezone_names = array();
		$timezones = \DateTimeZone::listAbbreviations();
		foreach($timezones as $tz){
			foreach($tz as $t){
				list(, $offset, $timezone) = array_values($t);
				if(!$timezone){
					continue;
				}

				$sign = $offset < 0 ? "-" : "+";

				$gmt_offset_hours = abs(round($offset / 3600));
				$label = str_replace("_", " ", $timezone);
				$label .= " (GMT{$sign}";

				if($gmt_offset_hours < 10){
					$gmt_offset_hours = "0{$gmt_offset_hours}";
				}

				$label .= "{$gmt_offset_hours}:00)";
				static::$timezone_names[$timezone] = $label;
			}
		}

		asort(static::$timezone_names);

		return static::$timezone_names;
	}

	/**
	 * @return \Et\Locales_Timezone
	 */
	public static function getSystemTimezone(){
		return static::getTimezone(\date_default_timezone_get());
	}

	/**
	 * @param null|string|int|\DateTime $date [optional]
	 * @param null|string|\DateTimeZone|\Et\Locales_Timezone $timezone [optional]
	 * @return \Et\Locales_Date
	 */
	public static function getDate($date, $timezone = null){
		if($date instanceof Locales_Date){
			return $date;
		}
		return new Locales_Date($date, $timezone);
	}

	/**
	 * @param null|string|\DateTimeZone|\Et\Locales_Timezone $timezone [optional]
	 * @return Locales_Date
	 */
	public static function getDateToday($timezone = null){
		return new Locales_Date("today", $timezone);
	}

	/**
	 * @param null|string|int|\DateTime $datetime [optional]
	 * @param null|string|\DateTimeZone|\Et\Locales_Timezone $timezone [optional]
	 * @return \Et\Locales_DateTime
	 */
	public static function getDateTime($datetime, $timezone = null){
		if($datetime instanceof Locales_DateTime){
			return $datetime;
		}
		return new Locales_DateTime($datetime, $timezone);
	}

	/**
	 * @param null|string|\DateTimeZone|\Et\Locales_Timezone $timezone [optional]
	 * @return \Et\Locales_DateTime
	 */
	public static function getDateTimeNow($timezone = null){
		return new Locales_DateTime("now", $timezone);
	}


}