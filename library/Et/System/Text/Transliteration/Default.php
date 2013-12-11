<?php
namespace Et;
et_require("System_Text_Transliteration_Abstract");
class System_Text_Transliteration_Default extends System_Text_Transliteration_Abstract {

	/**
	 * @var System_Text_Transliteration_Default_Map
	 */
	protected $transliteration_map;

	/**
	 * @return System_Text_Transliteration_Default_Map
	 */
	public function getTransliterationMap() {
		if(!$this->transliteration_map){
			$this->transliteration_map = new System_Text_Transliteration_Default_Map();
		}
		return $this->transliteration_map;
	}

	/**
	 * @param System_Text_Transliteration_Default_Map $map
	 */
	function setTransliterationMap(System_Text_Transliteration_Default_Map $map){
		$this->transliteration_map = $map;
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	function transliterate($text) {
		$transliterated = $this->getTransliterationMap()->transliterate($text);

		/** @noinspection SpellCheckingInspection */
		if(function_exists("transliterator_transliterate")){
			/** @noinspection SpellCheckingInspection */
			$transliterated = transliterator_transliterate('NFD; [:Nonspacing Mark:] Remove; NFC', $transliterated);
		}

		$transliterated = preg_replace('/\p{Mn}/u', '', \Normalizer::normalize($transliterated, \Normalizer::FORM_KD));

		return $transliterated;
	}

	/**
	 * Object initialization
	 */
	function initialize() {
	}
}