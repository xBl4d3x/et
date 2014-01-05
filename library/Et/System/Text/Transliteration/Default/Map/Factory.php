<?php
namespace Et;
et_require("Object");
class System_Text_Transliteration_Default_Map_Factory extends Object {

	const CLASS_NAMES_PREFIX = 'Et\System_Text_Transliteration_Default_Map_';

	/**
	 * @param string $map_class_postfix
	 *
	 * @return string
	 */
	public static function getMapClassName($map_class_postfix){
		Debug_Assert::isVariableName($map_class_postfix);
		$class_name = static::CLASS_NAMES_PREFIX . $map_class_postfix;
		return Factory::getClassName($class_name, 'Et\System_Text_Transliteration_Default_Map_Abstract');
	}

	/**
	 * @param string $map_class_postfix
	 *
	 * @return System_Text_Transliteration_Default_Map_Abstract
	 */
	public static function getMapInstance($map_class_postfix){
		$class_name = static::getMapClassName($map_class_postfix);
		return new $class_name();
	}
}