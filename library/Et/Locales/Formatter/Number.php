<?php
namespace Et;
et_require("Object");
class Locales_Formatter_Number extends Object {

	/**
	 * @var Locales_Locale
	 */
	protected $locale;

	/**
	 * @param Locales_Locale|string $locale
	 */
	function __construct($locale){
		$this->locale = Locales::getLocale($locale);
	}

	/**
	 * @return Locales_Locale
	 */
	function getLocale(){
		return $this->locale;
	}

	/**
	 * @link http://www.php.net/manual/en/class.numberformatter.php
	 *
	 * @param int $style [optional] Default: \NumberFormatter::DECIMAL
	 * @param null|string $pattern [optional]
	 * @param array $formatter_attributes [optional]
	 * @param array $formatter_text_attributes [optional]
	 *
	 * @throws Locales_Exception
	 * @return \NumberFormatter
	 */
	protected function createNumberFormatter($style = null, $pattern = null, array $formatter_attributes = array(), array $formatter_text_attributes = array()){
		if(!$style){
			$style = \NumberFormatter::DECIMAL;
		}

		$formatter = \NumberFormatter::create((string)$this->locale, $style, $pattern);
		if(!$formatter || intl_is_failure(intl_get_error_code())) {

			throw new Locales_Exception(
				"Number formatter creation failed - error ".intl_get_error_code().": " . intl_get_error_message(),
				Locales_Exception::CODE_FORMATTER_FAILURE
			);
		}

		foreach($formatter_attributes as $attribute => $value){
			$formatter->setAttribute($attribute, $value);
			if(intl_is_failure(intl_get_error_code())) {

				throw new Locales_Exception(
					"Failed to set formatter attribute - error ".intl_get_error_code().": " . intl_get_error_message(),
					Locales_Exception::CODE_FORMATTER_FAILURE
				);
			}
		}

		foreach($formatter_text_attributes as $attribute => $value){
			$formatter->setTextAttribute($attribute, $value);
			if(intl_is_failure(intl_get_error_code())) {

				throw new Locales_Exception(
					"Failed to set formatter text attribute - error ".intl_get_error_code().": " . intl_get_error_message(),
					Locales_Exception::CODE_FORMATTER_FAILURE
				);
			}
		}

		return $formatter;
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
		Debug_Assert::isNumber($number);
		if($max_fraction_digits !== null){
			$max_fraction_digits = max(0, (int)$max_fraction_digits);
			$formatter_attributes[\NumberFormatter::MAX_FRACTION_DIGITS] = $max_fraction_digits;
		}
		$formatter = $this->createNumberFormatter(\NumberFormatter::DECIMAL, null, $formatter_attributes);
		return $formatter->format($number);
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
		$size_in_bytes = max(0, (int)$size_in_bytes);
		$target_size = $size_in_bytes;
		if($target_units){
			$target_units = rtrim(strtoupper($target_units), "B");
			switch($target_units){
				case "":
					break;
				case "K":
					$target_size /= (1024 << 0);
					break;
				case "M":
					$target_size /= (1024 << 10);
					break;
				case "G":
					$target_size /= (1024 << 20);
					break;
				case "T":
					$target_size /= (1024 << 30);
					break;
				case "P":
					$target_size /= (1024 << 40);
					break;

				default:

					throw new Locales_Exception(
						"Invalid target units - must be one of B, KB, MB, GB, TB, PB",
						Locales_Exception::CODE_FORMATTER_FAILURE
					);
			}
		} else {
			$units = array("K", "M", "G", "T", "P");

			$target_units = "";
			while($target_size >= 1024 && $units){
				$target_units = array_shift($units);
				$target_size /= 1024;
			}
		}

		return $this->formatNumber($target_size, $max_fraction_digits, $formatter_attributes) . " {$target_units}B";
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

		Debug_Assert::isNumber($amount);
		if($max_fraction_digits !== null){
			$max_fraction_digits = max(0, (int)$max_fraction_digits);
			$formatter_attributes[\NumberFormatter::MAX_FRACTION_DIGITS] = $max_fraction_digits;
		}

		$formatter = $this->createNumberFormatter(\NumberFormatter::CURRENCY, null, $formatter_attributes);
		$formatted = $formatter->formatCurrency($amount, $currency);
		return $formatted;
	}
}