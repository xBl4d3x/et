<?php
namespace Et;
et_require("System_Text_Transliteration_Default_Map_Abstract");
class System_Text_Transliteration_Default_Map_Polish extends System_Text_Transliteration_Default_Map_Abstract {

	/**
	 * @var string
	 */
	protected $name = "polish";

	/**
	 * @var array
	 */
	protected $characters_map = array (
		'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
		'ż' => 'z', 'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'O', 'Ś' => 'S',
		'Ź' => 'Z', 'Ż' => 'Z'
	);
}