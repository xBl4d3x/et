<?php
namespace Et;
abstract class System_Text_Transliteration_Default_Map_Abstract {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var bool
	 */
	protected $enabled = true;

	/**
	 * @var array
	 */
	protected $characters_map = array();

	/**
	 * @return System_Text_Transliteration_Default_Map_Abstract
	 */
	function enable(){
		return $this->setEnabled(true);
	}

	/**
	 * @return System_Text_Transliteration_Default_Map_Abstract
	 */
	function disable(){
		return $this->setEnabled(false);
	}

	/**
	 * @return bool
	 */
	function isEnabled(){
		return $this->enabled;
	}

	/**
	 * @param boolean $enabled
	 *
	 * @return System_Text_Transliteration_Default_Map_Abstract
	 */
	public function setEnabled($enabled) {
		$this->enabled =(bool) $enabled;
		return $this;
	}

	/**
	 * @return string
	 */
	function getName(){
		return $this->name;
	}

	/**
	 * @return array
	 */
	function getCharactersMap(){
		return $this->characters_map;
	}

	/**
	 * @param string $character
	 * @param string $transliteration
	 *
	 * @return System_Text_Transliteration_Default_Map_Abstract
	 */
	function addCharacter($character, $transliteration){
		$this->characters_map[$character] = $transliteration;
		return $this;
	}

	/**
	 * @param string $character
	 *
	 * @return System_Text_Transliteration_Default_Map_Abstract
	 */
	function removeCharacter($character){
		if(isset($this->characters_map[$character])){
			unset($this->characters_map[$character]);
		}
		return $this;
	}

	/**
	 * @param string $character
	 *
	 * @return bool
	 */
	function isCharacterDefined($character){
		return isset($this->characters_map[$character]);
	}

	/**
	 * @param string $character
	 *
	 * @return string
	 */
	function transliterateCharacter($character){
		if(!$this->isEnabled()){
			return $character;
		}

		return isset($this->characters_map[$character])
				? $this->characters_map[$character]
				: $character;
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	function transliterateText($text){
		if(!$this->isEnabled()){
			return $text;
		}
		return strtr($text, $this->characters_map);
	}
}
