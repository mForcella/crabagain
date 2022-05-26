<?php

	// establish database connection
	include_once('db_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check connection
	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	// get hashed password from database
	$password = $_POST['password'];
	$user_id = $_POST['user_id'];
	$hashed_password = "";
	$sql = "SELECT * from user WHERE id = ".$user_id;
	$result = $db->query($sql);
	if ($result->num_rows === 1) {
		while($row = $result->fetch_assoc()) {
			$hashed_password = $row;
		}
	}

	// confirm that the password matches the records
	if(password_verify(trim($password), $hashed_password['password'])) {
		echo 1;
	} else {
		echo 0;
	}

?>