<?php
namespace Et;
et_require("System_Text_Transliteration_Default_Map_Abstract");
class System_Text_Transliteration_Default_Map_Latvian extends System_Text_Transliteration_Default_Map_Abstract {

	/**
	 * @var string
	 */
	protected $name = "latvian";

	/**
	 * @var array
	 */
	protected $characters_map = array (
		'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
		'š' => 's', 'ū' => 'u', 'ž' => 'z', 'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i',
		'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z'
	);
}