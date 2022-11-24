<?php

	include_once('db_config.php');
	$db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

	if ($db->connect_error) {
		echo $db->connect_error;
	  	die("Connection failed: " . $db->connect_error);
	}

	$user = $_POST['user'];
	$xp = $_POST['xp'];
	$attribute_pts = $_POST['attribute_pts'];

	// delete all awards for user
	$sql = "DELETE FROM user_xp_award WHERE user_id = ".$user;
	$db->query($sql);

	// update user xp and attribute points
	$sql = "UPDATE user SET xp = ".$xp.", attribute_pts = ".$attribute_pts." WHERE id = ".$user;
	$db->query($sql);
	
	$db->close();
	echo 'ok';

?>