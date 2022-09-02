<?php

	include_once('db_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	$users = $_POST['users'];
	$xp = $_POST['xp'];
	$awards = $_POST['awards'];
	$attribute_pts = $_POST['attribute_pts'];

	$i = 0;
	foreach ($users as $user_id) {
		// update xp and award values
		$sql = "UPDATE user SET xp = ".$xp[$i].", xp_awarded = ".$awards[$i];
		if (count($attribute_pts) > 0) {
			$sql .= ", attribute_pts = ".$attribute_pts[$i];
		}
		$i++;
		$sql .= " WHERE id = ".$user_id;
		$db->query($sql);
	}

	$db->close();
	echo 'ok';

?>