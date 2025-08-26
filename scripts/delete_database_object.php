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

	$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : NULL;
	$login_id = isset($_POST['login_id']) ? $_POST['login_id'] : NULL;

	$save_sql = "INSERT INTO sql_query (query, source, type, login_id, character_id) VALUES ('".addslashes($sql)."', 'delete_database_object.php', 'delete', ".$login_id.", ".$user_id.")";
	$db->query($save_sql);
	
	$db->close();
	// echo $sql;

	echo "delete ok";

?>