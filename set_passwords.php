<?php

	include_once('db_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	$admin_password = password_hash("crabrangoon", PASSWORD_DEFAULT);
	$new_character_password = password_hash("gygax", PASSWORD_DEFAULT);
	$sql = "UPDATE campaign SET admin_password = '".$admin_password."', new_character_password = '".$new_character_password."' WHERE id = 1";
	$db->query($sql);

?>