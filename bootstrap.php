<?php
namespace Et;
require(__DIR__ . "/environment.php");
require(__DIR__ . "/init.php");

$cfg = new DB_Adapter_SQLite_Config(array(
	"dsn" => "sqlite:" . ET_TEMPORARY_DATA_PATH . "/sqlite.db"
));
$db = new DB_Adapter_SQLite($cfg);
$profiler = new DB_Profiler("SQLite profiler");
$db->setProfiler($profiler);

$db->exec("CREATE TABLE IF NOT EXISTS some_table(ID INTEGER PRIMARY KEY, name TEXT, surname TEXT )");
$db->truncateTable("some_table");
$db->insert("some_table", array("ID" => 1, "name" => "John", "surname" => "Doe"));
$db->insert("some_table", array("ID" => 2, "name" => "Peter", "surname" => "Jackson"));
$db->insert("some_table", array("ID" => 3, "name" => "Jackie", "surname" => "Chan"));

Http_Headers::responseText("", false);

print_r($db->fetchColumn(DB_Query::getInstance("some_table")));

echo json_encode($profiler, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit();


System::initialize();
Http_Request::initialize();
MVC_Modules::initialize();



echo "ET RUNNING [" . Debug::getDuration() . "s]";

Application::end();
