<?php

	include_once('../config/db_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	$users = $_POST['users'];
	$awards = $_POST['awards'];

	$i = 0;
	foreach ($users as $user_id) {
		$sql = "INSERT INTO user_xp_award (user_id, xp_award) VALUES (".$user_id.", ".$awards[$i].")";
		$db->query($sql);
		$i++;
	}
	
	$db->close();
	echo 'ok';

?>