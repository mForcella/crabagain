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
	$user_id = $_POST['user_id'];

	$sql = $user_id == "" ? "INSERT INTO ".$_POST['table']." (" : "INSERT INTO ".$_POST['table']." (user_id,";
	foreach ($columns as $column) {
		if (isset($data[$column]) && $data[$column] != '') {
			$sql .= $column.",";
		}
	}
	$sql = $user_id == "" ? rtrim($sql, ",") . ") VALUES (" : rtrim($sql, ",") . ") VALUES (".$user_id.",";
	foreach ($columns as $column) {
		if (isset($data[$column]) && $data[$column] != '') {
			$sql .= is_numeric($data[$column]) ? $data[$column]."," : "'".addslashes($data[$column])."',";
		}
	}
	$sql = rtrim($sql, ",") . ")";
	// echo $sql;

	try {
		$db->query($sql);
		echo $db->insert_id;
	} catch (mysqli_sql_exception $e) {
		error_log("\nSQL Query: $sql\n", 3, "error_log");
		echo $db->error;
	}

	$do_not_write = [
		"user_feat" => ["description"],
		"user_note" => ["note"],
		"user" => ["background"]
	];

	// check for potentially long columns to not include in sql_query
	foreach ($do_not_write as $table => $skip_columns) {
		if ($_POST['table'] == $table) {
			foreach ($skip_columns as $skip_column) {
				if (($key = array_search($skip_column, $columns)) !== false) {
				    $data[$skip_column] = "LOREM SKIPSUM";
				}
				$sql = $user_id == "" ? "INSERT INTO ".$_POST['table']." (" : "INSERT INTO ".$_POST['table']." (user_id,";
				foreach ($columns as $column) {
					if (isset($data[$column]) && $data[$column] != '') {
						$sql .= $column.",";
					}
				}
				$sql = $user_id == "" ? rtrim($sql, ",") . ") VALUES (" : rtrim($sql, ",") . ") VALUES (".$user_id.",";
				foreach ($columns as $column) {
					if (isset($data[$column]) && $data[$column] != '') {
						$sql .= is_numeric($data[$column]) ? $data[$column]."," : "'".addslashes($data[$column])."',";
					}
				}
				$sql = rtrim($sql, ",") . ")";
			}
		}
	}

	$save_sql = "INSERT INTO sql_query (query, source, type, login_id, character_id) VALUES ('".addslashes($sql)."', 'insert_database_object.php', 'insert', ".$_POST['login_id'].", ".($user_id == "" ? NULL : $user_id).")";
	$db->query($save_sql);
	
	$db->close();

?>