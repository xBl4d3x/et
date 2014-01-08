<?php
namespace Et;
class DB_Profiler extends Debug_Profiler_Abstract {

	/**
	 * @var DB_Profiler_Query[]
	 */
	protected $periods = array();


	/**
	 * @return string
	 */
	public function getMilestonesHTML() {
		return "";
	}

	/**
	 * @return string
	 */
	public function getPeriodsHTML() {
		if(!$this->getPeriodsCount()){
			return "No queries";
		}

		$locale = Locales::getLocale("en_US");
		$output = "<strong>Queries ({$this->getPeriodsCount()})</strong><br/>\n";
		$output .= "<table cellspacing='0' cellpadding='2' border='1' style='border-collapse: collapse;'>\n";
		$output .= "<thead><tr><th>Duration</th><th>Memory difference</th><th>Query</th></tr></thead>\n";
		$output .= "<tbody>\n";
		foreach($this->periods as $period){
			$output .= "<tr>\n";
			$output .= "<td>".round($period->getDuration(), 5)."s</td>";
			$output .= "<td>".$locale->formatSize($period->getMemoryDifference())."</td>";
			$output .= "<td><pre>".htmlspecialchars($period->getName(), ENT_NOQUOTES)."</pre></td>";
			$output .= "</tr>\n";
		}
		$output .= "</tbody>\n";
		$output .= "<tfoot><tr><td colspan='3'>Total duration: ".round($this->getPeriodsDuration(), 5)."s</td></tr></tfoot>\n";
		$output .= "</table>";

		return $output;
	}

	/**
	 * @return \Et\DB_Profiler_Query
	 */
	function getLastQuery(){
		return $this->getLastPeriod();
	}

	/**
	 * @param string $period_name
	 * @return DB_Profiler_Query|bool
	 */
	function period($period_name){
		if(!$this->isEnabled()){
			return false;
		}
		$period = new DB_Profiler_Query($period_name);
		$this->periods[] = $period;
		return $period;
	}

	/**
	 * @param string $query
	 * @return bool|DB_Profiler_Query
	 */
	function queryStarted($query){
		return $this->period($query);
	}

}