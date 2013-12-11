<?php
namespace Et;
et_require("Object");
/**
 * Message formatter
 *
 * @link http://userguide.icu-project.org/formatparse/messages
 * @link http://icu-project.org/apiref/icu4c/classMessageFormat.html#_details
 * @link http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.ht
 * @link http://php.net/manual/en/class.messageformatter.php
 */
class Locales_Formatter_Message extends Object {

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
	 * @param string $message_pattern
	 * @throws Locales_Exception
	 * @return \MessageFormatter
	 */
	public function getFormatter($message_pattern = "_"){
		$formatter = \MessageFormatter::create((string)$this->locale, (string)$message_pattern);
		if(!$formatter || intl_is_failure(intl_get_error_code())){

			throw new Locales_Exception(
				"Failed to create formatter - intl error ". intl_get_error_message()." [code ".intl_get_error_code()."] occurred",
				Locales_Exception::CODE_FORMATTER_FAILURE
			);
		}
		return $formatter;
	}

	/**
	 * @param $message [reference]
	 * @param array $arguments [reference[
	 */
	protected function prepareMessageForFormatter(&$message, array &$arguments){
		// double quote single quotes
		if(strpos($message, "'") !== false){
			$message = preg_replace('~\'+~', '\'\'', $message);
		}

		// no arguments
		if(!$arguments){
			return;
		}

		// numeric parameters
		if(isset($arguments[0]) && array_keys($arguments) === range(0, count($arguments) - 1)){
			return;
		}

		// find data keys and replace them for numbers
		$message_parts = preg_split('~(\{\s*\w+)~s', $message, -1, PREG_SPLIT_DELIM_CAPTURE);
		$new_arguments = array();
		$idx = 0;

		foreach($message_parts as $i => $part){
			if($part[0] != "{"){
				continue;
			}

			$argument = preg_replace('~^[^\w]+~', '', $part);
			if(isset($args[$argument])){
				$new_arguments[] = $args[$argument];
				$message_parts[$i] = str_replace($argument, $idx++, $part);
			}
		}

		$message = implode("", $message_parts);
		$arguments = $new_arguments;
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
	function formatMessage($message, array $arguments = array()){
		$message = (string)$message;
		if(!$arguments || strpos($message, "{") === false){
			return $message;
		}

		$this->prepareMessageForFormatter($message, $arguments);
		$formatter = $this->getFormatter();

		if(!$formatter->setPattern($message) || intl_is_failure(intl_get_error_code())){
			throw new Locales_Exception(
				"Failed to set message pattern to formatter - intl error ".intl_get_error_message()." [code ".intl_get_error_code()."] occurred",
				Locales_Exception::CODE_FORMATTER_FAILURE,
				array(
				     "message to format" => $message,
				     "message arguments" => $arguments
				)
			);
		}

		$formatted = $formatter->format($arguments);
		if($formatted === false  || intl_is_failure(intl_get_error_code())){

			throw new Locales_Exception(
				"Failed to format message - intl error ".intl_get_error_message()." [code ".intl_get_error_code()."] occurred",
				Locales_Exception::CODE_FORMATTER_FAILURE,
				array(
				     "message to format" => $message,
				     "message arguments" => $arguments
				)
			);
		}

		return $formatted;
	}
}