<?php

	// establish database connection
	include_once('db_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	// check connection
	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	// get user password
	$user = [];
	$sql = "SELECT password from user WHERE id = ".$_POST['user_id'];
	$result = $db->query($sql);
	if ($result->num_rows === 1) {
		while($row = $result->fetch_assoc()) {
			$user = $row;
		}
	}

	// get campaign admin password
	$campaign = [];
	$sql = "SELECT admin_password from campaign WHERE id = ".$_POST['campaign_id'];
	$result = $db->query($sql);
	if ($result->num_rows === 1) {
		while($row = $result->fetch_assoc()) {
			$campaign = $row;
		}
	}

	// confirm that the input password matches the records
	$password = $_POST['password'];
	if(password_verify(trim($password), $user['password']) || password_verify(trim($password), $campaign['admin_password'])) {
		echo 1;
	} else {
		echo 0;
	}

?>