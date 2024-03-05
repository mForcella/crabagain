<?php

	// establish database connection
	include_once('../config/db_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check connection
	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	$data = $_POST['data'];
	$columns = $_POST['columns'];

	$sql = $_POST['user_id'] == "" ? "INSERT INTO ".$_POST['table']." (" : "INSERT INTO ".$_POST['table']." (user_id,";
	foreach ($columns as $column) {
		if (isset($data[$column]) && $data[$column] != '') {
			$sql .= $column.",";
		}
	}
	$sql = $_POST['user_id'] == "" ? rtrim($sql, ",") . ") VALUES (" : rtrim($sql, ",") . ") VALUES (".$_POST['user_id'].",";
	foreach ($columns as $column) {
		if (isset($data[$column]) && $data[$column] != '') {
			$sql .= is_numeric($data[$column]) ? $data[$column]."," : "'".addslashes($data[$column])."',";
		}
	}
	$sql = rtrim($sql, ",") . ")";
	// echo $sql;

	$db->query($sql);
	echo $db->insert_id;
	$db->close();

?>