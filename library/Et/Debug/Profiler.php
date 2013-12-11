<?php
namespace Et;
class Debug_Profiler {

	const MAIN_PROFILER_NAME = "Main platform profiler";

	/**
	 * @var Debug_Profiler_Abstract[]
	 */
	protected static $profilers = array();

	/**
     * @var bool
	 */
	protected static $profiling_enabled = ET_PROFILE_MODE;

	/**
	 * @return bool|Debug_Profiler_Abstract
	 */
	public static function getMainProfiler(){
		$profiler = static::getProfiler(static::MAIN_PROFILER_NAME);
		if(!$profiler){
			et_require("Debug_Profiler_Default");
			$profiler = new Debug_Profiler_Default(static::MAIN_PROFILER_NAME);
			static::addProfiler($profiler);
		}
		return $profiler;
	}

	/**
	 * @param string $profiler_name
	 * @return bool|Debug_Profiler_Abstract
	 */
	public static function getProfiler($profiler_name){
		$ID = static::getProfilerID($profiler_name);
		return isset(static::$profilers[$ID])
				? static::$profilers[$ID]
				: false;
	}

	/**
	 * @param string $profiler_name
	 * @return string
	 */
	protected static function getProfilerID($profiler_name){
		return md5($profiler_name);
	}

	/**
	 * @param Debug_Profiler_Abstract $profiler
	 */
	public static function addProfiler(Debug_Profiler_Abstract $profiler){
		static::$profilers[static::getProfilerID($profiler->getName())] = $profiler;
	}

	/**
	 * @param boolean $profiling_enabled
	 */
	public static function setProfilingEnabled($profiling_enabled) {
		self::$profiling_enabled = (bool)$profiling_enabled;
	}

	/**
	 * @return boolean
	 */
	public static function getProfilingEnabled() {
		return self::$profiling_enabled;
	}



	/**
	 * @param string $milestone_name
	 * @return Debug_Profiler_Milestone|bool
	 */
	public static function milestone($milestone_name){
		return static::getMainProfiler()->milestone($milestone_name);
	}

	/**
	 * @param string $period_name
	 * @return Debug_Profiler_Period|bool
	 */
	public static function period($period_name){
		return static::getMainProfiler()->period($period_name);
	}

