<?php

	// establish database connection
	include_once('db_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check connection
	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	// insert new row
	$hashed_password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);
	$sql = "INSERT into campaign (name, admin_password) VALUES ('".$_POST['name']."', '".$hashed_password."')";
	$db->query($sql);

	// return inserted id
	echo $db->insert_id;

?>