<?php
namespace Et;
abstract class Entity_Definition_Abstract extends Object {

	protected $entity_class_name;

	protected $entity_name;

	protected $properties;

	protected $relations = array();

}