	/**
	 * @param null|string $report_name [optional]
	 * @return string
	 */
	public static function generateReport($report_name = null){
		if(!static::getProfilingEnabled()){
			return false;
		}

		if(!trim($report_name)){
			$report_name = "[".date("Y.m.d H:i:s")."] Profiler report";
			if(defined("ET_REQUEST_URL_WITH_QUERY")){
				$report_name .= " - " . ET_REQUEST_URL_WITH_QUERY;
			}
		}

		$main_profiler = static::getMainProfiler();
		$profilers_output = $main_profiler->toHTML() . "\n<hr/>\n";
		$profilers_links = array("<li><a href=\"#profiler_{$main_profiler->getID()}_title\">".htmlspecialchars($main_profiler->getName())."</a></li>");
		foreach(static::$profilers as $profiler){
			if($profiler === $main_profiler){
				continue;
			}
			$ID = $profiler->getID();
			$profilers_output .= $profiler->toHTML() . "\n<hr/>\n";
			$profilers_links[] = "<li><a href=\"#profiler_{$ID}_title\">".htmlspecialchars($profiler->getName())."</a></li>";
		}

		$profilers_links = "<ul>\n" . implode("\n", $profilers_links) . "</ul>\n";

		$report_name_specialchars = htmlspecialchars($report_name);
		$time = date("d.m.Y H:i:s");
		$URL = defined("ET_REQUEST_URL_WITH_QUERY") ? htmlspecialchars(ET_REQUEST_URL_WITH_QUERY) : "???";
		$start_time = isset($_SERVER['REQUEST_TIME_FLOAT'])
				    ? $_SERVER['REQUEST_TIME_FLOAT']
					: ET_REQUEST_TIME;
		$duration = round(microtime(true) - $start_time, 5);
		$memory_usage = static::formatMemorySize(memory_get_peak_usage());

		$output
=<<<"PROFILER_OUTPUT"
<!DOCTYPE html>
<html>
<head>

	<meta charset="utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<title>{$report_name_specialchars}</title>

	<style type="text/css">
		.datagrid table {
			border-collapse: collapse;
			text-align: left;
			width: 100%;
		}

		.datagrid {
			font: normal 12px/150% Arial, Helvetica, sans-serif;
			background: #fff;
			overflow: hidden;
			border: 1px solid #006699;
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
			border-radius: 3px;
		}

		.datagrid table td, .datagrid table th {
			padding: 3px 10px;
		}

		.datagrid table thead th {
			background: -webkit-gradient(linear, left top, left bottom, color-stop(0.05, #006699), color-stop(1, #00557F));
			background: -moz-linear-gradient(center top, #006699 5%, #00557F 100%);
			filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#006699', endColorstr='#00557F');
			background-color: #006699;
			color: #ffffff;
			font-size: 15px;
			font-weight: bold;
			border-left: 1px solid #0070A8;
		}

		.datagrid table thead th:first-child {
			border: none;
		}

		.datagrid table tbody td {
			color: #00496B;
			border-left: 1px solid #E1EEF4;
			font-size: 12px;
			font-weight: normal;
		}

		.datagrid table tbody .alt td {
			background: #E1EEF4;
			color: #00496B;
		}

		.datagrid table tbody td:first-child {
			border-left: none;
		}

		.datagrid table tbody tr:last-child td {
			border-bottom: none;
		}

		.datagrid table tfoot td div {
			border-top: 1px solid #006699;
			background: #E1EEF4;
		}

		.datagrid table tfoot td {
			padding: 0;
			font-size: 12px
		}

		.datagrid table tfoot td div {
			padding: 2px;
		}

		.datagrid table tfoot td ul {
			margin: 0;
			padding: 0;
			list-style: none;
			text-align: right;
		}

		.datagrid table tfoot  li {
			display: inline;
		}

		.datagrid table tfoot li a {
			text-decoration: none;
			display: inline-block;
			padding: 2px 8px;
			margin: 1px;
			color: #FFFFFF;
			border: 1px solid #006699;
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
			border-radius: 3px;
			background: -webkit-gradient(linear, left top, left bottom, color-stop(0.05, #006699), color-stop(1, #00557F));
			background: -moz-linear-gradient(center top, #006699 5%, #00557F 100%);
			filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#006699', endColorstr='#00557F');
			background-color: #006699;
		}

		.datagrid table tfoot ul.active, .datagrid table tfoot ul a:hover {
			text-decoration: none;
			border-color: #006699;
			color: #FFFFFF;
			background: none;
			background-color: #00557F;
		}
	</style>


	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
</head>
<body>
<strong>{$report_name_specialchars}</strong><br/<br/>
<div class='datagrid'>
<table cellspacing="0" cellpadding="2" border="1">
	<tr>
		<td>Time:</td>
		<td>{$time}</td>
	</tr>
	<tr>
		<td>URL:</td>
		<td><a href="{$URL}">{$URL}</a></td>
	</tr>
	<tr>
		<td>Total duration:</td>
		<td>{$duration}s</td>
	</tr>
	<tr>
		<td>Peak memory usage</td>
		<td>{$memory_usage}</td>
	</tr>
</table>
{$profilers_links}<hr/>
{$profilers_output}
</div>
</body>
</html>
PROFILER_OUTPUT;
		return $output;
	}

	/**
	 * @param System_File $file
	 * @param null|string $report_name [optional]
	 * @return bool
	 */
	public static function saveReport(System_File $file, $report_name = null){
		if(!static::getProfilingEnabled()){
			return false;
		}
		$file->writeContent(static::generateReport($report_name));
		return true;
	}

	/**
	 * @param int $memory_size
	 * @return string
	 */
	public static function formatMemorySize($memory_size){
		if($memory_size < 1024){
			return $memory_size . " B";
		}

		$memory_size /= 1024;
		if($memory_size < 1024){
			return round($memory_size, 2) . " kB";
		}

		$memory_size /= 1024;
		if($memory_size < 1024){
			return round($memory_size, 2) . " MB";
		}

		$memory_size /= 1024;
		if($memory_size < 1024){
			return round($memory_size, 2) . " GB";
		}

		$memory_size /= 1024;
		return round($memory_size, 2) . " TB";
	}

}