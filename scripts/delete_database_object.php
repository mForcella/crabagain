<?php

	// establish database connection
	include_once('../config/db_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check connection
	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	$sql = "DELETE FROM ".$_POST['table']." WHERE id = ".$_POST['id'];
	$db->query($sql);
	$db->close();
	// echo $sql;

	echo "delete ok";

?>