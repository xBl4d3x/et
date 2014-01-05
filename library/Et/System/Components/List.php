<?php
namespace Et;
et_require("Object");
class System_Components_List extends Object implements \Countable,\Iterator,\ArrayAccess {

	/**
	 * @var string
	 */
	protected $components_type;

	/**
	 * @var System_Components_Component[]
	 */
	protected $components = array();

	/**
	 * @var bool
	 */
	protected $has_changed = false;

	/**
	 * @param string $components_type
	 */
	function __construct($components_type){
		Debug_Assert::isVariableName($components_type);
		$this->components_type = $components_type;
	}

	/**
	 * @return System_Components_Component[]
	 */
	public function getComponents() {
		return $this->components;
	}

	/**
	 * @return string
	 */
	public function getComponentsType() {
		return $this->components_type;
	}


	function __wakeup(){
		$this->has_changed = false;
	}

	/**
	 * @return bool
	 */
	function hasChanged(){
		if($this->has_changed){
			return true;
		}
		
		foreach($this->components as $component){
			if($component->hasChanged()){
				return true;
			}
		}
		
		return false;
	}

	/**
	 * @param string $component_name
	 * @param string $label [optional]
	 * @param string $description [optional]
	 * @return System_Components_Component
	 */
	function createComponent($component_name, $label = "", $description = ""){
		$component = new System_Components_Component($component_name, $label, $description);
		$this->addComponent($component);
		$this->has_changed = true;
		return $component;
	}


	/**
	 * @param string $component_name
	 * @param string $label [optional]
	 * @param string $description [optional]
	 * @param bool $store_component [optional]
	 * @return System_Components_Component
	 */
	function getOrCreateComponent($component_name, $label = "", $description = "", $store_component = true){
		$component = $this->getComponent($component_name);
		if(!$component){
			$component = $this->createComponent($component_name, $label, $description);
			if($store_component){
				$this->addComponent($component);
			}
		}
		return $component;
	}

	/**
	 * @param System_Components_Component $component
	 */
	function addComponent(System_Components_Component $component){
		$this->components[$component->getID()] = $component;
		$this->sortComponents();
	}

	/**
	 * @param bool $desc
	 */
	function sortComponents($desc = false){
		if($desc){
			krsort($this->components);
		} else {
			ksort($this->components);
		}
	}

	/**
	 * @param string $component_name
	 * @return bool
	 */
	function removeComponent($component_name){
		if(isset($this->components[$component_name])){
			unset($this->components[$component_name]);
			return true;
		}
		return false;
	}

	/**
	 * @param string $component_name
	 * @return bool
	 */
	function getComponentExists($component_name){
		return isset($this->components[$component_name]);
	}

	/**
	 * @param string $component_name
	 * @return System_Components_Component|bool
	 */
	function getComponent($component_name){
		if(isset($this->components[$component_name])){
			return $this->components[$component_name];
		}
		return false;
	}

	/**
	 * @return int
	 */
	function getComponentsCount(){
		return count($this->components);
	}

	/**
	 * @return array
	 */
	function getComponentsNames(){
		return array_keys($this->components);
	}
	

	/**
	 * @return System_Components_Component[]
	 */
	function getInstalledComponents(){
		$installed_components = array();
		foreach($this->components as $component_name => $component){
			if($component->isInstalled()){
				$installed_components[$component_name] = $component;
			}
		}
		return $installed_components;
	}

	/**
	 * @return int
	 */
	function getInstalledComponentsCount(){
		return count($this->getInstalledComponents());	
	}

	/**
	 * @return array
	 */
	function getInstalledComponentsNames(){
		return array_keys($this->getInstalledComponents());
	}

	/**
	 * @param string $component_name
	 * @return bool
	 */
	function getComponentIsInstalled($component_name){
		$component = $this->getComponent($component_name);
		return $component && $component->isInstalled();
	}


	/**
	 * @return System_Components_Component[]
	 */
	function getEnabledComponents(){
		$enabled_components = array();
		foreach($this->components as $component_name => $component){
			if($component->isEnabled()){
				$enabled_components[$component_name] = $component;
			}
		}
		return $enabled_components;
	}

	/**
	 * @return int
	 */
	function getEnabledComponentsCount(){
		return count($this->getEnabledComponents());
	}

	/**
	 * @return array
	 */
	function getEnabledComponentsNames(){
		return array_keys($this->getEnabledComponents());
	}

	/**
	 * @param string $component_name
	 * @return bool
	 */
	function getComponentIsEnabled($component_name){
		$component = $this->getComponent($component_name);
		return $component && $component->isEnabled();
	}


	/**
	 * @return System_Components_Component|bool
	 */
	public function current() {
		return current($this->components);
	}
	
	public function next() {
		next($this->components);
	}
	
	/**
	 * @return string|null
	 */
	public function key() {
		return key($this->components);
	}
	
	/**
	 * @return bool
	 */
	public function valid() {
		return key($this->components) !== null;
	}
	
	
	public function rewind() {
		reset($this->components);
	}
	
	/**
	 * @param string $component_name
	 * @return bool
	 */
	public function offsetExists($component_name) {
		return $this->getComponentExists($component_name);
	}
	
	/**
	 * @param string $component_name
	 * @return bool|System_Components_Component
	 */
	public function offsetGet($component_name) {
		return $this->getComponent($component_name);
	}
	
	
	public function offsetSet($component_name, $value) {
		$this->addComponent($value);
	}
	
	
	public function offsetUnset($component_name) {
		$this->removeComponent($component_name);
	}

	/**
	 * @return int
	 */
	public function count() {
		return $this->getComponentsCount();
	}

	/**
	 * @return string
	 */
	function __toString(){
		return $this->getComponentsType();
	}
}