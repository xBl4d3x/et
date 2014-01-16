<?php
namespace Et;
require(__DIR__ . "/environment.php");
require(__DIR__ . "/init.php");

$cfg = new DB_Adapter_SQLite_Config(array(
	"dsn" => "sqlite:" . ET_TEMPORARY_DATA_PATH . "/sqlite.db"
));
$db = new DB_Adapter_SQLite($cfg);
$db->enableProfiler();
$db->exec("CREATE TABLE IF NOT EXISTS some_table(ID INTEGER PRIMARY KEY, name TEXT, surname TEXT )");
$db->truncateTable("some_table");

$db->beginTransaction();
$db->insert("some_table", array("ID" => 1, "name" => "John", "surname" => "Doe"));
$db->insert("some_table", array("ID" => 2, "name" => "Peter", "surname" => "Jackson"));
$db->insert("some_table", array("ID" => 3, "name" => "Jackie", "surname" => "Chan"));
$db->commit();

Http_Headers::responseText("", false);


print_r($db->fetchRows(DB_Query::getInstance("some_table")));

echo json_encode($db->getProfiler(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit();
$query = new DB_Query("test_table");
$query->select(array(
		"col1_as" => "column1",
		"col_other" => "table2.column1",
		"col2",
		"cnt" => "COUNT(*)"
));

echo (string)$query;

exit();

System::initialize();
Http_Request::initialize();
MVC_Modules::initialize();



echo "ET RUNNING [" . Debug::getDuration() . "s]";

Application::end();
