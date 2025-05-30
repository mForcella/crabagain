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

	try {
		$db->query($sql);
		echo 'ok';
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
				$sql = "UPDATE ".$_POST['table']." SET ";
				foreach ($columns as $column) {
					if (isset($data[$column])) {
						$insert_val = $data[$column] == "" ? 'NULL, ' : ( is_numeric($data[$column]) ? ($data[$column].", ") : ("'".addslashes($data[$column])."', ") );
						$sql .= $column." = ".$insert_val;
					}
				}
				$sql = rtrim($sql, ", ");
				$sql .= " WHERE id = ".$data['id'];
			}
		}
	}

	$save_sql = "INSERT INTO sql_query (query, source, type, login_id, character_id) VALUES ('".addslashes($sql)."', 'update_database_object.php', 'update', ".$_POST['login_id'].", ".($user_id == "" ? NULL : $user_id).")";
	$db->query($save_sql);

	$db->close();

?>