<?php

	// establish database connection
	include_once('../config/db_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check connection
	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	$sql = "UPDATE ".$_POST['table']." SET ".$_POST['column']." = '".addslashes($_POST['value'])."' WHERE id = ".$_POST['id'];
	try {
		$db->query($sql);
		echo "ok";
	} catch (mysqli_sql_exception $e) {
		error_log("\nSQL Query: $sql\n", 3, "error_log");
		echo $db->error;
	}
	
	$user_id = $_POST['user_id'];

	$do_not_write = [
		"user_feat" => ["description"],
		"user_note" => ["note"],
		"user" => ["background"]
	];

	// check for potentially long columns to not include in sql_query
	foreach ($do_not_write as $table => $skip_columns) {
		if ($_POST['table'] == $table) {
			foreach ($skip_columns as $skip_column) {
				if ($_POST['column'] == $skip_column) {
					$sql = "UPDATE ".$_POST['table']." SET ".$_POST['column']." = 'LOREM SKIPSUM' WHERE id = ".$_POST['id'];
				}
			}
		}
	}
			
	$save_sql = "INSERT INTO sql_query (query, source, type, login_id, character_id) VALUES ('".addslashes($sql)."', 'update_database_column.php', 'update', ".$_POST['login_id'].", ".($user_id == "" ? NULL : $user_id).")";
	$db->query($save_sql);

	$db->close();
	// echo $sql;


?>