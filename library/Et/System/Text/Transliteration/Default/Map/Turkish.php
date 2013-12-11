<?php
namespace Et;
et_require("System_Text_Transliteration_Default_Map_Abstract");
class System_Text_Transliteration_Default_Map_Turkish extends System_Text_Transliteration_Default_Map_Abstract {

	/**
	 * @var string
	 */
	protected $name = "turkish";

	/**
	 * @var array
	 */
	protected $characters_map = array (
		'ş' => 's', 'Ş' => 'S', 'ı' => 'i', 'İ' => 'I',
		'ç' => 'c', 'Ç' => 'C', 'ü' => 'u', 'Ü' => 'U',
		'ö' => 'o', 'Ö' => 'O', 'ğ' => 'g', 'Ğ' => 'G'
	);
}