<?php
namespace Et;
et_require("Object");
abstract class System_Text_Transliteration_Abstract extends Object {

	function __construct(){
		$this->initialize();
	}

	/**
	 * Object initialization
	 */
	abstract function initialize();

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	abstract function transliterate($text);

	/**
	 * @param string $text
	 * @param string $delimiter [optional]
	 * @param bool $lower_case [optional]
	 * @param int $maximal_length [optional]
	 *
	 * @return mixed
	 */
	function createIdentifier($text, $delimiter = "-", $lower_case = true, $maximal_length = 64){
		$text = trim($text);
		if(!$text){
			return $text;
		}

		$transliterated = $this->transliterate($text);
		if($lower_case){
			$transliterated = strtolower($transliterated);
		}

		$identifier = preg_replace("~[^a-zA-Z0-9]+~", $delimiter, $transliterated);
		$identifier = preg_replace("~({$delimiter})+~", $delimiter, $identifier);
		$identifier = trim($identifier, $delimiter);

		if($maximal_length > 0 && strlen($identifier) > $maximal_length){
			$identifier = substr($maximal_length, 0, $maximal_length);
			$identifier = trim($identifier, $delimiter);
		}

		return $identifier;
	}


}