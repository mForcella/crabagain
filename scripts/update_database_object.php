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

	$sql = "UPDATE ".$_POST['table']." SET ";
	foreach ($columns as $column) {
		if (isset($data[$column])) {
			$insert_val = $data[$column] == "" ? 'NULL, ' : ( is_numeric($data[$column]) ? ($data[$column].", ") : ("'".addslashes($data[$column])."', ") );
			$sql .= $column." = ".$insert_val;
		}
	}
	$sql = rtrim($sql, ", ");
	$sql .= " WHERE id = ".$data['id'];
	// echo $sql;

	$db->query($sql);
	$db->close();
	echo 'update ok';

?>