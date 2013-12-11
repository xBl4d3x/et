<?php
namespace Et;
et_require("System_Text_Transliteration_Default_Map_Abstract");
class System_Text_Transliteration_Default_Map_Symbols extends System_Text_Transliteration_Default_Map_Abstract {

	/**
	 * @var string
	 */
	protected $name = "symbols";

	/**
	 * @var array
	 */
	protected $characters_map = array (
		'©' => '(c)',
		'®' => '(r)'
	);
}