<?php
namespace Et;
et_require('Debug_Profiler_Abstract');
et_require("Debug_Profiler");
class Debug_Profiler_Default extends Debug_Profiler_Abstract{

	/**
	 * @return string
	 */
	public function getMilestonesHTML() {
		if(!$this->getMilestonesCount()){
			return "No milestones";
		}

		$output = "<strong>Milestones ({$this->getMilestonesCount()})</strong><br/>\n";
		$output .= "<table border='1' style='border-collapse: collapse'>\n";
		$output .= "<thead><tr><th>Milestone</th><th>Duration</th><th>Memory used</th></tr></thead>\n";
		$output .= "<tbody>\n";
		foreach($this->milestones as $milestone){
			$output .= "<tr>\n";
			$output .= "<td>".htmlspecialchars($milestone->getName(), ENT_NOQUOTES)."</td>";
			$output .= "<td>+".round($milestone->getDuration(), 5)."s</td>";
			$output .= "<td>".Debug_Profiler::formatMemorySize($milestone->getUsedMemory())."</td>";
			$output .= "</tr>\n";
		}
		$output .= "</tbody>\n";
		$output .= "<tfoot><tr><td colspan='3'>Total duration: ".round($this->getMilestonesDuration(), 5)."s</td></tr></tfoot>\n";
		$output .= "</table>";

		return $output;
	}

	/**
	 * @return string
	 */
	public function getPeriodsHTML() {
		if(!$this->getPeriodsCount()){
			return "No periods";
		}

		$output = "<strong>Periods ({$this->getPeriodsCount()})</strong><br/>\n";
		$output .= "<table border='1' style='border-collapse: collapse'>\n";
		$output .= "<thead><tr><th>Period</th><th>Duration</th><th>Memory difference</th></tr></thead>\n";
		$output .= "<tbody>\n";
		foreach($this->periods as $period){
			$output .= "<tr>\n";
			$output .= "<td>".htmlspecialchars($period->getName(), ENT_NOQUOTES)."</td>";
			$output .= "<td>".round($period->getDuration(), 5)."s</td>";
			$output .= "<td>".Debug_Profiler::formatMemorySize($period->getMemoryDifference())."</td>";
			$output .= "</tr>\n";
		}
		$output .= "</tbody>\n";
		$output .= "<tfoot><tr><td colspan='3'>Total duration: ".round($this->getPeriodsDuration(), 5)."s</td></tr></tfoot>\n";
		$output .= "</table>";

		return $output;
	}
}