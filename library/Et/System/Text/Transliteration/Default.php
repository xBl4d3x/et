<?php
namespace Et;
et_require("System_Text_Transliteration_Abstract");
class System_Text_Transliteration_Default extends System_Text_Transliteration_Abstract {

	/**
	 * @var \Transliterator
	 */
	protected $transliterator;

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	function transliterate($text) {
		return $this->transliterator->transliterate($text);
	}

	/**
	 * Object initialization
	 */
	function initialize() {
		$this->transliterator = \Transliterator::create("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove;");
	}
}