<?php
namespace Et;
et_require("System_Text_Transliteration_Default_Map_Abstract");
class System_Text_Transliteration_Default_Map_Czech extends System_Text_Transliteration_Default_Map_Abstract {

	/**
	 * @var string
	 */
	protected $name = "czech";

	/**
	 * @var array
	 */
	protected $characters_map = array (
		'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
		'ž' => 'z', 'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T',
		'Ů' => 'U', 'Ž' => 'Z'
	);
}