<?php
namespace Et;
abstract class Entity_Part_Abstract extends Entity_Abstract {

	/**
	 * @var string|Entity_Main
	 */
	protected static $_main_entity_class;

	/**
	 * @var string|Entity_Main|Entity_Part_Abstract
	 */
	protected static $_parent_entity_class;

	protected $_parent_ID;

	protected $_main_ID;

	protected $_parent_entity;

	protected $_main_entity;

	public static function getByParentID($ID){

	}

	public static function getByParentIDs($ID){

	}

	public static function getByMainID($ID){

	}

	public static function getByMainIDs($ID){

	}


}