<?php
namespace Et;
et_require('Debug_Profiler_Period');
et_require('Debug_Profiler_Milestone');
abstract class Debug_Profiler_Abstract implements \JsonSerializable  {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var bool
	 */
	protected $enabled = ET_PROFILE_MODE;

	/**
	 * @var Debug_Profiler_Milestone[]
	 */
	protected $milestones = array();

	/**
	 * @var Debug_Profiler_Period[]
	 */
	protected $periods = array();

	/**
	 * @param string $name
	 */
	function __construct($name){
		$this->name = trim($name);
		$this->enabled = Debug_Profiler::getProfilingEnabled();
	}

	/**
	 * @param boolean $enabled
	 */
	public function setEnabled($enabled) {
		$this->enabled = (bool)$enabled;
	}

	/**
	 * @return boolean
	 */
	public function isEnabled() {
		return Debug_Profiler::getProfilingEnabled() && $this->enabled;
	}



	/**
	 * @return string
	 */
	public function getID(){
		return md5($this->getName());
	}

	/**
	 * @return bool
	 */
	function isEmpty(){
		return !$this->getMilestonesCount() && !$this->getPeriodsCount();
	}

	/**
	 * @return \Et\Debug_Profiler_Milestone[]
	 */
	public function getMilestones() {
		return $this->milestones;
	}

	/**
	 * @return int
	 */
	public function getMilestonesCount() {
		return count($this->milestones);
	}

	/**
	 * @return \Et\Debug_Profiler_Period[]
	 */
	public function getPeriods() {
		return $this->periods;
	}

	/**
	 * @return int
	 */
	public function getPeriodsCount() {
		return count($this->periods);
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}



	/**
	 * @return float
	 */
	function getMilestonesDuration(){
		$duration = 0.0;
		foreach($this->milestones as $milestone){
			$duration += $milestone->getDuration();
		}
		return $duration;
	}

	/**
	 * @return float
	 */
	function getPeriodsDuration(){
		$duration = 0.0;
		foreach($this->periods as $period){
			$duration += (float)$period->getDuration();
		}
		return $duration;
	}

	/**
	 * @param string $name
	 * @param null|float $custom_time [optional]
	 * @return \Et\Debug_Profiler_Milestone
	 */
	function milestone($name, $custom_time = null){
		if(!$this->isEnabled()){
			return false;
		}
		if($this->milestones){
			$milestone = new Debug_Profiler_Milestone($name, end($this->milestones), $custom_time);
		} else {
			$milestone = new Debug_Profiler_Milestone($name, null, $custom_time);
		}
		$this->milestones[] = $milestone;
		return $milestone;
	}

	/**
	 * @param string $period_name
	 * @return Debug_Profiler_Period|bool
	 */
	function period($period_name){
		if(!$this->isEnabled()){
			return false;
		}
		$period = new Debug_Profiler_Period($period_name);
		$this->periods[] = $period;
		return $period;
	}


	/**
	 * @return array
	 */
	function toArray(){
		return array(
			"name" => $this->getName(),
			"milestones" => $this->getMilestones(),
			"periods" => $this->getPeriods(),
			"milestones_count" => $this->getMilestonesCount(),
			"periods_count" => $this->getPeriodsCount(),
			"milestones_duration" => $this->getMilestonesDuration(),
			"periods_duration" => $this->getPeriodsDuration()
		);
	}

	/**
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}

	/**
	 * @return string
	 */
	abstract public function getMilestonesHTML();

	/**
	 * @return string
	 */
	abstract public function getPeriodsHTML();

	/**
	 * @return string
	 */
	function toHTML(){
		$ID = md5($this->getName());
		$output = "<span class='profiler_title' id='profiler_{$ID}_title'>Profiler <strong>" . htmlspecialchars($this->getName()) . "</strong>&nbsp;";
		$output .= "<a href='#' onclick=\"document.getElementById('profiler_{$ID}').style.display='none';return false;\">[hide]</a>&nbsp;|&nbsp;";
		$output .= "<a href='#' onclick=\"document.getElementById('profiler_{$ID}').style.display='';return false;\">[show]</a>\n";
		$output .= "<div id='profiler_{$ID}'>";
		$output .= $this->getMilestonesHTML();
		$output .= "<br/>\n";
		$output .= $this->getPeriodsHTML();
		$output .= "</div>";
		return $output;
	}

	/**
	 * @return \Et\Debug_Profiler_Period
	 */
	function getLastPeriod(){
		$period = end($this->periods);
		reset($this->periods);
		return $period;
	}
}