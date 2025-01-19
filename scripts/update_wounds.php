<?php

	include_once('../config/db_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	$campaign_id = $_POST['campaign_id'];

	$sql = "SELECT id, damage, fortitude, size FROM user WHERE campaign_id = $campaign_id";
	$result = $db->query($sql);
	$damage = [];
	if ($result) {
		while($row = $result->fetch_assoc()) {
			array_push($damage, $row);
		}
	}
	
	$db->close();
	echo json_encode($damage);

?>